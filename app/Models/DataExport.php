<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\DataExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExport extends Model
{
    /** @use HasFactory<DataExportFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'modules',
        'filters',
        'status',
        'disk',
        'file_path',
        'file_name',
        'row_counts',
        'error',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'filters' => 'array',
            'row_counts' => 'array',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDownloadable(): bool
    {
        return $this->isCompleted()
            && $this->file_path !== null
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
