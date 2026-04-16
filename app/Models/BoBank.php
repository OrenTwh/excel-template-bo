<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoBank extends Model
{
    use SoftDeletes;

    protected $table = 'bo_banks';

    protected $fillable = [
        'bank_id',
        'display_order',
        'gateway',
        'bank_name',
        'account_name',
        'account_number',
        'balance',
        'remark',
        'config',
        'status',
    ];

    protected $casts = [
        'config' => 'array',
        'balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(BoBankTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
