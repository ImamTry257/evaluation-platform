<?php

namespace App\Http\Controllers\Api\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\ResponseAnswer;
use App\Models\ResponseSession;
use App\Models\EvaluationResult;
use App\Models\EvaluationResultDetail;
use App\Models\Recommendation;
use App\Models\ScoringLevel;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EvaluasiController extends Controller
{
    use HasApiResponse;

    /**
     * POST /api/v1/evaluations/start
     * Start a new evaluation session or resume existing one.
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questionnaireId' => 'required|exists:questionnaires,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $questionnaire = Questionnaire::with('components.subComponents.indicators.questions')
            ->where('status', 'published')
            ->find($request->questionnaireId);

        if (!$questionnaire) {
            return $this->errorResponse('Questionnaire not found or not published', 404);
        }

        // Check for existing inProgress session
        $existingSession = ResponseSession::where('userId', $request->user()->id)
            ->where('questionnaireId', $request->questionnaireId)
            ->where('status', 'inProgress')
            ->first();

        if ($existingSession) {
            // Resume existing session
            return $this->successResponse([
                'session' => $existingSession->load(['answers.question.indicator']),
                'questionnaire' => $questionnaire,
                'scoringLevels' => ScoringLevel::where('is_active', 1)->orderBy('value', 'asc')->get(),
                'isResumed' => true,
            ], 'Session resumed successfully');
        }

        // Create new session
        $session = ResponseSession::create([
            'userId' => $request->user()->id,
            'questionnaireId' => $request->questionnaireId,
            'status' => 'inProgress',
            'startedAt' => now(),
            'remainingSeconds' => $questionnaire->durationMinutes * 60,
        ]);

        return $this->successResponse([
            'session' => $session->load(['answers.question.indicator']),
            'questionnaire' => $questionnaire,
            'scoringLevels' => ScoringLevel::where('is_active', 1)->orderBy('value', 'asc')->get(),
            'isResumed' => false,
        ], 'Evaluation started successfully', 201);
    }

    /**
     * GET /api/v1/evaluations/{sessionId}
     * Get evaluation session details (for resume).
     */
    public function show(Request $request, $sessionId)
    {
        $session = ResponseSession::where('userId', $request->user()->id)
            ->with(['answers.question.indicator', 'questionnaire.components.subComponents.indicators.questions'])
            ->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found', 404);
        }

        return $this->successResponse([
            'session' => $session,
            'scoringLevels' => ScoringLevel::where('is_active', 1)->orderBy('value', 'asc')->get(),
        ], 'Session retrieved successfully');
    }

    /**
     * POST /api/v1/evaluations/{sessionId}/answers
     * Save or update an answer for a question.
     */
    public function saveAnswer(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'questionId' => 'required|exists:questions,id',
            'score' => 'required|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $session = ResponseSession::where('userId', $request->user()->id)
            ->where('status', 'inProgress')
            ->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found or not in progress', 404);
        }

        // Verify question belongs to this questionnaire
        $question = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
            $q->where('questionnaireId', $session->questionnaireId);
        })->find($request->questionId);

        if (!$question) {
            return $this->errorResponse('Question does not belong to this questionnaire', 422);
        }

        // Upsert answer (unique constraint on responseSessionId + questionId)
        $answer = ResponseAnswer::updateOrCreate(
            [
                'responseSessionId' => $session->id,
                'questionId' => $request->questionId,
            ],
            [
                'score' => $request->score,
            ]
        );

        return $this->successResponse(
            $answer->load('question'),
            'Answer saved successfully'
        );
    }

    /**
     * POST /api/v1/evaluations/{sessionId}/submit
     * Submit the evaluation and trigger scoring.
     */
    public function submit(Request $request, $sessionId)
    {
        $session = ResponseSession::where('userId', $request->user()->id)
            ->where('status', 'inProgress')
            ->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found or not in progress', 404);
        }

        // Check if all questions are answered
        $totalQuestions = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
            $q->where('questionnaireId', $session->questionnaireId);
        })->count();

        $answeredQuestions = $session->answers()->count();

        if ($answeredQuestions < $totalQuestions) {
            return $this->errorResponse('Please answer all questions before submitting', 422, [
                'answered' => $answeredQuestions,
                'total' => $totalQuestions,
            ]);
        }

        // Calculate scores and generate results
        DB::beginTransaction();

        try {
            // Update session status
            $session->update([
                'status' => 'submitted',
                'submittedAt' => now(),
            ]);

            // Calculate indicator scores
            $indicatorScores = $this->calculateIndicatorScores($session);

            // Calculate overall score (weighted average)
            $overallScore = 0;
            $totalWeight = 0;

            foreach ($indicatorScores as $indicatorId => $data) {
                $overallScore += $data['score'] * $data['totalWeight'];
                $totalWeight += $data['totalWeight'];
            }

            // Score is weighted average, percentage is score/maxScore(7) * 100
            $weightedAverage = $totalWeight > 0 ? $overallScore / $totalWeight : 0;
            $overallPercentage = ($weightedAverage / 7) * 100; // Likert 1-7 scale
            $overallCategory = $this->getCategory($overallPercentage);

            // Create evaluation result
            $result = EvaluationResult::create([
                'responseSessionId' => $session->id,
                'overallScore' => round($weightedAverage, 2),
                'overallPercentage' => round($overallPercentage, 2),
                'overallCategory' => $overallCategory,
                'conclusion' => $this->getConclusion($overallCategory),
            ]);

            // Create result details for each indicator
            foreach ($indicatorScores as $indicatorId => $data) {
                $percentage = ($data['score'] / 7) * 100; // Likert 1-7 scale
                $category = $this->getCategory($percentage);

                // Find recommendation for this indicator and score range
                $recommendation = Recommendation::where('indicatorId', $indicatorId)
                    ->where('minScore', '<=', $data['score'])
                    ->where('maxScore', '>=', $data['score'])
                    ->first();

                EvaluationResultDetail::create([
                    'evaluationResultId' => $result->id,
                    'indicatorId' => $indicatorId,
                    'score' => round($data['score'], 2),
                    'percentage' => round($percentage, 2),
                    'category' => $category,
                    'recommendation' => $recommendation?->recommendationText,
                ]);
            }

            DB::commit();

            // Reload result with relations
            $result->load('details.indicator');

            return $this->successResponse([
                'result' => $result,
            ], 'Evaluation submitted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to submit evaluation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/evaluations/{sessionId}/results
     * Get evaluation results.
     */
    public function results(Request $request, $sessionId)
    {
        $session = ResponseSession::where('userId', $request->user()->id)
            ->with(['result.details.indicator', 'questionnaire'])
            ->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found', 404);
        }

        if ($session->status !== 'submitted') {
            return $this->errorResponse('Session not yet submitted', 422);
        }

        if (!$session->result) {
            return $this->errorResponse('Results not available', 404);
        }

        return $this->successResponse([
            'result' => $session->result,
        ], 'Results retrieved successfully');
    }

    /**
     * POST /api/v1/evaluations/{sessionId}/autosave
     * Auto-save remaining time and optionally answers.
     */
    public function autoSave(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'remainingSeconds' => 'required|integer|min:0',
            'answers' => 'nullable|array',
            'answers.*.questionId' => 'required_with:answers|exists:questions,id',
            'answers.*.score' => 'required_with:answers|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $session = ResponseSession::where('userId', $request->user()->id)
            ->where('status', 'inProgress')
            ->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found or not in progress', 404);
        }

        // Update remaining time
        $session->update([
            'remainingSeconds' => $request->remainingSeconds,
        ]);

        // Auto-save answers if provided
        $savedAnswers = [];
        $skippedAnswers = [];

        if ($request->has('answers') && is_array($request->answers)) {
            foreach ($request->answers as $answerData) {
                // Verify question belongs to this questionnaire
                $question = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
                    $q->where('questionnaireId', $session->questionnaireId);
                })->find($answerData['questionId']);

                if ($question) {
                    ResponseAnswer::updateOrCreate(
                        [
                            'responseSessionId' => $session->id,
                            'questionId' => $answerData['questionId'],
                        ],
                        [
                            'score' => $answerData['score'],
                        ]
                    );
                    $savedAnswers[] = $answerData['questionId'];
                } else {
                    $skippedAnswers[] = $answerData['questionId'];
                }
            }
        }

        $response = [
            'remainingSeconds' => $session->remainingSeconds,
            'savedAt' => now()->toIso8601String(),
        ];

        if (!empty($savedAnswers)) {
            $response['savedAnswers'] = $savedAnswers;
        }

        if (!empty($skippedAnswers)) {
            $response['skippedAnswers'] = $skippedAnswers;
            $response['skippedReason'] = 'Questions do not belong to this questionnaire';
        }

        return $this->successResponse($response, 'Auto-save successful');
    }

    /**
     * Calculate scores for each indicator.
     */
    private function calculateIndicatorScores(ResponseSession $session): array
    {
        $answers = $session->answers()->with('question.indicator')->get();

        $indicatorScores = [];

        foreach ($answers as $answer) {
            $indicatorId = $answer->question->indicator->id;
            $weight = $answer->question->weight;

            if (!isset($indicatorScores[$indicatorId])) {
                $indicatorScores[$indicatorId] = [
                    'totalScore' => 0,
                    'totalWeight' => 0,
                ];
            }

            $indicatorScores[$indicatorId]['totalScore'] += $answer->score * $weight;
            $indicatorScores[$indicatorId]['totalWeight'] += $weight;
        }

        // Calculate weighted average for each indicator
        foreach ($indicatorScores as $indicatorId => &$data) {
            $data['score'] = $data['totalWeight'] > 0
                ? $data['totalScore'] / $data['totalWeight']
                : 0;
        }

        return $indicatorScores;
    }

    /**
     * Map percentage to category (A-E).
     */
    private function getCategory(float $percentage): string
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 75) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 45) return 'D';
        return 'E';
    }

    /**
     * Get conclusion text based on category.
     */
    private function getConclusion(string $category): string
    {
        $conclusions = [
            'A' => 'Sangat Baik - Kebijakan lingkungan sekolah sudah sangat optimal',
            'B' => 'Baik - Kebijakan lingkungan sekolah sudah cukup baik, perlu peningkatan di beberapa aspek',
            'C' => 'Sedang - Kebijakan lingkungan sekolah masih perlu perbaikan',
            'D' => 'Kurang - Kebijakan lingkungan sekolah perlu perbaikan signifikan',
            'E' => 'Sangat Kurang - Kebijakan lingkungan sekolah perlu perbaikan menyeluruh',
        ];

        return $conclusions[$category] ?? 'Evaluasi selesai';
    }
}
