<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
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
            'academic_class_id' => $this->academic_class_id,
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'description' => $this->description,
            'order_column' => $this->order_column,
            'chapters_count' => $this->whenCounted('chapters'),
            'academic_class' => new AcademicClassResource($this->whenLoaded('academicClass')),
        ];
    }
}
