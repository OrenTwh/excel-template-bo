<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class FeatureProject extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id',
        'property_id',
        'country_id',
        'country_code',
        'county',
        'sequence',
        'feature_type',
        'status',
        'advertisement_type',
        'supported_languages',
    ];

    protected $casts = [
        'supported_languages' => 'array',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            '1' => 'Inactive',
            '10' => 'Active',
        ];

        return $statuses[$this->attributes['status']] ?? 'Unknown';
    }

    public function getFeatureTypeLabelAttribute()
    {
        $featureTypes = [
            '1' => __('feature_project.featured_listings'),
            '2' => __('feature_project.trending_projects'),
            '3' => __('feature_project.mm2m_projects'),
        ];

        return $featureTypes[$this->attributes['feature_type']] ?? 'Unknown';
    }

    public static function getFeatureTypes()
    {
        return [
            '1' => __('feature_project.featured_listings'),
            '2' => __('feature_project.trending_projects'),
            '3' => __('feature_project.mm2m_projects'),
        ];
    }

    /**
     * Check if this feature project supports a specific language/country
     *
     * @param string $isoCode Country ISO code (MY, SG, TW, ID, CN, JP, KR)
     * @return bool
     */
    public function supportsLanguage($isoCode)
    {
        // If supported_languages is null or empty, supports all languages
        $languages = $this->supported_languages;

        if ($languages === null || empty($languages)) {
            return true;
        }

        return is_array($languages) && in_array($isoCode, $languages);
    }

    /**
     * Get all supported countries/languages
     * Returns null to indicate "all languages" when empty
     * The $casts already handles JSON decoding
     *
     * @param mixed $value
     * @return array|null
     */
    public function getSupportedLanguagesAttribute($value)
    {
        // $casts already converts JSON to array, so $value is already an array or null
        // Return null for empty arrays to indicate "supports all languages"
        if (empty($value) || (is_array($value) && count($value) === 0)) {
            return null;
        }

        return $value;
    }

    /**
     * Scope a query to only include feature projects that support a specific language
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $isoCode Country ISO code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLanguage($query, $isoCode)
    {
        if (empty($isoCode)) {
            return $query;
        }

        return $query->where(function($q) use ($isoCode) {
            $q->whereJsonContains('supported_languages', $isoCode)
              ->orWhereNull('supported_languages')
              ->orWhere('supported_languages', '[]')
              ->orWhere('supported_languages', '');
        });
    }

    public function getDecodedSupportedLanguagesAttribute() {
        return $this->attributes['supported_languages'] ? json_decode( $this->attributes['supported_languages'], true ) : []; 
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'project_id',
        'country_id',
        'country_code',
        'county',
        'sequence',
        'feature_type',
        'status',
        'property_id',
        'advertisement_type',
        'supported_languages',
    ];

    protected static $logName = 'feature_projects';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} feature_projects";
    }
}