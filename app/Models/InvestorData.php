<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorData extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'browserless_connection_id',
        'portal_name',
        'raw_data',
        'tables',
        'funds',
        'screenshot',
        'crawled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'tables' => 'array',
            'funds' => 'array',
            'crawled_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the investor data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the browserless connection that owns the investor data.
     */
    public function browserlessConnection(): BelongsTo
    {
        return $this->belongsTo(BrowserlessConnection::class);
    }

    /**
     * Get the latest investor data for a user.
     */
    public static function latestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->orderBy('crawled_at', 'desc')
            ->first();
    }

    /**
     * Get all tables as a formatted array.
     */
    public function getFormattedTables(): array
    {
        if (empty($this->tables)) {
            return [];
        }

        $formatted = [];
        foreach ($this->tables as $table) {
            $formatted[] = [
                'headers' => $table['headers'] ?? [],
                'rows' => $table['rows'] ?? [],
            ];
        }

        return $formatted;
    }

    /**
     * Check if the data contains any fund information.
     */
    public function hasFundData(): bool
    {
        return !empty($this->funds) || !empty($this->tables);
    }
}
