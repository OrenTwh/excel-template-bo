<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class TenancyAgreementVersion extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenancy_agreement_id',
        'version_number',
        'title',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected static $logAttributes = [
        'tenancy_agreement_id',
        'version_number',
        'title',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected static $logName = 'tenancy_agreement_versions';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} tenancy agreement version";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
    public function tenancyAgreement()
    {
        return $this->belongsTo(TenancyAgreement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sections()
    {
        return $this->hasMany(TenancyAgreementSection::class);
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
            11 => __('datatables.approved'),
            20 => __('datatables.suspended'),
            21 => __('datatables.rejected'),
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }

    public function getIsApprovedAttribute()
    {
        return !empty($this->attributes['approved_at']) && !empty($this->attributes['approved_by']);
    }

    /**
     * Scopes
     */
    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    /**
     * Static methods
     */
    public static function statusOptions()
    {
        return [
            1 => __('datatables.pending'),
            10 => __('datatables.activated'),
            11 => __('datatables.approved'),
            20 => __('datatables.suspended'),
            21 => __('datatables.rejected'),
        ];
    }
}
