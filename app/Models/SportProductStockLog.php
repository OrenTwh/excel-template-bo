<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportProductStockLog extends Model
{
    use HasFactory;

    protected $table = 'sport_product_stock_logs';

    protected $fillable = [
        'variant_id',
        'quantity_change',
        'quantity_after',
        'type',
        'reference_id',
        'notes',
        'created_by_admin_id',
    ];

    protected $casts = [
        'variant_id'          => 'integer',
        'quantity_change'     => 'integer',
        'quantity_after'      => 'integer',
        'reference_id'        => 'integer',
        'created_by_admin_id' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(SportProductVariant::class, 'variant_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
