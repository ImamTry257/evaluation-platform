<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResultDetailResource extends JsonResource
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
            'evaluationResultId' => $this->evaluation_result_id,
            'indicatorId' => $this->indicator_id,
            'score' => $this->score,
            'percentage' => $this->percentage,
            'category' => $this->category,
            'recommendation' => $this->recommendation,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'indicator' => new IndicatorResource($this->whenLoaded('indicator')),
        ];
    }
}
