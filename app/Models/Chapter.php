<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

class Chapter extends Model
{
    use HasFactory;

    protected $table = 'chapters';

    protected $fillable = [
        'subject_id',
        'chapter_number',
        'name',
        'slug',
        'description',
        'is_active',
        'order_column',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'chapter_number' => 'integer',
        'is_active' => 'boolean',
        'order_column' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Get the subject this chapter belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the notes and PDFs under this chapter.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the MCQ questions under this chapter.
     */
    public function mcqQuestions(): HasMany
    {
        return $this->hasMany(McqQuestion::class);
    }

    /**
     * Get the academic class through the subject.
     */
    public function academicClass(): HasOneThrough
    {
        return $this->hasOneThrough(
            AcademicClass::class,
            Subject::class,
            'id',
            'id',
            'subject_id',
            'academic_class_id'
        );
    }
}
