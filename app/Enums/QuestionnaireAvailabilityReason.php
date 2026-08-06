<?php

namespace App\Enums;

/**
 * Alasan mengapa tidak ada kuesioner aktif yang tersedia untuk responden.
 *
 * Dipakai sebagai value dari field `reason` pada struktur availabilitas
 * kuesioner di endpoint `GET /api/v1/evaluations/active-questionnaire`.
 */
enum QuestionnaireAvailabilityReason: string
{
    /** Belum ada kuesioner sama sekali yang dibuat admin. */
    case NO_QUESTIONNAIRE = 'no_questionnaire';

    /** Ada kuesioner namun periode evaluasinya belum dibuka/jatuh tempo-nya belum tiba. */
    case PERIOD_NOT_OPEN = 'period_not_open';

    /** Ada kuesioner namun periode evaluasi sudah berakhir. */
    case PERIOD_ENDED = 'period_ended';

    /** Respond sudah pernah menyelesaikan evaluasi pada periode aktif. */
    case ALREADY_SUBMITTED = 'already_submitted';

    /**
     * Label ramah-user yang dipakai untuk chip "Status" di empty-state.
     */
    public function label(): string
    {
        return match ($this) {
            self::NO_QUESTIONNAIRE       => 'Menunggu buka',
            self::PERIOD_NOT_OPEN        => 'Menunggu buka',
            self::PERIOD_ENDED          => 'Periode berakhir',
            self::ALREADY_SUBMITTED     => 'Sudah selesai',
        };
    }
}