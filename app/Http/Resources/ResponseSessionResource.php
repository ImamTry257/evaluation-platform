<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseSessionResource extends JsonResource
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
            'userId' => $this->user_id,
            'questionnaireId' => $this->questionnaire_id,
            'status' => $this->status,
            'startedAt' => $this->started_at,
            'submittedAt' => $this->submitted_at,
            'remainingSeconds' => $this->remaining_seconds,
            'sessionNumber' => $this->session_number,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'questionnaire' => new QuestionnaireResource($this->whenLoaded('questionnaire')),
            'answers' => ResponseAnswerResource::collection($this->whenLoaded('answers')),
            'result' => new EvaluationResultResource($this->whenLoaded('result')),
        ];
    }
}
