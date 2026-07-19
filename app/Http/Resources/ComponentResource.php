<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
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
            'questionnaireId' => $this->questionnaire_id,
            'name' => $this->name,
            'description' => $this->description,
            'orderNumber' => $this->order_number,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'questionnaire' => $this->whenLoaded('questionnaire'),
        ];
    }
}
