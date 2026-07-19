<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Converts snake_case DB columns to camelCase for API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'evaluationPeriodId' => $this->evaluation_period_id,
            'title' => $this->title,
            'description' => $this->description,
            'durationMinutes' => $this->duration_minutes,
            'status' => $this->status,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'evaluationPeriod' => $this->whenLoaded('evaluationPeriod'),
        ];
    }
}
