<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CartMeta extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_bundle_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'froyo_quantity',
        'syrup_quantity',
        'topping_quantity',
        'status',
        'froyos',
        'syrups',
        'toppings',
        'total_price',
        'discount',
        'additional_charges',
    ];

    public function cart() {
        return $this->belongsTo( Cart::class, 'cart_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function productBundle() {
        return $this->belongsTo( ProductBundle::class, 'product_bundle_id' );
    }

    public function froyo()
    {
        return $this->belongsTo(Froyo::class, 'froyo_id');
    }

    public function syrup()
    {
        return $this->belongsTo(Syrup::class, 'syrup_id');
    }

    public function topping()
    {
        return $this->belongsTo(Topping::class, 'topping_id');
    }

    public function getFroyosMetasAttribute()
    {
        $froyoIds = json_decode($this->attributes['froyos'], true);
    
        if (empty($froyoIds)) {
            return collect(); // Return an empty collection if no froyo IDs
        }

        $froyo = Froyo::whereIn('id', $froyoIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $froyo->append('image_path');
    
        return $froyo;
    }

    public function getSyrupsMetasAttribute()
    {
        $syrupIds = json_decode($this->attributes['syrups'], true);
    
        if (empty($syrupIds)) {
            return collect(); // Return an empty collection if no syrup IDs
        }
    
        $syrup = Syrup::whereIn('id', $syrupIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $syrup->append('image_path');
    
        return $syrup;
    }

    public function getToppingsMetasAttribute()
    {
        $toppingIds = json_decode($this->attributes['toppings'], true);
    
        if (empty($toppingIds)) {
            return collect(); // Return an empty collection if no topping IDs
        }
    
        $topping = Topping::whereIn('id', $toppingIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $topping->append('image_path');
    
        return $topping;
    }

    public function getProductsMetasAttribute()
    {
        return Product::whereIn('id', $this->attributes['products'])
        ->select('id', 'title', 'price')
        ->get();
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
        'cart_id',
        'product_id',
        'product_bundle_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'froyo_quantity',
        'syrup_quantity',
        'topping_quantity',
        'status',
        'froyos',
        'syrups',
        'toppings',
        'total_price',
        'discount',
        'additional_charges',
    ];

    protected static $logName = 'cart_metas';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} cart meta";
    }
}
