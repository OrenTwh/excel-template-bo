<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FxCustomer extends Model
{
    use SoftDeletes;

    protected $table = 'fx_customers';

    protected $fillable = [
        'user_id',
        'name',
        'initial_balance',
        'status',
        'check_today',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'check_today'     => 'boolean',
    ];

    // The linked login account (nullable — customer may not have a login yet).
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function transactions()
    {
        return $this->hasMany(FxTransaction::class);
    }

    public function arrangements()
    {
        return $this->hasMany(FxArrangement::class);
    }
}
