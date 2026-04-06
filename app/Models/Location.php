<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Location extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
        'description',
        'code',
        'latitude',
        'longitude',
        'active',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'active' => 'boolean',
        'status' => 'integer',
    ];

    // Parent location (e.g., Area belongs to State)
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    // Child locations (e.g., State has many Areas)
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    // Courts in this location
    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    // Get all ancestors (parent, grandparent, etc.)
    public function ancestors()
    {
        $ancestors = collect([]);
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    // Get full path (e.g., "Malaysia > Kuala Lumpur > KLCC")
    public function getFullPathAttribute()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    // Scope for getting only root locations (no parent)
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope for getting by type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope for getting active locations
    public function scopeActive($query)
    {
        return $query->where('status', 10);
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = [
        'parent_id',
        'name',
        'slug',
        'type',
        'description',
        'code',
        'latitude',
        'longitude',
        'active',
        'status',
    ];

    protected static $logName = 'Location';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} ";
    }
}
