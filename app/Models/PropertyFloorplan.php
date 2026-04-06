<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;
use Carbon\Carbon;

class PropertyFloorplan extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'property_id',
        'sequence',
        'image',
        'remarks',
        'bathrooms',
        'bedrooms',
        'status',
        'balcony',
        'storeroom',
        'parking_spaces',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getFloorplanPathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'property_id',
        'sequence',
        'image',
        'remarks',
        'bathrooms',
        'bedrooms',
        'status',
        'balcony',
        'storeroom',
        'parking_spaces',
    ];

    protected static $logName = 'property_floorplans';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} property floorplan";
    }
}
