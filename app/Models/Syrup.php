<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Syrup extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'code',
        'title',
        'description',
        'image',
        'ingredients',
        'nutritional_values',
        'price',
        'status',
        'measurement_unit',
        'quantity_per_serving',
    ];

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'code',
        'title',
        'description',
        'image',
        'ingredients',
        'nutritional_values',
        'price',
        'status',
        'measurement_unit',
        'quantity_per_serving',
    ];

    protected static $logName = 'syrups';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} syrup";
    }
}
