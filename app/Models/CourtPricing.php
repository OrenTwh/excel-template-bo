<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CourtPricing extends Model
{
    use LogsActivity;

    protected $fillable = [
        'court_id',
        'label',
        'time_from',
        'time_to',
        'min_courts',
        'price',
    ];

    protected $casts = [
        'court_id'   => 'integer',
        'min_courts' => 'integer',
        'price'      => 'decimal:2',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    protected static $logAttributes = [
        'court_id', 'label', 'time_from', 'time_to', 'min_courts', 'price',
    ];

    protected static $logName = 'CourtPricing';

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
