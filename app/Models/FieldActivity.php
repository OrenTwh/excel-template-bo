<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldActivity extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'image',
        'price',
        'active',
        'status',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'active'  => 'boolean',
        'status'  => 'integer',
    ];

    public function orderDetails()
    {
        return $this->hasMany( FieldActivityOrderDetail::class, 'field_activity_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'slug',
        'name',
        'description',
        'image',
        'price',
        'active',
        'status',
    ];

    protected static $logName = 'FieldActivity';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
