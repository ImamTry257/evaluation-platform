<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Struktur availabilitas kuesioner untuk halaman empty-state responden.
 *
 * Konvensi field camelCase konsisten dengan resource lain di proyek.
 * Saat `available = false`, `questionnaire` dan `scoringLevels` bernilai null
 * sehingga frontend cukup mengecek `data.available` untuk memilih alur render.
 */
class QuestionnaireAvailabilityResource extends JsonResource
{
    /**
     * Resource dibangun dari hasil QuestionnaireAvailability::build().
     *
     * @param array<string, mixed> $resource
     */
    public function toArray(Request $request): array
    {
        return [
            'available' => $this->resource['available'],
            'reason' => $this->resource['reason']?->value,
            'reasonLabel' => $this->resource['reason']?->label(),
            'period' => $this->resource['period'],
            'estimatedMinutes' => $this->resource['estimatedMinutes'],
            'questionnaire' => isset($this->resource['questionnaire'])
                ? new QuestionnaireResource($this->resource['questionnaire'])
                : null,
            'scoringLevels' => $this->resource['scoringLevels'] ?? null,
        ];
    }
}
