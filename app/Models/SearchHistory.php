<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class SearchHistory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'search_type',
        'search_query',
        'search_filters', // JSON field for storing filter parameters
        'results_count',
        'ip_address',
        'user_agent',
        'status',
        'project_id',
        'property_id',
        'property_unit_id',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function project() {
        return $this->belongsTo( Project::class, 'project_id' );
    }

    public function property() {
        return $this->belongsTo( Property::class, 'property_id' );
    }

    public function propertyUnit() {
        return $this->belongsTo( PropertyUnit::class, 'property_unit_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected $casts = [
        'search_filters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static $logAttributes = [
        'user_id',
        'search_type',
        'search_query',
        'search_filters',
        'results_count',
        'ip_address',
        'user_agent',
        'status',
        'project_id',
        'project_id',
        'property_id',
        'property_unit_id',
    ];

    protected static $logName = 'SearchHistory';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
