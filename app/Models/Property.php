<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Property extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::saving(function ($property) {
            // Guard against null property instance (edge case during mass ops)
            if (! $property instanceof self) {
                return;
            }
    
            $titles = [];
    
            // Handle translations safely
            if (! empty($property->translations)) {
                $translations = $property->translations;

                // Decode only if it's not already an array
                if ( ! is_array( $translations ) ) {
                    $translations = json_decode( $translations, true ) ?? [];
                }
    
                if (is_array($translations)) {
                    foreach ($translations as $lang => $data) {
                        if (is_array($data)) {
                            if (! empty($data['property_name'])) {
                                $titles[] = $data['property_name'];
                            }
                            if (! empty($data['short_description'])) {
                                $titles[] = $data['short_description'];
                            }
                        }
                    }
                }
            }
    
            // Always ensure $titles is valid before assignment
            $property->searchable_titles = count($titles)
                ? implode(' ', array_unique($titles))
                : null;
        });
    }
    

    protected $casts = [
        'property_locations' => 'array',
        'amenities' => 'array',
        'block' => 'array',
        'property_details' => 'array',
        'supported_languages' => 'array',
        'build_up_area_psf' => 'decimal:2',
        'selling_price_psf' => 'decimal:2',
        'selling_price_unit' => 'decimal:2',
        'maintenance_fee_psf' => 'decimal:2',
        'min_build_up_area_psf' => 'decimal:2',
        'min_selling_price_psf' => 'decimal:2',
        'min_selling_price_unit' => 'decimal:2',
        'min_maintenance_fee_psf' => 'decimal:2',
    ];

    protected $fillable = [
        'property_name',
        'property_status',
        'property_type',
        'project_floorplan_id',
        'tenure',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'state',
        'area',
        'postcode',
        'longitude',
        'latitude',
        'build_up_area_psf',
        'furnishing_status',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'completion_date',
        'status',
        'thumbnail',
        'property_preview',
        'short_description',
        'description',
        'property_locations',
        'developer_id',

        'bedrooms',
        'bathrooms',
        'bedroom_text',
        'bathroom_text',
        'balcony',
        'storeroom',
        'parking_spaces',
        'lift',
        'furnishing',
        'building_type',
        'country_id',
        'sequence',
        'is_pet_friendly',
        'project_id',
        'block',
        'amenities',
        'agent_id',
        'translations',
        'pricing',
        'unit_left',
        'floorplan_type',
        'is_dual_key_unit',
        'block_name',
        'property_details',
        'rating',
        'short_rental_rating',
        'long_rental_rating',
        'accommodation_type',
        'number_of_guests',
        'searchable_titles',
        'waze_url',
        'google_map_url',
        'supported_languages',

        'min_build_up_area_psf',
        'min_selling_price_psf',
        'min_selling_price_unit',
        'min_maintenance_fee_psf',
        'flooring_scheme',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function developer()
    {
        return $this->belongsTo(Developer::class, 'developer_id');
    }

    public function projectFloorplan()
    {
        return $this->belongsTo(ProjectFloorplan::class, 'project_floorplan_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function getPropertyLocationDetailsAttribute()
    {
        return PropertyLocation::whereIn('id', json_decode($this->property_locations, true) ?? [])->get();
    }

    public function getAmenitiesDetailsAttribute()
    {
        return Amenity::whereIn('id', json_decode($this->amenities, true) ?? [])->get();
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getPropertyPreviewPathAttribute() {
        return $this->attributes['property_preview'] ? asset( 'storage/'.$this->attributes['property_preview'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getDecodedTranslationsAttribute(){
        return $this->attributes['translations'] ? json_decode( $this->attributes['translations'] ) : null;
    }
    
    public function getDecodedPropertyDetailsAttribute(){
        return $this->attributes['property_details'] ? json_decode( $this->property_details ) : null;
    }

    public function galleries()
    {
        return $this->hasMany(PropertyGallery::class)->where('status', 10)->orderBy('sequence');
    }

    public function floorplans()
    {
        return $this->hasMany(PropertyFloorplan::class)->where('status', 10)->orderBy('sequence');
    }

    public function appointmentUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 10 )
            ->where( function ( $query ) {
                $query->whereNull( 'unit_type' )
                      ->orWhere( 'unit_type', 4 );
            })
            ->orderBy( 'id', 'desc' );
    }

    public function units()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 10 )
            ->where( function ( $query ) {
                $query->whereNull( 'unit_type' )
                      ->orWhere( 'unit_type', 4 );
            })
            ->orderBy( 'id', 'desc' );
    }

    public function forSaleUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 10 )
            ->where( 'unit_type', 1 )
            ->orderBy( 'id', 'desc' );
    }

    public function SoldUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 11 )
            ->where( 'unit_type', 1 )
            ->orderBy( 'id', 'desc' );
    }

    // Long rental units (unit_type = 2)
    public function longRentalUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 10 )
            ->where( 'unit_type', 2 )
            ->orderBy( 'id', 'desc' );
    }

    // Short rental units (unit_type = 3)
    public function shortRentalUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 10 )
            ->where( 'unit_type', 3 )
            ->orderBy( 'id', 'desc' );
    }

    public function longRentednits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 13 )
            ->where( 'unit_type', 2 )
            ->orderBy( 'id', 'desc' );
    }

    // Short rental units (unit_type = 3)
    public function shortRentedUnits()
    {
        return $this->hasMany( PropertyUnit::class )
            ->where( 'status', 13 )
            ->where( 'unit_type', 3 )
            ->orderBy( 'id', 'desc' );
    }

    /**
     * Get all prices in different currencies for this property
     */
    public function prices()
    {
        return $this->hasMany(\App\Models\PropertyPrice::class, 'property_id');
    }

    public function rentalReviews()
    {
        return $this->hasMany(RentalReview::class);
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getPropertyTagsAttribute( $type = null )
    {
        if ( $this->project ) {
            $completionYear = $this->project->completion_date
                ? date( 'Y', strtotime( $this->project->completion_date ) )
                : null;

            // $tenure       = $this->project->tenure ?? null;
            $buildingType = $this->project->building_type ?? null;
            $furnishing   = $this->project->furnishing_status ?? null;
        } else {
            $completionYear = $this->completion_date
                ? date( 'Y', strtotime( $this->completion_date ) )
                : null;

            // $tenure       = $this->tenure ?? null;
            $buildingType = $this->building_type ?? null;
            $furnishing   = $this->furnishing_status ?? null;
        }

        $accommodationType = $this->accommodation_type ?? null;
    
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
    
        $furnishingMap = [
            1 => 'fully_furnished',
            2 => 'partial_furnished',
            3 => 'no_furnishing',
        ];

        $accommodationTypeMap = [
            1 => 'homestay',
            2 => 'hotel',
            3 => 'guesthouse',
            4 => 'hostel',
            5 => 'resort',
            6 => 'serviced_apartment',
            7 => 'villa',
            8 => 'lodge',
            9 => 'motel',
            10 => 'inn',
        ];

        $languages = [ 'en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko' ];
        $result = [];

        foreach ( $languages as $lang ) {
            $tags = [];

            $tags[] = $type == 2 ? __( 'property.long_rent' ) : __( 'property.short_rent' );

            if ( $accommodationType && isset( $accommodationTypeMap[$accommodationType] ) ) {
                $tags[] = __( 'property.' . $accommodationTypeMap[$accommodationType], [], $lang );
            }
    
            // if ( $tenure ) {
            //     $tags[] = __( 'property.' . $tenure, [], $lang );
            // }
    
            // if ( $buildingType && isset( $buildingTypeMap[$buildingType] ) ) {
            //     $tags[] = __( 'property.' . $buildingTypeMap[$buildingType], [], $lang );
            // }
    
            // if ( $furnishing && isset( $furnishingMap[$furnishing] ) ) {
            //     $tags[] = __( 'property.' . $furnishingMap[$furnishing], [], $lang );
            // }
    
            // if ( $completionYear ) {
            //     $tags[] = __( 'property.new_project', [ 'year' => $completionYear ], $lang );
            // }
    
            $result[$lang] = $tags;
        }
    
        return $result;
    }

    public function getTenureTypeLabelAttribute()
    {
        $tenureType = [
            'freehold' => __('property.freehold'),
            'leasehold' => __('property.leasehold'),
        ];

        return $tenureType[$this->attributes['tenure']] ?? null;
    }

    public function getFurnishingLabelAttribute()
    {
        $furnishingType = [
            1  => __( 'property.fully_furnished' ),
            2  => __( 'property.partial_furnished' ),
            3  => __( 'property.no_furnishing' ),
        ];

        return $furnishingType[$this->attributes['furnishing']] ?? null;
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

    public function getPropertyStatusTypeLabelAttribute()
    {
        $propertyStatusType = [
            1 => __('property.completed'),
            2 => __('property.under_construction'),
        ];

        return $propertyStatusType[$this->attributes['property_status']] ?? null;
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

    public function getAccomodationTypeLabelAttribute()
    {
        $accomodationType = [
            1 => __('property.homestay'),
            2 => __('property.hotel'),
            3 => __('property.guesthouse'),
            4 => __('property.hostel'),
            5 => __('property.resort'),
            6 => __('property.serviced_apartment'),
            7 => __('property.villa'),
            8 => __('property.lodge'),
            9 => __('property.motel'),
            10 => __('property.inn'),
        ];

        return $accomodationType[$this->attributes['accommodation_type']] ?? null;
    }

    public function getFurnishingStatusTypeLabelAttribute()
    {
        $furnishingStatus = [
            1 => __('property.fully_furnish'),
            2 => __('property.partial_furnish'),
            3 => __('property.bared_unit'),
        ];

        return $furnishingStatus[$this->attributes['furnishing_status']] ?? null;
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            10 => __('datatables.activated'),
            20 => __('datatables.suspended'),
            21 => __('datatables.soft_deleted'),
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
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
        'property_name',
        'property_status',
        'property_type',
        'project_floorplan_id',
        'tenure',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'state',
        'area',
        'postcode',
        'longitude',
        'latitude',
        'build_up_area_psf',
        'furnishing_status',
        'selling_price_psf',
        'selling_price_unit',
        'maintenance_fee_psf',
        'completion_date',
        'status',
        'thumbnail',
        'property_preview',
        'short_description',
        'description',
        'property_locations',
        'developer_id',

        'bedrooms',
        'bathrooms',
        'bedroom_text',
        'bathroom_text',
        'balcony',
        'storeroom',
        'parking_spaces',
        'lift',
        'furnishing',
        'building_type',
        'country_id',
        'sequence',
        'is_pet_friendly',
        'project_id',
        'block',
        'amenities',
        'agent_id',
        'translations',
        'pricing',
        'unit_left',
        'floorplan_type',
        'is_dual_key_unit',
        'block_name',
        'property_details',
        'rating',
        'short_rental_rating',
        'long_rental_rating',
        'number_of_guests',
        'accommodation_type',
        'searchable_titles',
        'waze_url',
        'google_map_url',
        'supported_languages',

        'min_build_up_area_psf',
        'min_selling_price_psf',
        'min_selling_price_unit',
        'min_maintenance_fee_psf',
        'flooring_scheme',
    ];

    protected static $logName = 'properties';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} property";
    }
}
