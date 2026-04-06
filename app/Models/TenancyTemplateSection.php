<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class TenancyTemplateSection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenancy_template_id',
        'section_key',
        'title',
        'content',
        'sort_order',
        'status',
    ];

    protected static $logAttributes = [
        'tenancy_template_id',
        'section_key',
        'title',
        'content',
        'sort_order',
        'status',
    ];

    protected static $logName = 'tenancy_template_sections';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} tenancy template section";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
    public function tenancyTemplate()
    {
        return $this->belongsTo(TenancyTemplate::class);
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
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
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
