<?php

namespace App\Support;

use App\Enums\QuestionnaireAvailabilityReason;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Penentu availabilitas kuesioner untuk responden.
 *
 * Memusatkan logika "kenapa tidak ada kuesioner aktif" agar reusable
 * dan mudah diuji, sekaligus menghasilkan struktur data yang siap dirender
 * oleh halaman empty-state kuesioner.
 */
class QuestionnaireAvailability
{
    /**
     * Hitung status ketersediaan kuesioner aktif untuk responden.
     *
     * @param Questionnaire|null $questionnaire kuesioner published aktif, atau null bila tak ada.
     * @param User|null $user responden yang sedang login.
     * @return array{
     *   available: bool,
     *   reason: QuestionnaireAvailabilityReason|null,
     *   period: array,
     *   estimatedMinutes: int|null
     * }
     */
    public static function build(?Questionnaire $questionnaire, ?User $user = null): array
    {
        // 1. Tidak ada kuesioner published sama sekali
        if (!$questionnaire) {
            return [
                'available' => false,
                'reason' => QuestionnaireAvailabilityReason::NO_QUESTIONNAIRE,
                'period' => self::periodShape(),
                'estimatedMinutes' => null,
            ];
        }

        $period = $questionnaire->evaluationPeriod;
        $today = Carbon::now();

        // 2. Cek status periode
        if ($period) {
            $periodOpen = $period->is_active
                && $period->start_date
                && $period->end_date
                && $today->between($period->start_date, $period->end_date);

            if ($period->is_active && $period->end_date && $today->greaterThan($period->end_date)) {
                return [
                    'available' => false,
                    'reason' => QuestionnaireAvailabilityReason::PERIOD_ENDED,
                    'period' => self::periodShape($period),
                    'estimatedMinutes' => $questionnaire->duration_minutes,
                ];
            }

            if (!$periodOpen) {
                return [
                    'available' => false,
                    'reason' => QuestionnaireAvailabilityReason::PERIOD_NOT_OPEN,
                    'period' => self::periodShape($period),
                    'estimatedMinutes' => $questionnaire->duration_minutes,
                ];
            }
        }

        // 3. Responden sudah pernah submit pada kuesioner ini?
        if ($user) {
            $alreadySubmitted = $questionnaire->responseSessions()
                ->where('user_id', $user->id)
                ->where('status', 'submitted')
                ->exists();

            if ($alreadySubmitted) {
                return [
                    'available' => false,
                    'reason' => QuestionnaireAvailabilityReason::ALREADY_SUBMITTED,
                    'period' => self::periodShape($period),
                    'estimatedMinutes' => $questionnaire->duration_minutes,
                ];
            }
        }

        // 4. Kuesioner aktif tersedia
        return [
            'available' => true,
            'reason' => null,
            'period' => self::periodShape($period),
            'estimatedMinutes' => $questionnaire->duration_minutes,
        ];
    }

    /**
     * Normalisasi data periode yang dirender empty-state.
     */
    protected static function periodShape($period = null): array
    {
        if (!$period) {
            return [
                'id' => null,
                'name' => null,
                'status' => 'menunggu_buka',
                'startDate' => null,
                'endDate' => null,
            ];
        }

        $today = Carbon::now();
        $status = 'menunggu_buka';
        if ($period->end_date && $today->greaterThan($period->end_date)) {
            $status = 'berakhir';
        }

        return [
            'id' => $period->id,
            'name' => $period->name,
            'status' => $status,
            'startDate' => optional($period->start_date)->toIso8601String(),
            'endDate' => optional($period->end_date)->toIso8601String(),
        ];
    }
}