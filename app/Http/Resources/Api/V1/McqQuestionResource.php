<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McqQuestionResource extends JsonResource
{
    /**
     * Whether to expose the correct answer and explanation.
     */
    protected bool $includeAnswer = false;

    /**
     * Setter to explicitly enable answer reveal.
     */
    public function withAnswer(bool $show = true): self
    {
        $this->includeAnswer = $show;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if study mode or include_answers was passed in request
        $mode = strtolower(trim((string) $request->input('mode', 'practice')));
        $requestedStudyMode = $mode === 'study' || $request->boolean('include_answers', false);

        $showAnswer = $this->includeAnswer || $requestedStudyMode;

        $data = [
            'id' => $this->id,
            'chapter_id' => $this->chapter_id,
            'question' => $this->question,
            'option_a' => $this->option_a,
            'option_b' => $this->option_b,
            'option_c' => $this->option_c,
            'option_d' => $this->option_d,
            'difficulty' => $this->difficulty,
            'order_column' => $this->order_column,
        ];

        if ($showAnswer) {
            $data['correct_option'] = $this->correct_option;
            $data['explanation'] = $this->explanation;
        }

        return $data;
    }
}
