<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';

    protected $fillable = [
        'academic_class_id',
        'name',
        'code',
        'slug',
        'description',
        'is_active',
        'order_column',
    ];

    protected $casts = [
        'academic_class_id' => 'integer',
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
     * Get the academic class this subject belongs to.
     */
    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    /**
     * Get chapters for this subject.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)
            ->orderBy('chapter_number')
            ->orderBy('order_column');
    }
}
