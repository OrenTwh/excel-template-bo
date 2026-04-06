<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Amenity extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'amenities';

    protected $fillable = [
        'title',
        'status',
        'icon',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getIconPathAttribute(){
        return $this->attributes['icon'] ? asset( 'storage/'.$this->attributes['icon'] ) : asset( 'admin/images/logo.png' ) . Helper::assetVersion();
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'd/m/Y H:i:s' );
    }

    protected static $logAttributes = [
        'title',
        'icon',
        'status',
    ];

    protected static $logName = 'amenity';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} amenity";
    }
}
