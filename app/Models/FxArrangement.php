<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FxArrangement extends Model
{
    use SoftDeletes;

    protected $table = 'fx_arrangements';

    protected $fillable = [
        'fx_customer_id',
        'date',
        'account_number',
        'beneficiary_name',
        'bank',
        'arranging_amount',
        'done_amount',
        'is_done',
        'processed_by',
    ];

    protected $casts = [
        'date'             => 'date',
        'arranging_amount' => 'decimal:2',
        'done_amount'      => 'decimal:2',
        'is_done'          => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(FxCustomer::class, 'fx_customer_id');
    }
}
