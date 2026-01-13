<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteApplication extends Model
{
    /** @use HasFactory<\Database\Factories\CreditNoteApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'invoice_id',
        'amount_applied',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_applied' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getFormattedAmountAppliedAttribute(): string
    {
        $creditNote = $this->creditNote;
        $amount = $this->amount_applied / 100;
        return app(CurrencyService::class)->format($amount, $creditNote->currency);
    }
}
