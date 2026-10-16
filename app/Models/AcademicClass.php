<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class AcademicClass extends Model
{
    use HasFactory;

    protected $table = 'academic_classes';

    protected $fillable = [
        'name',
        'slug',
        'class_number',
        'stream',
        'description',
        'is_active',
    ];

    protected $casts = [
        'class_number' => 'integer',
        'is_active' => 'boolean',
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
     * Get subjects associated with this class.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'academic_class_id')
            ->orderBy('order_column')
            ->orderBy('name');
    }

    /**
     * Get all chapters across all subjects in this class.
     */
    public function chapters(): HasManyThrough
    {
        return $this->hasManyThrough(
            Chapter::class,
            Subject::class,
            'academic_class_id',
            'subject_id'
        );
    }
}
