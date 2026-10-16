<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasFactory;

    protected $table = 'notes';

    protected $fillable = [
        'chapter_id',
        'title',
        'slug',
        'description',
        'pdf_path',
        'file_original_name',
        'file_size',
        'mime_type',
        'status',
        'thumbnail_path',
        'is_active',
        'order_column',
        'download_count',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'order_column' => 'integer',
        'download_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    /**
     * Relationship: The chapter this note belongs to.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Relationship: The subject derived through chapter.
     */
    public function subject(): HasOneThrough
    {
        return $this->hasOneThrough(
            Subject::class,
            Chapter::class,
            'id',          // Foreign key on chapters table (chapters.id)
            'id',          // Foreign key on subjects table (subjects.id)
            'chapter_id',  // Local key on notes table (notes.chapter_id)
            'subject_id'   // Local key on chapters table (chapters.subject_id)
        );
    }

    /**
     * Accessor: Derive Academic Class via Chapter and Subject.
     */
    public function getAcademicClassAttribute(): ?AcademicClass
    {
        return $this->chapter?->subject?->academicClass;
    }

    /**
     * Scope: Only published and active notes (for student portal/API).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    /**
     * Scope: Draft notes.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the note is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    /**
     * Check if the note is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Accessor: Human readable file size (e.g., "2.4 MB", "340 KB").
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
