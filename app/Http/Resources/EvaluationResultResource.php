<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResultResource extends JsonResource
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
            'responseSessionId' => $this->response_session_id,
            'overallScore' => $this->overall_score,
            'overallPercentage' => $this->overall_percentage,
            'overallCategory' => $this->overall_category,
            'conclusion' => $this->conclusion,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'details' => EvaluationResultDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
