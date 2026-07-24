<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Same as QuestionListResource for now; separated for future extensibility.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return (new QuestionListResource($this))->toArray($request);
    }
}
