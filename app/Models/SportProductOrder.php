<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Helper;

class SportProductOrder extends Model
{
    const STATUS_PENDING_PAYMENT = 1;
    const STATUS_COMPLETED       = 10;
    const STATUS_CANCELLED       = 20;
    const STATUS_EXPIRED         = 21;

    protected $table = 'sport_product_orders';

    protected $fillable = [
        'user_id', 'voucher_id', 'status', 'subtotal', 'discount', 'total', 'notes',
        'order_no', 'payment_gateway', 'payment_gateway_ref', 'payment_attempt',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function items()
    {
        return $this->hasMany(SportProductOrderItem::class, 'order_id');
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
