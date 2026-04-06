<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class VendingMachineStock extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'vending_machine_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'quantity',
        'old_quantity',
        'last_stock_check',
        'status',
    ];

    public function vendingMachine()
    {
        return $this->belongsTo(VendingMachine::class, 'vending_machine_id');
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
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public static function getFroyoStock($vendingMachineId)
    {
        return self::where('vending_machine_id', $vendingMachineId)
                    ->whereNotNull('froyo_id')
                    ->sum('quantity');
    }

    public static function getSyrupStock($vendingMachineId)
    {
        return self::where('vending_machine_id', $vendingMachineId)
                    ->whereNotNull('syrup_id')
                    ->sum('quantity');
    }

    public static function getToppingStock($vendingMachineId)
    {
        return self::where('vending_machine_id', $vendingMachineId)
                    ->whereNotNull('topping_id')
                    ->sum('quantity');
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'vending_machine_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'quantity',
        'old_quantity',
        'last_stock_check',
        'status',
    ];

    protected static $logName = 'vending_machine_stocks';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vending_machine_stock";
    }
}
