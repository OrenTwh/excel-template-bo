<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoBankTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'bo_bank_transactions';

    protected $fillable = [
        'bo_bank_id',
        'date',
        'description',
        'amount_in',
        'amount_out',
        'time',
        'ref_id',
        'match',
        'fee',
        'remarks',
        'info',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount_in' => 'decimal:2',
        'amount_out' => 'decimal:2',
        'fee' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(BoBank::class, 'bo_bank_id');
    }
}
