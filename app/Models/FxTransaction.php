<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FxTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'fx_transactions';

    protected $fillable = [
        'fx_customer_id',
        'date',
        'currency',
        'amount_in',
        'amount_out',
        'rate',
        'myr_converted',
        'myr_out',
        'myr_in',
        'remark',
        'cost_rate',
        'profit',
        'created_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'amount_in'     => 'decimal:4',
        'amount_out'    => 'decimal:4',
        'rate'          => 'decimal:6',
        'myr_converted' => 'decimal:2',
        'myr_out'       => 'decimal:2',
        'myr_in'        => 'decimal:2',
        'cost_rate'     => 'decimal:6',
        'profit'        => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(FxCustomer::class, 'fx_customer_id');
    }

    // ── Formula helpers (per row) ─────────────────────────────────────────────

    /**
     * Sheet formula: F = E*(C-D)
     * CONVERT TO MYR = rate × (buy_in − sell_out)
     */
    public static function calcMyrConverted(float $rate, float $amountIn, float $amountOut): float
    {
        return $rate * ($amountIn - $amountOut);
    }

    /**
     * Sheet formula: L = C*(K-E)
     * PROFIT = buy_amount × (cost_rate − exchange_rate)
     */
    public static function calcProfit(float $amountIn, float $costRate, float $rate): float
    {
        return $amountIn * ($costRate - $rate);
    }
}
