<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FxMasterDaily extends Model
{
    protected $table = 'fx_master_daily';

    protected $fillable = [
        'year', 'month', 'day',
        'sgd', 'thb', 'pgk', 'usdt', 'aud_slow', 'aud_fast',
        'usd1', 'usd2', 'usd3', 'usd4',
        'balance_myr', 'need_pay',
    ];

    // Currency columns in display order (matches master sheet C–L)
    public static array $currencyCols = [
        'sgd' => 'SGD', 'thb' => 'THB', 'pgk' => 'PGK', 'usdt' => 'USDT',
        'aud_slow' => 'AUD SLOW', 'aud_fast' => 'AUD FAST',
        'usd1' => '$', 'usd2' => '$', 'usd3' => '$', 'usd4' => '$',
    ];

    public static function forMonth(int $year, int $month): \Illuminate\Support\Collection
    {
        return static::where('year', $year)->where('month', $month)
            ->get()->keyBy('day');
    }

    public static function upsertDay(int $year, int $month, int $day, string $col, mixed $value): static
    {
        $row = static::firstOrNew(['year' => $year, 'month' => $month, 'day' => $day]);
        $row->$col = $value;
        $row->save();
        return $row;
    }
}
