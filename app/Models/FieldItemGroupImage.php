<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldItemGroupImage extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'field_item_group_id',
        'icon',
        'image',
        'status',
    ];

    // Relationships
    public function fieldItemGroup()
    {
        return $this->belongsTo(FieldItemGroup::class, 'field_item_group_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'field_item_group_id',
        'icon',
        'image',
        'status',
    ];

    protected static $logName = 'FieldItemGroupImage';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
