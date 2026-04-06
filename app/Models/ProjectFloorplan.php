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

class ProjectFloorplan extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'projects_floorplans';

    protected $fillable = [
        'project_id',
        'sequence',
        'image',
        'remarks',
        'bathrooms',
        'bedrooms',
        'balcony',
        'storeroom',
        'parking_spaces',
        'status',
        'upload_link',
        'additional_attachment',
        'category',
    ];

    public function property()
    {
        return $this->hasOne(Property::class, 'project_floorplan_id');
    }

    // New relationship to handle multiple properties per floorplan
    public function properties()
    {
        return $this->hasMany(Property::class, 'project_floorplan_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getFloorplanPathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getAdditionalAttachmentPathAttribute() {
        return $this->attributes['additional_attachment'] ? asset( 'storage/'.$this->attributes['additional_attachment'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'project_id',
        'sequence',
        'image',
        'remarks',
        'bathrooms',
        'bedrooms',
        'balcony',
        'storeroom',
        'parking_spaces',
        'status',
        'upload_link',
        'additional_attachment',
        'category',
    ];

    protected static $logName = 'projects_floorplans';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} project floorplan";
    }
}
