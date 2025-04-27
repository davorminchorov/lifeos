<?php

namespace App\Investments\Projections;

use Illuminate\Database\Eloquent\Model;

class TransactionList extends Model
{
    protected $table = 'investment_transaction_list';

    protected $fillable = [
        'id',
        'investment_id',
        'type',
        'amount',
        'date',
        'notes'
    ];

    // Casting attributes
    protected $casts = [
        'amount' => 'float',
        'date' => 'date',
    ];

    public function investment()
    {
        return $this->belongsTo(InvestmentList::class, 'investment_id');
    }
}
