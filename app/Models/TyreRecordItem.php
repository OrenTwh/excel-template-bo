<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TyreRecordItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tyre_record_id',
        'tyre_id',
        'vendor_id',
        'category',
        'serial_number',
        'cost_per_pcs',
        'qty_in',
        'qty_out',
        'selling_price',
        'remarks',
        'bill_to',
    ];

    public function tyre() {
        return $this->belongsTo( Tyre::class, 'tyre_id' );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'tyre_record_id',
        'tyre_id',
        'category',
        'serial_number',
        'cost_per_pcs',
        'qty_in',
        'qty_out',
        'selling_price',
        'remarks',
        'bill_to',
    ];

    protected static $logName = 'tyre_record_items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} tyre record item";
    }
}
