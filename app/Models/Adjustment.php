<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Adjustment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'causer_id',
        'warehouse_id',
        'attachment',
        'remarks',
        'reference',
        'adjustment_date',
        'status',
    ];

    public function causer() {
        return $this->belongsTo( Administrator::class, 'causer_id' );
    }

    public function warehouse() {
        return $this->belongsTo( Warehouse::class, 'warehouse_id' );
    }

    public function AdjustmentMetas()
    {
        return $this->hasMany(AdjustmentMeta::class, 'adjustment_id');
    }

    public function getAttachmentPathAttribute() {
        return $this->attributes['attachment'] ? asset( 'storage/' . $this->attributes['attachment'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'causer_id',
        'warehouse_id',
        'attachment',
        'remarks',
        'reference',
        'adjustment_date',
        'status',
    ];

    protected static $logName = 'adjustments';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} adjustment";
    }
}
