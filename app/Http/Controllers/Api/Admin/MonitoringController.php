<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseSession;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/sessions
     * List all evaluation sessions with filtering.
     */
    public function index(Request $request)
    {
        $query = ResponseSession::with(['user', 'questionnaire', 'result']);

        // Filter by questionnaire
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaireId', $request->questionnaireId);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by user name or email
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $limit = min($request->get('limit', 15), 100);

        $sessions = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        // Add computed fields
        $sessions->getCollection()->transform(function ($session) {
            $totalQuestions = $session->questionnaire
                ? $session->questionnaire->components->sum(function ($c) {
                    return $c->subComponents->sum(function ($sc) {
                        return $sc->indicators->sum(function ($i) {
                            return $i->questions->count();
                        });
                    });
                })
                : 0;

            $answeredCount = $session->answers->count();
            $progress = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;

            return [
                'id' => $session->id,
                'user' => [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                ],
                'questionnaire' => [
                    'id' => $session->questionnaire->id,
                    'title' => $session->questionnaire->title,
                ],
                'status' => $session->status,
                'startedAt' => $session->startedAt,
                'submittedAt' => $session->submittedAt,
                'remainingSeconds' => $session->remainingSeconds,
                'answeredCount' => $answeredCount,
                'totalQuestions' => $totalQuestions,
                'progress' => $progress,
                'result' => $session->result ? [
                    'overallScore' => $session->result->overallScore,
                    'overallPercentage' => $session->result->overallPercentage,
                    'overallCategory' => $session->result->overallCategory,
                ] : null,
                'createdAt' => $session->created_at,
            ];
        });

        return $this->listResponse($sessions, 'Sessions retrieved successfully');
    }

    /**
     * GET /api/v1/admin/sessions/{sessionId}
     * Get detailed session data including answers and results.
     */
    public function show($sessionId)
    {
        $session = ResponseSession::with([
            'user',
            'questionnaire.components.subComponents.indicators.questions',
            'answers.question.indicator',
            'result.details.indicator',
        ])->find($sessionId);

        if (!$session) {
            return $this->errorResponse('Session not found', 404);
        }

        // Calculate progress
        $totalQuestions = $session->questionnaire->components->sum(function ($c) {
            return $c->subComponents->sum(function ($sc) {
                return $sc->indicators->sum(function ($i) {
                    return $i->questions->count();
                });
            });
        });

        $answeredCount = $session->answers->count();
        $progress = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;

        return $this->successResponse([
            'session' => [
                'id' => $session->id,
                'user' => [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                ],
                'questionnaire' => $session->questionnaire,
                'status' => $session->status,
                'startedAt' => $session->startedAt,
                'submittedAt' => $session->submittedAt,
                'remainingSeconds' => $session->remainingSeconds,
                'answeredCount' => $answeredCount,
                'totalQuestions' => $totalQuestions,
                'progress' => $progress,
                'answers' => $session->answers,
                'result' => $session->result,
            ],
        ], 'Session retrieved successfully');
    }
}
