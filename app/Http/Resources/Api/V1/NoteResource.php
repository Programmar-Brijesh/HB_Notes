<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
            'chapter_id' => $this->chapter_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'file_name' => $this->file_original_name,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'mime_type' => $this->mime_type,
            'download_count' => $this->download_count,
            'order_column' => $this->order_column,
            'has_thumbnail' => !empty($this->thumbnail_path),
            'preview_url' => route('api.v1.notes.preview', ['note' => $this->id]),
            'download_url' => route('api.v1.notes.download', ['note' => $this->id]),
            'thumbnail_url' => !empty($this->thumbnail_path)
                ? route('api.v1.notes.thumbnail', ['note' => $this->id])
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
