<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class VendingMachineProduct extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'vending_machine_id',
        'product_id',
        'quantity',
        'old_quantity',
        'last_stock_check',
        'status',
    ];

    public function vendingMachine()
    {
        return $this->belongsTo(VendingMachine::class, 'vending_machine_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'vending_machine_id',
        'product_id',
        'quantity',
        'old_quantity',
        'last_stock_check',
        'status',
    ];

    protected static $logName = 'vending_machine_products';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} Vending Machine Product";
    }
}
