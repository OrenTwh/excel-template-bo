<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'parts_image',
        'theme',
        'image',
        'status',
    ];

    // Relationships
    public function translations()
    {
        return $this->hasMany(FieldItemTranslation::class, 'item_id');
    }

    public function groups()
    {
        return $this->hasMany(FieldItemGroup::class, 'field_item_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getPartsImagePathAttribute() {
        return $this->attributes['parts_image'] ? asset( 'storage/'.$this->attributes['parts_image'] ) : null;
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'slug',
        'type',
        'parts_image',
        'theme',
        'image',
        'status',
    ];

    protected static $logName = 'FieldItem';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
