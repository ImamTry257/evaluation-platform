<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseAnswerResource extends JsonResource
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
            'questionId' => $this->question_id,
            'score' => $this->score,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'question' => new QuestionResource($this->whenLoaded('question')),
        ];
    }
}
