<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class ProjectPrice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id',
        'currency_code',
        'build_up_area_psf',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'status',
    ];

    protected $casts = [
        'build_up_area_psf' => 'decimal:2',
        'selling_price_psf' => 'decimal:2',
        'selling_price_unit' => 'decimal:2',
        'maintenance_fee_psf' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    /**
     * Scope to get only active prices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 10);
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'project_id',
        'currency_code',
        'build_up_area_psf',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'status',
    ];

    protected static $logName = 'ProjectPrice';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} project price for currency {$this->currency_code}";
    }
}
