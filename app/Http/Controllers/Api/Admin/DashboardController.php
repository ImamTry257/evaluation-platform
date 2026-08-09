<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\ResponseSession;
use App\Models\ScoringLevelComponent;
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
        $queryTotalRespondent = User::where('role', 'RESPONDENT')
            ->where('is_active', true);

        // Search by respondent type
        if ($request->has('respondentType') && $request->respondentType) {
            $queryTotalRespondent->where('type', $request->respondentType);
        }

        // Search by city
        if ($request->has('cityName') && $request->cityName) {
            $queryTotalRespondent->where('city_name', strtoupper($request->cityName));
        }

        $totalRespondent = $queryTotalRespondent->count();

        $querySubmittedCount = ResponseSession::where('status', 'submitted')
            ->whereHas('user', fn ($q) => $q->where('is_active', true));

        // Search by respondent type
        if ($request->has('respondentType') && $request->respondentType) {
            $querySubmittedCount->whereHas('user', fn ($q) => $q->where('type', $request->respondentType));
        }

        // Search by city
        if ($request->has('cityName') && $request->cityName) {
            $querySubmittedCount->whereHas('user', fn ($q) => $q->where('city_name', strtoupper($request->cityName)));
        }

        $submittedCount = $querySubmittedCount->count();

        $queryInProgressCount = ResponseSession::where('status', 'in_progress')
            ->whereHas('user', fn ($q) => $q->where('is_active', true));

        // Search by respondent type
        if ($request->has('respondentType') && $request->respondentType) {
            $queryInProgressCount->whereHas('user', fn ($q) => $q->where('type', $request->respondentType));
        }

        // Search by city
        if ($request->has('cityName') && $request->cityName) {
            $queryInProgressCount->whereHas('user', fn ($q) => $q->where('city_name', strtoupper($request->cityName)));
        }

        $inProgressCount = $queryInProgressCount->count();

        $notStartedCount = $totalRespondent - $submittedCount - $inProgressCount;
        if ($notStartedCount < 0) $notStartedCount = 0;

        // === Weekly Progress (last 7 days) ===
        $queryWeeklyData = ResponseSession::where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays(7)->startOfDay())
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->select(
                DB::raw('DAYOFWEEK(submitted_at) as day_of_week'),
                DB::raw('DATE(submitted_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'day_of_week')
            ->orderBy('date');

        // Search by respondent type
        if ($request->has('respondentType') && $request->respondentType) {
            $queryWeeklyData->whereHas('user', fn ($q) => $q->where('type', $request->respondentType));
        }

        // Search by city
        if ($request->has('cityName') && $request->cityName) {
            $queryWeeklyData->whereHas('user', fn ($q) => $q->where('city_name', strtoupper($request->cityName)));
        }
        
        $weeklyData = $queryWeeklyData->get()
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
        $queryActiveSessions = ResponseSession::with(['user', 'questionnaire'])
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->limit(10);

        // Search by respondent type
        if ($request->has('respondentType') && $request->respondentType) {
            $queryActiveSessions->whereHas('user', fn ($q) => $q->where('type', $request->respondentType));
        }

        // Search by city
        if ($request->has('cityName') && $request->cityName) {
            $queryActiveSessions->whereHas('user', fn ($q) => $q->where('city_name', strtoupper($request->cityName)));
        }

        $activeSessions = $queryActiveSessions->get()
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

        // === Instrument Penelitian + Grafik Skor per Komponen ===
        // Instrumen = kuesioner published pertama (urut id), konsisten dengan
        // pola report-chart-component v3. Saat tidak ada published → available=false.
        $publishedQuestionnaire = Questionnaire::with([
                'components' => fn ($q) => $q->withTrashed()->orderBy('order_number'),
            ])
            ->where('status', 'published')
            ->orderBy('id')
            ->first();

        $instrumentAvailable = false;
        $hasData = false;
        $componentCharts = [];

        if ($publishedQuestionnaire) {
            $querySubmittedSessions = ResponseSession::with([
                'answers' => fn ($a) => $a->with([
                    'question' => fn ($q) => $q->withTrashed(),
                    'question.indicator' => fn ($q) => $q->withTrashed(),
                    'question.indicator.subComponent' => fn ($q) => $q->withTrashed(),
                    'question.indicator.subComponent.component' => fn ($q) => $q->withTrashed(),
                ]),
            ])
                ->where('questionnaire_id', $publishedQuestionnaire->id)
                ->where('status', 'submitted')
                ->whereHas('user', fn ($q) => $q->where('is_active', true));

            // Search by respondent type
            if ($request->has('respondentType') && $request->respondentType) {
                $querySubmittedSessions->whereHas('user', fn ($q) => $q->where('type', $request->respondentType));
            }

            // Search by city
            if ($request->has('cityName') && $request->cityName) {
                $querySubmittedSessions->whereHas('user', fn ($q) => $q->where('city_name', strtoupper($request->cityName)));
            }
                
            $submittedSessions = $querySubmittedSessions->get();

            // Data submit global TANPA filter (type/city): pembeda empty state
            // "belum ada data sama sekali" vs "belum ada data yang cocok dengan filter"
            $hasData = ResponseSession::where('questionnaire_id', $publishedQuestionnaire->id)
                ->where('status', 'submitted')
                ->whereHas('user', fn ($q) => $q->where('is_active', true))
                ->exists();

            if ($submittedSessions->isNotEmpty()) {
                $instrumentAvailable = true;

                // Level categories per komponen untuk instrumen ini (mirror exportExcel:
                // sum(score) sesi → lookup score_title between start_from & end_at).
                $scoreLevelsByComponent = ScoringLevelComponent::where('questionnaire_id', $publishedQuestionnaire->id)
                    ->orderBy('start_from')
                    ->get()
                    ->groupBy('component_id');

                foreach ($publishedQuestionnaire->components as $component) {
                    $levels = $scoreLevelsByComponent->get($component->id, collect());

                    // Hitung kategori tiap sesi: sum(score) seluruh jawaban komponen sesi itu,
                    // lalu bucket sesuai rentang scoring_level_component. Skip sesi tanpa jawaban.
                    $categoryCounts = [];
                    $total = 0;
                    foreach ($submittedSessions as $session) {
                        $componentAnswers = $session->answers->filter(function ($answer) use ($component) {
                            return $answer->question
                                && $answer->question->indicator
                                && $answer->question->indicator->subComponent
                                && $answer->question->indicator->subComponent->component_id === $component->id;
                        });

                        if ($componentAnswers->isEmpty()) {
                            continue;
                        }

                        $scoreSum = $componentAnswers->sum('score');
                        $title = $levels->firstWhere(
                            fn ($l) => $scoreSum >= $l->start_from && $scoreSum <= $l->end_at
                        )?->score_title;

                        if ($title) {
                            $categoryCounts[$title] = ($categoryCounts[$title] ?? 0) + 1;
                            $total++;
                        }
                    }

                    // Bangun dist urut start_from; isi count 0 bila tak ada sesi di bucket itu
                    $dist = [];
                    foreach ($levels as $level) {
                        $dist[] = [
                            'title' => ucwords(strtolower(str_replace('_', ' ', $level->score_title))),
                            'scoreTitle' => $level->score_title,
                            'count' => $categoryCounts[$level->score_title] ?? 0,
                            'countPrecentage' => round(( ( $categoryCounts[$level->score_title] ?? 0 ) / $total ) * 100, 2)
                        ];
                    }

                    // Kategori dominan = bucket count terbesar (tie-break: urutan array)
                    $dominant = null;
                    $maxCount = -1;
                    foreach ($dist as $bucket) {
                        if ($bucket['count'] > $maxCount) {
                            $maxCount = $bucket['count'];
                            $dominant = $bucket;
                        }
                    }

                    $componentCharts[] = [
                        'id' => $component->id,
                        'name' => $component->name,
                        'orderNumber' => $component->order_number,
                        'total' => $total,
                        'dist' => $dist,
                        'dominantTitle' => $dominant ? $dominant['title'] : null,
                        'dominantCount' => $dominant ? $dominant['count'] : null,
                    ];
                }
            }
        }

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
            'instrument' => [
                'available' => $instrumentAvailable,
                'hasData' => $hasData,
                'id' => $publishedQuestionnaire?->id,
                'title' => $publishedQuestionnaire?->title,
                'status' => $publishedQuestionnaire?->status,
            ],
            'componentCharts' => $componentCharts,
        ], 'Dashboard data retrieved successfully');
    }
}
