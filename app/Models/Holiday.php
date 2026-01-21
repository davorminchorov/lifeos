<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'country',
        'date',
        'name',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
