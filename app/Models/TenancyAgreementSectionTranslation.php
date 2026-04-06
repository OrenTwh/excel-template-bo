<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class TenancyAgreementSectionTranslation extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 't_a_section_translations';

    protected $fillable = [
        't_a_section_id',
        'language_code',
        'title',
        'content',
    ];

    protected static $logAttributes = [
        't_a_section_id',
        'language_code',
        'title',
        'content',
    ];

    protected static $logName = 't_a_section_translations';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} tenancy agreement section translation";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
    public function tenancyAgreementSection()
    {
        return $this->belongsTo(TenancyAgreementSection::class, 't_a_section_id');
    }

    /**
     * Accessors
     */
    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    /**
     * Scopes
     */
    public function scopeForLanguage($query, $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }
}
