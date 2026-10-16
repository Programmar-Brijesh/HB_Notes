<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class McqQuestion extends Model
{
    use HasFactory;

    protected $table = 'mcq_questions';

    protected $fillable = [
        'chapter_id',
        'question',
        'question_hash',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'difficulty',
        'status',
        'is_active',
        'order_column',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'is_active' => 'boolean',
        'order_column' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            $model->correct_option = strtoupper(trim((string) $model->correct_option));
            $model->difficulty = strtolower(trim((string) $model->difficulty));
            $model->status = strtolower(trim((string) $model->status));
            $model->question_hash = static::computeQuestionHash($model->question);
        });
    }

    /**
     * Normalize question text for deterministic duplicate comparison.
     */
    public static function normalizeQuestionText(string $text): string
    {
        // Strip UTF-8 BOM, zero-width spaces, and control characters
        $normalized = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        // Normalize whitespace and newlines to a single space
        $normalized = preg_replace('/\s+/u', ' ', $normalized);
        // Trim and lowercase preserving multi-byte/Unicode characters
        return mb_strtolower(trim($normalized), 'UTF-8');
    }

    /**
     * Compute a SHA-1 hash for fast deterministic duplicate index lookups.
     */
    public static function computeQuestionHash(string $text): string
    {
        return sha1(static::normalizeQuestionText($text));
    }

    /**
     * The chapter this question belongs to.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The subject derived through chapter.
     */
    public function subject(): HasOneThrough
    {
        return $this->hasOneThrough(
            Subject::class,
            Chapter::class,
            'id',          // Foreign key on chapters table
            'id',          // Foreign key on subjects table
            'chapter_id',  // Local key on mcq_questions table
            'subject_id'   // Local key on chapters table
        );
    }

    /**
     * Academic Class accessor derived via Subject.
     */
    public function getAcademicClassAttribute(): ?AcademicClass
    {
        return $this->chapter?->subject?->academicClass;
    }

    /**
     * Scope: Only published questions.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    /**
     * Scope: Draft questions.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scopes for difficulty.
     */
    public function scopeEasy(Builder $query): Builder
    {
        return $query->where('difficulty', 'easy');
    }

    public function scopeMedium(Builder $query): Builder
    {
        return $query->where('difficulty', 'medium');
    }

    public function scopeHard(Builder $query): Builder
    {
        return $query->where('difficulty', 'hard');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
