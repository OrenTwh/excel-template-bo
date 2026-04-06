<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportProductStock extends Model
{
    use HasFactory;

    protected $table = 'sport_product_stocks';

    protected $fillable = [
        'variant_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'variant_id'        => 'integer',
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(SportProductVariant::class, 'variant_id');
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
