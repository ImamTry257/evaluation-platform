<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionListResource extends JsonResource
{
    /**
     * Transform the resource into an array with full hierarchy context.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $indicator = $this->indicator;
        $subComponent = $indicator?->subComponent;
        $component = $subComponent?->component;
        $questionnaire = $component?->questionnaire;

        return [
            'id' => $this->id,
            'questionText' => $this->question_text,
            'weight' => (float) $this->weight,
            'isActive' => (bool) $this->is_active,
            'orderNumber' => $this->order_number,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'indicator' => $indicator ? [
                'id' => $indicator->id,
                'name' => $indicator->name,
                'subComponent' => $subComponent ? [
                    'id' => $subComponent->id,
                    'name' => $subComponent->name,
                    'component' => $component ? [
                        'id' => $component->id,
                        'name' => $component->name,
                        'questionnaire' => $questionnaire ? [
                            'id' => $questionnaire->id,
                            'title' => $questionnaire->title,
                            'period' => $questionnaire->evaluationPeriod ? [
                                'id' => $questionnaire->evaluationPeriod->id,
                                'name' => $questionnaire->evaluationPeriod->name,
                            ] : null,
                        ] : null,
                    ] : null,
                ] : null,
            ] : null,
        ];
    }
}
