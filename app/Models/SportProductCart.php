<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportProductCart extends Model
{
    protected $table = 'sport_product_carts';

    protected $fillable = ['user_id', 'voucher_id'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function items()
    {
        return $this->hasMany(SportProductCartItem::class, 'cart_id');
    }
}
