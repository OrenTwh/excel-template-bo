<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class TenancyTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'status',
    ];

    protected static $logAttributes = [
        'name',
        'description',
        'created_by',
        'status',
    ];

    protected static $logName = 'tenancy_templates';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} tenancy template";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections()
    {
        return $this->hasMany(TenancyTemplateSection::class);
    }

    /**
     * Accessors
     */
    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            1 => __('datatables.pending'),
            10 => __('datatables.activated'),
            20 => __('datatables.suspended'),
            21 => __('datatables.deactivated'),
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }

    /**
     * Static methods
     */
    public static function statusOptions()
    {
        return [
            1 => __('datatables.pending'),
            10 => __('datatables.activated'),
            20 => __('datatables.suspended'),
            21 => __('datatables.deactivated'),
        ];
    }
}
