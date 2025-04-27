<?php

namespace App\Investments\Projections;

use Illuminate\Database\Eloquent\Model;

class ValuationList extends Model
{
    protected $table = 'investment_valuation_list';

    protected $fillable = [
        'id',
        'investment_id',
        'value',
        'date',
        'notes'
    ];

    // Casting attributes
    protected $casts = [
        'value' => 'float',
        'date' => 'date',
    ];

    public function investment()
    {
        return $this->belongsTo(InvestmentList::class, 'investment_id');
    }
}
