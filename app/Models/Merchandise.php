<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Merchandise extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable =
    [
        'title',
        'thumbnail',
        'price',
        'description',
        'status'
    ];

    public function galleries()
    {
        return $this->hasMany( MerchandiseGallery::class );
    }

    public function orderItems()
    {
        return $this->hasMany( MerchandiseOrderItem::class );
    }

    public function stock()
    {
        return $this->hasOne( MerchandiseStock::class );
    }

    public function stockMovements()
    {
        return $this->hasMany( MerchandiseStockMovement::class );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = 
    [
        'title',
        'thumbnail',
        'price',
        'description',
        'status'
    ];

    protected static $logName = 'Merchandise';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
