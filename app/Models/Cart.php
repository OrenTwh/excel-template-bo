<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Cart extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'product_bundle_id',
        'user_id',
        'vending_machine_id',
        'outlet_id',
        'total_price',
        'discount',
        'reference',
        'payment_method',
        'status',
        'voucher_id',
        'taxes',
        'session_key',
        'user_bundle_id',
        'tax',
        'subtotal',
        'additional_charges',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'product_id',
        'product_bundle_id',
        'outlet_id',
    ];

    public function voucher() {
        return $this->belongsTo( Voucher::class, 'voucher_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function userBundle() {
        return $this->belongsTo( UserBundle::class, 'user_bundle_id' );
    }

    public function productBundle() {
        return $this->belongsTo( ProductBundle::class, 'product_bundle_id' );
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function outlet() {
        return $this->belongsTo( Outlet::class, 'outlet_id' );
    }

    public function vendingMachine() {
        return $this->belongsTo( VendingMachine::class, 'vending_machine_id' );
    }

    public function vendingMachineTitle() {
        return $this->belongsTo( VendingMachine::class, 'vending_machine_id' );
    }

    public function cartMetas() {
        return $this->hasMany( CartMeta::class, 'cart_id' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'product_id',
        'product_bundle_id',
        'user_id',
        'vending_machine_id',
        'outlet_id',
        'total_price',
        'discount',
        'reference',
        'payment_method',
        'status',
        'voucher_id',
        'taxes',
        'session_key',
        'user_bundle_id',
        'tax',
        'subtotal',
        'additional_charges',
    ];

    protected static $logName = 'carts';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} cart";
    }
}
