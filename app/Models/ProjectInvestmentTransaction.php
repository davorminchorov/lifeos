<?php

namespace App\Models;

use App\Services\CurrencyService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvestmentTransaction extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * Cached CurrencyService instance.
     */
    private ?CurrencyService $currencyServiceCache = null;

    /**
     * Get the CurrencyService instance, caching it for reuse.
     */
    private function getCurrencyService(): CurrencyService
    {
        if ($this->currencyServiceCache === null) {
            $this->currencyServiceCache = app(CurrencyService::class);
        }

        return $this->currencyServiceCache;
    }

    protected $fillable = [
        'project_investment_id',
        'user_id',
        'amount',
        'currency',
        'transaction_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function projectInvestment(): BelongsTo
    {
        return $this->belongsTo(ProjectInvestment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get formatted amount with currency
    public function getFormattedAmountAttribute(): string
    {
        return $this->getCurrencyService()->format($this->amount, $this->currency);
    }
}
