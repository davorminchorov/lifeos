<?php

namespace App\Investments\Projections;

use Illuminate\Database\Eloquent\Model;

class InvestmentList extends Model
{
    protected $table = 'investment_list';

    protected $fillable = [
        'id',
        'name',
        'type',
        'institution',
        'account_number',
        'initial_investment',
        'current_value',
        'roi',
        'start_date',
        'end_date',
        'description',
        'total_invested',
        'total_withdrawn',
        'last_valuation_date'
    ];

    // Casting attributes
    protected $casts = [
        'initial_investment' => 'float',
        'current_value' => 'float',
        'roi' => 'float',
        'total_invested' => 'float',
        'total_withdrawn' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_valuation_date' => 'date',
    ];
}
