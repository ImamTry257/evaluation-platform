<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'questionText' => $this->question_text,
            'weight' => $this->weight,
            'orderNumber' => $this->order_number,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'indicator' => $this->whenLoaded('indicator'),
        ];
    }
}
