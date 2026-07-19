<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubComponentResource extends JsonResource
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
            'componentId' => $this->component_id,
            'name' => $this->name,
            'description' => $this->description,
            'orderNumber' => $this->order_number,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'component' => $this->whenLoaded('component'),
        ];
    }
}
