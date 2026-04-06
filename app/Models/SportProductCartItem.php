<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportProductCartItem extends Model
{
    protected $table = 'sport_product_cart_items';

    protected $fillable = ['cart_id', 'variant_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function variant()
    {
        return $this->belongsTo(SportProductVariant::class, 'variant_id');
    }
}
