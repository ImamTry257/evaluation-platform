<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseSession;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/dashboard
     * Get dashboard summary stats + monitoring data.
     */
    public function index(Request $request)
    {
        // === Summary Stats ===
        $totalRespondent = User::where('role', 'RESPONDENT')->count();

        $submittedCount = ResponseSession::where('status', 'submitted')->count();
        $inProgressCount = ResponseSession::where('status', 'in_progress')->count();
        $notStartedCount = $totalRespondent - $submittedCount - $inProgressCount;
        if ($notStartedCount < 0) $notStartedCount = 0;

        // === Weekly Progress (last 7 days) ===
        $weeklyData = ResponseSession::where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays(7)->startOfDay())
            ->select(
                DB::raw('DAYOFWEEK(submitted_at) as day_of_week'),
                DB::raw('DATE(submitted_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'day_of_week')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                return [
                    'day' => $dayNames[$item->day_of_week - 1] ?? '-',
                    'date' => $item->date,
                    'value' => $item->count,
                ];
            });

        // Fill missing days with 0
        $filledWeekly = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][now()->subDays($i)->dayOfWeek];
            $found = $weeklyData->firstWhere('date', $date);
            $filledWeekly[] = [
                'day' => $dayName,
                'date' => $date,
                'value' => $found ? $found['value'] : 0,
            ];
        }

        // === Real-time Monitoring (active sessions) ===
        $activeSessions = ResponseSession::with(['user', 'questionnaire'])
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($session) {
                $totalQuestions = $session->questionnaire
                    ? $session->questionnaire->components->sum(function ($c) {
                        return $c->subComponents->sum(function ($sc) {
                            return $sc->indicators->sum(function ($i) {
                                return $i->questions->count();
                            });
                        });
                    })
                    : 0;

                $answeredCount = $session->answers()->count();
                $progress = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;

                $remaining = $session->remaining_seconds ?? 0;
                $minutes = floor($remaining / 60);
                $seconds = $remaining % 60;

                return [
                    'id' => $session->id,
                    'userName' => $session->user->name ?? '-',
                    'userEmail' => $session->user->email ?? '-',
                    'questionnaireTitle' => $session->questionnaire->title ?? '-',
                    'answeredCount' => $answeredCount,
                    'totalQuestions' => $totalQuestions,
                    'progress' => $progress,
                    'remainingSeconds' => $remaining,
                    'timeRemaining' => $remaining > 0
                        ? sprintf('%02dm %02ds', $minutes, $seconds)
                        : 'Habis',
                    'startedAt' => $session->started_at,
                    'updatedAt' => $session->updated_at,
                ];
            });

        return $this->successResponse([
            'summary' => [
                'totalRespondent' => $totalRespondent,
                'submitted' => $submittedCount,
                'inProgress' => $inProgressCount,
                'notStarted' => $notStartedCount,
                'completionPercent' => $totalRespondent > 0
                    ? round(($submittedCount / $totalRespondent) * 100)
                    : 0,
            ],
            'weeklyProgress' => $filledWeekly,
            'activeSessions' => $activeSessions,
        ], 'Dashboard data retrieved successfully');
    }
}
