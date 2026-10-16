<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McqImportLog extends Model
{
    use HasFactory;

    protected $table = 'mcq_import_logs';

    protected $fillable = [
        'import_token',
        'user_id',
        'chapter_id',
        'file_name',
        'stored_file_path',
        'total_rows',
        'imported_count',
        'duplicate_count',
        'invalid_count',
        'failed_count',
        'status',
        'preview_data',
        'error_data',
        'confirmed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'chapter_id' => 'integer',
        'total_rows' => 'integer',
        'imported_count' => 'integer',
        'duplicate_count' => 'integer',
        'invalid_count' => 'integer',
        'failed_count' => 'integer',
        'preview_data' => 'array',
        'error_data' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'completed_with_errors'], true);
    }
}
