<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Developer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'company_registration_number',
        'license_number',
        'developer_type',
    
        // Contact Information
        'email',
        'phone',
        'fax',
        'website',
    
        // Address
        'address',
        'city',
        'state',
        'postcode',
        'country',
    
        // Business Details
        'established_date',
        'paid_up_capital',
        'description',
        'logo',
    
        // Key Personnel
        'ceo_name',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'contact_person_designation',
    
        // Business Performance
        'total_projects_completed',
        'total_projects_ongoing',
        'total_gfa_completed',
        'rating',
    
        // Financial Information
        'bank_name',
        'bank_account_number',
        'tax_identification_number',
    
        // Specialization
        'specializations',
        'certifications',
    
        // Social Media & Marketing
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
    
        // System Fields
        'is_featured',
        'is_verified',
        'verified_at',
        'verified_by',
        'status',
        'remarks',
        'logo'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_id');
    }

    public function getLogoPathAttribute() {
        return $this->attributes['logo'] ? asset( 'storage/' . $this->attributes['logo'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getDeveloperTypeLabelAttribute()
    {
        $developerTypes = [
            '1' => __('developer.local'),
            '2' => __('developer.international'),
            '3' => __('developer.joint_venture'),
        ];

        return $developerTypes[$this->attributes['developer_type']] ?? null;
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'company_registration_number',
        'license_number',
        'developer_type',
    
        // Contact Information
        'email',
        'phone',
        'fax',
        'website',
    
        // Address
        'address',
        'city',
        'state',
        'postcode',
        'country',
    
        // Business Details
        'established_date',
        'paid_up_capital',
        'description',
        'logo',
    
        // Key Personnel
        'ceo_name',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'contact_person_designation',
    
        // Business Performance
        'total_projects_completed',
        'total_projects_ongoing',
        'total_gfa_completed',
        'rating',
    
        // Financial Information
        'bank_name',
        'bank_account_number',
        'tax_identification_number',
    
        // Specialization
        'specializations',
        'certifications',
    
        // Social Media & Marketing
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
    
        // System Fields
        'is_featured',
        'is_verified',
        'verified_at',
        'verified_by',
        'status',
        'remarks',
        'logo'
    ];

    protected static $logName = 'developers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} developers";
    }
}
