<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'bo_transactions';

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'customer_phone',
        'type',
        'amount',
        'status',
        'agent_username',
        'bo_bank_id',
        'other_info',
        'transacted_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transacted_at' => 'datetime',
    ];

    public function bank()
    {
        return $this->belongsTo(BoBank::class, 'bo_bank_id');
    }
}
