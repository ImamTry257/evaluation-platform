<?php

namespace App\Http\Controllers\Api\Respondent;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseAnswerResource;
use App\Http\Resources\ResponseSessionResource;
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
     * GET /api/v1/evaluations/active-questionnaire
     * Get the published questionnaire for respondent to evaluate.
     */
    public function activeQuestionnaire(Request $request)
    {
        $questionnaire = Questionnaire::with([
            'evaluationPeriod',
            'components.subComponents.indicators.questions',
        ])
            ->where('status', 'published')
            ->first();

        if (!$questionnaire) {
            return $this->errorResponse('Tidak ada kuesioner yang tersedia', 404);
        }

        $getIndicatorList = [];
        $numbering = 1;
        $count = 0;
        $indicatorLength = 0;
        foreach($questionnaire->components as $component) {
            foreach($component->subComponents as $subComponent){
                foreach($subComponent->indicators as $indicator){
                    if ( $numbering == 1 ) {
                        $getIndicatorList = [
                            'page'      => $numbering,
                            'indicator' => $indicator->name,
                            'statementList' => $indicator->questions
                        ];
                    }
                    $count += count($indicator->questions);
                    $indicatorLength += count($subComponent->indicators);
                    $numbering++;
                }
            }
        }

        $getIndicatorList['count'] = $count;
        $getIndicatorList['indicatorLength'] = $indicatorLength;

        $questionnaire['session'] = [
            'evaluation' => null,
            'statements' => $getIndicatorList, 
        ];

        return $this->successResponse(
            new \App\Http\Resources\QuestionnaireResource($questionnaire),
            'Kuesioner aktif ditemukan'
        );
    }

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

        $getIndicatorList = [];
        $numbering = 1;
        $count = 0;
        $indicatorLength = 0;
        foreach($questionnaire->components as $component) {
            foreach($component->subComponents as $subComponent){
                foreach($subComponent->indicators as $indicator){
                    if ( $numbering == 1 ) {
                        $getIndicatorList = [
                            'page'      => $numbering,
                            'indicator' => $indicator->name,
                            'statementList' => $indicator->questions
                        ];
                    }
                    $count += count($indicator->questions);
                    $indicatorLength += count($subComponent->indicators);
                    $numbering++;
                }
            }
        }

        $getIndicatorList['count'] = $count;
        $getIndicatorList['indicatorLength'] = $indicatorLength;

        DB::beginTransaction();

        try {
            // Lock — cegah duplikat session saat klik barengan
            $existingSession = ResponseSession::lockForUpdate()
                ->where('user_id', $request->user()->id)
                ->where('questionnaire_id', $request->questionnaireId)
                ->where('status', 'in_progress')
                ->first();

            if ($existingSession) {
                DB::rollBack();
                return $this->successResponse([
                    'session' => [
                        'evaluation' => new ResponseSessionResource($existingSession->load(['answers.question.indicator'])),
                        'statements' => $getIndicatorList,
                    ],
                    'questionnaire' => $questionnaire,
                    'scoringLevels' => ScoringLevel::where('is_active', 1)->orderBy('value', 'asc')->get(),
                    'isResumed' => true,
                ], 'Session resumed successfully');
            }

            // Create new session with order number
            $nextNumber = ResponseSession::where('user_id', $request->user()->id)->max('session_number') ?? 0;
            $session = ResponseSession::create([
                'user_id' => $request->user()->id,
                'questionnaire_id' => $request->questionnaireId,
                'status' => 'in_progress',
                'started_at' => now(),
                'remaining_seconds' => $questionnaire->duration_minutes * 60,
                'session_number' => $nextNumber + 1,
            ]);

            DB::commit();

            return $this->successResponse([
                'session' => [
                    'evaluation' => new ResponseSessionResource($session->load(['answers.question.indicator'])),
                    'statements' => $getIndicatorList,
                ],
                'questionnaire' => $questionnaire,
                'scoringLevels' => ScoringLevel::where('is_active', 1)->orderBy('value', 'asc')->get(),
                'isResumed' => false,
            ], 'Evaluation started successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to start evaluation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/evaluations/{sessionId}
     * Get evaluation session details (for resume).
     */
    public function show(Request $request, $sessionId, $pageId)
    {
        $session = ResponseSession::where('user_id', $request->user()->id)
            ->with(['answers.question.indicator', 'questionnaire.components.subComponents.indicators.questions'])
            ->find($sessionId);

        // Bug 3 fix: null check SEBELUM akses property
        if (!$session) {
            return $this->errorResponse('Session not found', 404);
        }

        // Bug 5 fix: tolak kalau session sudah submitted
        if ($session->status === 'submitted') {
            return $this->errorResponse('Session sudah di-submit. Silakan lihat hasil evaluasi.', 422);
        }

        $getIndicatorList = [];
        $page = 1;
        $count = 0;
        $indicatorLength = 0;
        $numbering = 0;
        foreach($session->questionnaire->components as $component) {
            foreach($component->subComponents as $subComponent){
                foreach($subComponent->indicators as $indicator){

                    if ( $page > 0 ) {
                        if ( $page < $pageId ) {
                            $numbering += count($indicator->questions);
                        }

                        if ( $pageId == 1 ) {
                            $numbering = 0;
                        }
                    }

                    if ( $page == $pageId ) {
                        $getIndicatorList = [
                            'page'      => $page,
                            'indicator' => $indicator->name,
                        ];

                        foreach($indicator->questions as $question) {
                            $numbering++;

                            $getIndicatorList['statementList'][] = [
                                "id" => $question->id,
                                "indicator_id" => $question->indicator_id,
                                "question_text" => $question->question_text,
                                "weight" => $question->weight,
                                "order_number" => $question->order_number,
                                "is_active" => $question->is_active,
                                "number" => $numbering
                            ];
                        }
                    }

                    $count += count($indicator->questions);
                    $indicatorLength += count($subComponent->indicators);
                    $page++;
                }
            }
        }

        $getIndicatorList['count'] = $count;
        $getIndicatorList['indicatorLength'] = $indicatorLength;

        return $this->successResponse([
            'session' => [
                'evaluation' => new ResponseSessionResource($session),
                'statements' => $getIndicatorList,
            ],
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

        DB::beginTransaction();

        try {
            // Lock session — tolak kalau sudah submitted
            $session = ResponseSession::lockForUpdate()
                ->where('user_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->find($sessionId);

            if (!$session) {
                DB::rollBack();
                return $this->errorResponse('Session not found or not in progress', 404);
            }

            // Verify question belongs to this questionnaire
            $question = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
                $q->where('questionnaire_id', $session->questionnaire_id);
            })->find($request->questionId);

            if (!$question) {
                DB::rollBack();
                return $this->errorResponse('Question does not belong to this questionnaire', 422);
            }

            // Upsert answer (unique constraint on response_session_id + question_id)
            $answer = ResponseAnswer::updateOrCreate(
                [
                    'response_session_id' => $session->id,
                    'question_id' => $request->questionId,
                ],
                [
                    'score' => $request->score,
                ]
            );

            DB::commit();

            return $this->successResponse(
                new ResponseAnswerResource($answer->load('question')),
                'Answer saved successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal menyimpan jawaban', 500);
        }
    }

    /**
     * POST /api/v1/evaluations/{sessionId}/submit
     * Submit the evaluation and trigger scoring.
     */
    public function submit(Request $request, $sessionId)
    {
        DB::beginTransaction();

        try {
            // 1. Lock session row — siapapun edit/autoSave harus tunggu
            $session = ResponseSession::lockForUpdate()
                ->where('user_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->find($sessionId);

            if (!$session) {
                DB::rollBack();
                return $this->errorResponse('Session not found or not in progress', 404);
            }

            // 2. Validasi jawaban di DALAM transaksi (setelah lock)
            $totalQuestions = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
                $q->where('questionnaire_id', $session->questionnaire_id);
            })->count();

            $answeredQuestions = $session->answers()->count();

            if ($answeredQuestions < $totalQuestions) {
                DB::rollBack();
                return $this->errorResponse('Please answer all questions before submitting', 422, [
                    'answered' => $answeredQuestions,
                    'total' => $totalQuestions,
                ]);
            }

            // 3. Update status → submitted
            $session->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // 4. Calculate indicator scores
            $indicatorScores = $this->calculateIndicatorScores($session);

            // 5. Calculate overall score (weighted average)
            $overallScore = 0;
            $totalWeight = 0;

            foreach ($indicatorScores as $indicatorId => $data) {
                $overallScore += $data['score'] * $data['totalWeight'];
                $totalWeight += $data['totalWeight'];
            }

            $weightedAverage = $totalWeight > 0 ? $overallScore / $totalWeight : 0;
            $overallPercentage = ($weightedAverage / 7) * 100;
            $overallCategory = $this->getCategory($overallPercentage);

            // 6. Create evaluation result
            $result = EvaluationResult::create([
                'response_session_id' => $session->id,
                'overall_score' => round($weightedAverage, 2),
                'overall_percentage' => round($overallPercentage, 2),
                'overall_category' => $overallCategory,
                'conclusion' => $this->getConclusion($overallCategory),
            ]);

            // 7. Create result details for each indicator
            foreach ($indicatorScores as $indicatorId => $data) {
                $percentage = ($data['score'] / 7) * 100;
                $category = $this->getCategory($percentage);

                $recommendation = Recommendation::where('indicator_id', $indicatorId)
                    ->where('min_score', '<=', $data['score'])
                    ->where('max_score', '>=', $data['score'])
                    ->first();

                EvaluationResultDetail::create([
                    'evaluation_result_id' => $result->id,
                    'indicator_id' => $indicatorId,
                    'score' => round($data['score'], 2),
                    'percentage' => round($percentage, 2),
                    'category' => $category,
                    'recommendation' => $recommendation?->recommendation_text,
                ]);
            }

            // 8. Semua sukses → commit + lepas lock
            DB::commit();

            $result->load('details.indicator');

            return $this->successResponse([
                'result' => new \App\Http\Resources\EvaluationResultResource($result),
            ], 'Evaluation submitted successfully');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Unique constraint violation — duplikat submit
            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse('Evaluasi sudah pernah di-submit', 409);
            }
            return $this->errorResponse('Gagal menyimpan data evaluasi', 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal menyimpan data evaluasi', 500);
        }
    }

    /**
     * GET /api/v1/evaluations/{sessionId}/results
     * Get evaluation results.
     */
    public function results(Request $request, $sessionId)
    {
        $session = ResponseSession::where('user_id', $request->user()->id)
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
            'result' => new \App\Http\Resources\EvaluationResultResource($session->result),
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

        DB::beginTransaction();

        try {
            // Lock session — tunggu kalau submit sedang jalan
            $session = ResponseSession::lockForUpdate()
                ->where('user_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->find($sessionId);

            if (!$session) {
                DB::rollBack();
                return $this->errorResponse('Session not found or not in progress', 404);
            }

            // Update remaining time
            $session->update([
                'remaining_seconds' => $request->remainingSeconds,
            ]);

            // Auto-save answers if provided
            $savedAnswers = [];
            $skippedAnswers = [];

            if ($request->has('answers') && is_array($request->answers)) {
                foreach ($request->answers as $answerData) {
                    $question = Question::whereHas('indicator.subComponent.component', function ($q) use ($session) {
                        $q->where('questionnaire_id', $session->questionnaire_id);
                    })->find($answerData['questionId']);

                    if ($question) {
                        ResponseAnswer::updateOrCreate(
                            [
                                'response_session_id' => $session->id,
                                'question_id' => $answerData['questionId'],
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

            // Semua tersimpan → commit
            DB::commit();

            $response = [
                'remainingSeconds' => $session->remaining_seconds,
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

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal menyimpan jawaban otomatis', 500);
        }
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
        foreach ($indicatorScores as $indicatorId => $data) {
            $indicatorScores[$indicatorId]['score'] = $data['totalWeight'] > 0
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
