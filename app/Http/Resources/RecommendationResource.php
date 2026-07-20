<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
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
            'indicatorId' => $this->indicator_id,
            'minScore' => $this->min_score,
            'maxScore' => $this->max_score,
            'category' => $this->category,
            'recommendationText' => $this->recommendation_text,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'indicator' => new IndicatorResource($this->whenLoaded('indicator')),
        ];
    }
}
