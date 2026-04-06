<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Project extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::saving(function ($project) {
            $titles = [];

            if (!empty($project->translations)) {
                $translations = $project->translations;

                // Decode only if it's not already an array
                if ( ! is_array( $translations ) ) {
                    $translations = json_decode( $translations, true ) ?? [];
                }

                if (is_array($translations)) {
                    foreach ($translations as $lang => $data) {
                        if (!empty($data['title'])) {
                            $titles[] = $data['title'];
                        }
                        if (!empty($data['short_description'])) {
                            $titles[] = $data['short_description'];
                        }
                    }
                }
            }

            $project->searchable_titles = implode(' ', array_unique($titles));
        });
    }

    protected $casts = [
        'project_locations' => 'array',
        'amenities' => 'array',
        'blocks' => 'array',
        'project_details' => 'array',
        'supported_languages' => 'array',
    ];

    protected $hidden = [
        'title',
        'description',
        'short_description',
    ];

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'logo',
        'translations',
        'pricing',
        'status',

        // new col
        'developer_id',
        'agent_id',
        'country_id',
        'project_status',
        'amenities',
        'property_type',
        'tenure',
        'bedrooms',
        'bedroom_text',
        'bathrooms',
        'bathroom_text',
        'building_type',
        'furnishing_status',
        'build_up_area_psf',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'completion_date',
        'sequence',
        'is_pet_friendly',

        // location
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'state',
        'area',
        'postcode',
        'longitude',
        'latitude',

        'min_bedrooms',
        'max_bedrooms',
        'min_bathrooms',
        'max_bathrooms',

        'blocks',
        'block_number',
        'total_floor',
        'total_unit',
        'min_carpark',
        'max_carpark',
        'carpark_text',
        'min_storeroom',
        'max_storeroom',
        'storeroom_text',
        'balcony',
        'project_details',
        'project_locations',
        'searchable_titles',
        'waze_url',
        'google_map_url',
        'supported_languages',
    ];

    public function getProjectTagsAttribute()
    {
        $completionYear = $this->attributes['completion_date']
            ? date( 'Y', strtotime( $this->attributes['completion_date'] ) )
            : null;
    
        $furnishingMap = [
            1 => 'fully_furnished',
            2 => 'partial_furnished',
            3 => 'no_furnishing',
        ];
    
        $buildingTypeMap = [
            1  => 'apartment_condominium',
            2  => 'flat',
            3  => 'service_residence',
            4  => 'terrace_link_house',
            5  => 'bungalow',
            6  => 'semi_detached',
            7  => 'townhouse',
            8  => 'shop',
            9  => 'office',
            10 => 'retail',
            11 => 'soho',
            12 => 'hotel',
            13 => 'restaurant',
            14 => 'factory',
            15 => 'warehouse',
            16 => 'land_only',
            17 => 'agriculture',
        ];        
    
        $languages = [ 'en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko' ];
        $result = [];
    
        foreach ( $languages as $lang ) {
            $tags = [];
    
            // if ( !empty( $this->attributes['tenure'] ) ) {
            //     $tags[] = __( 'property.' . $this->attributes['tenure'], [], $lang );
            // }
    
            if ( isset( $buildingTypeMap[$this->attributes['building_type']] ) ) {
                $tags[] = __( 'property.' . $buildingTypeMap[$this->attributes['building_type']], [], $lang );
            }
    
            if ( isset( $furnishingMap[$this->attributes['furnishing_status']] ) ) {
                $tags[] = __( 'property.' . $furnishingMap[$this->attributes['furnishing_status']], [], $lang );
            }
    
            if ( $completionYear ) {
                $tags[] = __( 'property.new_project', [ 'year' => $completionYear ], $lang );
            }
    
            $result[$lang] = $tags;
        }
    
        return $result;
    }
    
    
    public function floorplans()
    {
        return $this->hasMany(ProjectFloorplan::class)->where('status', 10)->orderBy('sequence');
    }

    public function developer()
    {
        return $this->belongsTo(Developer::class, 'developer_id');
    }

    public function galleries()
    {
        return $this->hasMany(ProjectGallery::class)->where('status', 1)->orderBy('sequence');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function getProjectLocationsDetailsAttribute()
    {
        return PropertyLocation::whereIn('id', json_decode($this->project_locations, true) ?? [])->get();
    }

    public function getAmenitiesDetailsAttribute()
    {
        $amenities = Amenity::whereIn(
            'id',
            json_decode( $this->amenities, true ) ?? []
        )->get();
    
        // Ensure each amenity includes the "icon_path" accessor
        return $amenities->each( function ( $amenity ) {
            $amenity->append( 'icon_path' ); // this calls getIconPathAttribute
            // $amenity->makeHidden( 'created_at', 'updated_at', 'status' );
        } );
    }

    public function getFurnishingLabelAttribute()
    {
        $furnishingType = [
            1  => __( 'property.fully_furnished' ),
            2  => __( 'property.partial_furnished' ),
            3  => __( 'property.no_furnishing' ),
        ];

        return $furnishingType[$this->attributes['furnishing_status']] ?? null;
    }
    
    public function getBuildingTypeLabelAttribute()
    {
        $buildingType = [
            1  => __( 'property.apartment_condominium' ),
            2  => __( 'property.flat' ),
            3  => __( 'property.service_residence' ),
            4  => __( 'property.terrace_link_house' ),
            5  => __( 'property.bungalow' ),
            6  => __( 'property.semi_detached' ),
            7  => __( 'property.townhouse' ),
            8  => __( 'property.shop' ),
            9  => __( 'property.office' ),
            10 => __( 'property.retail' ),
            11 => __( 'property.soho' ),
            12 => __( 'property.hotel' ),
            13 => __( 'property.restaurant' ),
            14 => __( 'property.factory' ),
            15 => __( 'property.warehouse' ),
            16 => __( 'property.land_only' ),
            17 => __( 'property.agriculture' ),
        ];        

        return $buildingType[$this->attributes['building_type']] ?? null;
    }

    public function getDecodedTranslationsAttribute(){
        return $this->attributes['translations'] ? json_decode( $this->attributes['translations'] ) : null;
    }

    public function getDecodedProjectDetailsAttribute(){
        return $this->attributes['project_details'] ? json_decode( $this->project_details, true ) : [];
    }

    public function getPresetTitleAttribute() {
        if ( ! empty( $this->attributes['translations'] ) ) {
            $translations = json_decode( $this->attributes['translations'], true ); // decode as array
    
            return $translations['en']['title'] ?? $this->attributes['title'];
        }
    
        return $this->attributes['title'];
    }

    public function getDecodedAmenitiesAttribute(){
        return $this->attributes['amenities'] ? json_decode( $this->attributes['amenities'] ) : null;
    }

    public function getLogoPathAttribute() {
        return $this->attributes['logo'] ? asset( 'storage/'.$this->attributes['logo'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function developers()
    {
        return $this->belongsToMany(Developer::class, 'developers_projects');
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get all prices in different currencies for this project
     */
    public function prices()
    {
        return $this->hasMany(\App\Models\ProjectPrice::class, 'project_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function getTotalActiveUnitsAttribute()
    {
        return $this->properties->flatMap(function ($property) {
            return $property->forSaleUnits;
        })->whereIn('status', [10,11])->count();
    }

    public function getTotalActiveUnitsLeftAttribute(){
        return $this->properties->flatMap(function ($property) {
            return $property->forSaleUnits;
        })->count();
    }

    public function getTenureTypeLabelAttribute()
    {
        $tenureType = [
            'freehold' => __('property.freehold'),
            'leasehold' => __('property.leasehold'),
        ];

        return $tenureType[$this->attributes['tenure']] ?? null;
    }

    public function getPropertyTypeLabelAttribute()
    {
        $propertyType = [
            1 => __('property.residential'),
            2 => __('property.commercial'),
            3 => __('property.industrial'),
        ];

        return $propertyType[$this->attributes['property_type']] ?? null;
    }

    public function getProjectStatusTypeLabelAttribute()
    {
        $projectStatusType = [
            1 => __('property.completed'),
            2 => __('property.under_construction'),
        ];

        return $projectStatusType[$this->attributes['project_status']] ?? null;
    }

    public function getProjectTypeLabelAttribute()
    {
        $propertyType = [
            1 => __('property.residential'),
            2 => __('property.commercial'),
            3 => __('property.industrial'),
        ];

        return $propertyType[$this->attributes['property_type']] ?? null;
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

    protected static $logAttributes = [
        'title',
        'short_description',
        'description',
        'logo',
        'translations',
        'pricing',
        'status',

        // new col
        'developer_id',
        'agent_id',
        'country_id',
        'project_status',
        'amenities',
        'property_type',
        'tenure',
        'bedrooms',
        'bedroom_text',
        'bathrooms',
        'bathroom_text',
        'building_type',
        'furnishing_status',
        'build_up_area_psf',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'completion_date',
        'sequence',
        'is_pet_friendly',

        // location
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'state',
        'area',
        'postcode',
        'longitude',
        'latitude',

        'min_bedrooms',
        'max_bedrooms',
        'min_bathrooms',
        'max_bathrooms',
        
        'blocks',
        'block_number',
        'total_floor',
        'total_unit',
        'project_details',
        'project_locations',
        'searchable_titles',
        'waze_url',
        'google_map_url',
        'supported_languages',
    ];

    protected static $logName = 'projects';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} project";
    }
}