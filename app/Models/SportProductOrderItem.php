<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportProductOrderItem extends Model
{
    protected $table = 'sport_product_order_items';

    protected $fillable = ['order_id', 'variant_id', 'name', 'price', 'quantity', 'image'];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
    ];
}
