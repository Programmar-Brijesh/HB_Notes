<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'chapter_number' => $this->chapter_number,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'order_column' => $this->order_column,
            'notes_count' => $this->whenCounted('notes'),
            'mcqs_count' => $this->whenCounted('mcqQuestions'),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
        ];
    }
}
