<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class TenancyAgreement extends Model
{
    use HasFactory, LogsActivity;

    const STATUS = [
        1 => 'Pending',
        10 => 'Activated',
        11 => 'Approved',
        20 => 'Suspended',
        21 => 'Cancelled',
        30 => 'Expired',
    ];

    protected $fillable = [
        'agreement_number',
        'tenancy_template_id',
        'property_rental_id',
        'property_short_rental_id',
        'property_id',
        'property_unit_id',
        'booking_id',
        'user_id',
        'agent_id',
        'signature',
        'owner_signature',
        'created_by',
        'last_edited_by',
        'start_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    protected static $logAttributes = [
        'agreement_number',
        'tenancy_template_id',
        'property_rental_id',
        'property_short_rental_id',
        'property_id',
        'property_unit_id',
        'booking_id',
        'user_id',
        'agent_id',
        'signature',
        'owner_signature',
        'created_by',
        'last_edited_by',
        'start_date',
        'status',
    ];

    protected static $logName = 'tenancy_agreements';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} tenancy agreement";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
    public function template()
    {
        return $this->belongsTo(TenancyTemplate::class, 'tenancy_template_id');
    }

    public function rental()
    {
        return $this->belongsTo(PropertyRental::class, 'property_rental_id');
    }

    public function shortRental()
    {
        return $this->belongsTo(PropertyShortRental::class, 'property_short_rental_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function propertyRental()
    {
        return $this->belongsTo(PropertyRental::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function creator()
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }

    public function lastEditor()
    {
        return $this->belongsTo(Administrator::class, 'last_edited_by');
    }

    public function versions()
    {
        return $this->hasMany(TenancyAgreementVersion::class);
    }

    public function latestVersion()
    {
        return $this->hasOne(TenancyAgreementVersion::class)->latest('version_number');
    }

    public function currentVersion()
    {
        return $this->hasOne(TenancyAgreementVersion::class)->latest('version_number');
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
            21 => __('datatables.cancelled'),
            30 => __('datatables.expired'),
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }

    /**
     * Get the PDF download path for this tenancy agreement
     */
    public function getPdfDownloadPathAttribute()
    {
        return route('admin.tenancy_agreement.downloadPdf', ['id' => $this->encrypted_id]);
    }

    /**
     * Get the PDF download path for API (without admin prefix)
     */
    public function getPdfDownloadApiPathAttribute()
    {
        // Check if an API route exists, otherwise use admin route
        if (\Route::has('api.tenancy_agreement.downloadPdf')) {
            return route('api.tenancy_agreement.downloadPdf', ['id' => $this->id]);
        }

        return route('admin.tenancy_agreement.downloadPdf', ['id' => $this->encrypted_id]);
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
            21 => __('datatables.cancelled'),
            30 => __('datatables.expired'),
        ];
    }
}
