<?php

namespace App\Services;

use App\Models\{
    FavouriteProject,
    FavouriteProperty,
    Project,
    Property,
    Option
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Helper;

class FavouriteService
{
    public function getFavouriteProjects($request)
    {
        $user = Auth::user();

        $favourites = FavouriteProject::where('user_id', $user->id)
            ->where('status', 10)
            ->when($request, function ($query, $request) {
                if( $request->favourite_type ){
                    return $query->where('favourite_type', $request->favourite_type );
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($favourites->isEmpty()) {
            return [
                'data' => [],
                'pagination' => $this->emptyPagination(),
            ];
        }

        $projectIds = $favourites->pluck('project_id')->toArray();

        $projects = Project::with(['developers', 'country', 'floorplans'])
            ->where('status', 10)
            ->whereIn('id', $projectIds)
            ->get();

        $perPage = min($request->input('per_page', 10), 50);
        $page = $request->input('page', 1);

        $sorted = collect();
        foreach ($favourites as $fav) {
            $project = $projects->where('id', $fav->project_id)->first();
            if ($project) $sorted->push($project);
        }

        $total = $sorted->count();
        $offset = ($page - 1) * $perPage;
        $paginated = $sorted->slice($offset, $perPage);

        $data = $paginated->map(function ($p) use ($perPage) {
            return $this->transformProjectData($p, $perPage);
        });

        return [
            'data' => $data->values()->toArray(),
            'pagination' => $this->buildPagination($page, $perPage, $total, $offset),
        ];
    }

    public function getFavouriteProperties($request)
    {
        $user = Auth::user();

        $favourites = FavouriteProperty::where('user_id', $user->id)
            ->where('status', 10)
            ->when($request, function ($query, $request) {
                if( $request->favourite_type ){
                    return $query->where('favourite_type', $request->favourite_type );
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($favourites->isEmpty()) {
            return [
                'data' => [],
                'pagination' => $this->emptyPagination(),
            ];
        }

        $propertyIds = $favourites->pluck('property_id')->toArray();

        $properties = Property::with([
            'project', 'developer', 'agent', 'country',
            'galleries', 'floorplans', 'shortRentalUnits', 'longRentalUnits'
        ])
            ->where('status', 10)
            ->whereIn('id', $propertyIds)
            ->get();

        $perPage = min($request->input('per_page', 10), 50);
        $page = $request->input('page', 1);

        $sorted = collect();
        foreach ($favourites as $fav) {
            $property = $properties->where('id', $fav->property_id)->first();
            if ($property) $sorted->push($property);
        }

        $total = $sorted->count();
        $offset = ($page - 1) * $perPage;
        $paginated = $sorted->slice($offset, $perPage);

        $data = $paginated->map(function ($p) use ($perPage) {
            return $this->transformPropertyData($p, $perPage);
        });

        return [
            'data' => $data->values()->toArray(),
            'pagination' => $this->buildPagination($page, $perPage, $total, $offset),
        ];
    }

    public function addFavouriteProject($request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'favourite_type' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'status' => 422];
        }

        $existing = FavouriteProject::where('user_id', $user->id)
            ->where('project_id', $request->project_id)
            ->first();

        if ($existing) {
            if ($existing->status != 10) {
                $existing->update(['status' => 10]);
                return ['data' => $existing, 'message' => 'Project added back'];
            }
            return ['error' => 'Already favourited', 'status' => 409];
        }

        $favourite = FavouriteProject::create([
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'favourite_type' => $request->favourite_type ?? 1,
            'status' => 10,
        ]);

        return ['data' => $favourite, 'message' => 'Added successfully'];
    }

    public function addFavouriteProperty($request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'property_id' => 'required|integer|exists:properties,id',
            'favourite_type' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'status' => 422];
        }

        $existing = FavouriteProperty::where('user_id', $user->id)
            ->where('property_id', $request->property_id)
            ->first();

        if ($existing) {
            if ($existing->status != 10) {
                $existing->update(['status' => 10]);
                return ['data' => $existing, 'message' => 'Property added back'];
            }
            return ['error' => 'Already favourited', 'status' => 409];
        }

        $favourite = FavouriteProperty::create([
            'user_id' => $user->id,
            'property_id' => $request->property_id,
            'favourite_type' => $request->favourite_type ?? 1,
            'status' => 10,
        ]);

        return ['data' => $favourite, 'message' => 'Added successfully'];
    }

    public function removeFavouriteProject($project_id, $favourite_type)
    {
        $user = Auth::user();

        $favourite = FavouriteProject::where('user_id', $user->id)
            ->where('project_id', $project_id)
            ->where('status', 10)
            ->first();

        if (!$favourite) {
            return ['error' => 'Not found', 'status' => 404];
        }

        $favourite->update(['status' => 0]);
        return ['message' => 'Removed successfully'];
    }

    public function removeFavouriteProperty($property_id, $favourite_type)
    {
        $user = Auth::user();

        $favourite = FavouriteProperty::where('user_id', $user->id)
            ->where('property_id', $property_id)
            ->where('favourite_type', $favourite_type)
            ->where('status', 10)
            ->first();

        if (!$favourite) {
            return ['error' => 'Not found', 'status' => 404];
        }

        $favourite->update(['status' => 0]);
        return ['message' => 'Removed successfully'];
    }

    private function buildPagination($page, $perPage, $total, $offset)
    {
        return [
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total > 0 ? $offset + 1 : null,
            'to' => $total > 0 ? min($offset + $perPage, $total) : null,
        ];
    }

    private function emptyPagination()
    {
        return [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 10,
            'total' => 0,
            'from' => null,
            'to' => null,
        ];
    }

    /**
     * Transform project data to match searchProjectsApi response format
     */
    private function transformProjectData($project, $perPage)
    {
        $newTitle = $project->title;

        if ($project->translations) {
            $newTitle = $project->decoded_translations ? $project->decoded_translations->en->title : $project->title;
        }

        // Calculate min/max property prices for this project
        $propertyPrices = \DB::table('properties')
            ->where('project_id', $project->id)
            ->where('status', 10)
            ->whereNotNull('selling_price_unit')
            ->where('selling_price_unit', '>', 0)
            ->selectRaw('MIN(selling_price_unit) as min_price, MAX(selling_price_unit) as max_price')
            ->first();

        $minPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->min_price, 2, true ) : Helper::numberFormatV2(0, 2, true);
        $maxPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->max_price, 2, true ) : Helper::numberFormatV2(0, 2, true);
        $minRentalPrice = Helper::numberFormatV2(0, 2, true);
        $maxRentalPrice = Helper::numberFormatV2(0, 2, true);

        // Calculate min/max values from block JSON data of properties under this project
        $properties = \DB::table('properties')
            ->where('project_id', $project->id)
            ->where('status', 10)
            ->whereNotNull('block')
            ->get(['block']);

        $buildupValues = [];
        $pricePsfValues = [];
        $priceUnitValues = [];
        $maintenanceValues = [];

        foreach ($properties as $property) {
            $blockData = json_decode($property->block, true);
            if (!is_array($blockData)) continue;

            foreach ($blockData as $block) {
                if (!isset($block['floorplans']) || !is_array($block['floorplans'])) continue;

                foreach ($block['floorplans'] as $floorplan) {
                    if (isset($floorplan['build_up_area_psf']) && $floorplan['build_up_area_psf'] > 0) {
                        $buildupValues[] = $floorplan['build_up_area_psf'];
                    }
                    if (isset($floorplan['selling_price_psf']) && $floorplan['selling_price_psf'] > 0) {
                        $pricePsfValues[] = $floorplan['selling_price_psf'];
                    }
                    if (isset($floorplan['selling_price_unit']) && $floorplan['selling_price_unit'] > 0) {
                        $priceUnitValues[] = $floorplan['selling_price_unit'];
                    }
                    // Calculate maintenance fee from build_up_area_psf * maintenance_fee_psf from project
                    if (isset($floorplan['build_up_area_psf']) && $floorplan['build_up_area_psf'] > 0 && $project->maintenance_fee_psf > 0) {
                        $maintenanceValues[] = $floorplan['build_up_area_psf'] * $project->maintenance_fee_psf;
                    }
                }
            }
        }

        $propertyRanges = (object) [
            'min_buildup' => !empty($buildupValues) ? min($buildupValues) : null,
            'max_buildup' => !empty($buildupValues) ? max($buildupValues) : null,
            'min_price_psf' => !empty($pricePsfValues) ? min($pricePsfValues) : null,
            'max_price_psf' => !empty($pricePsfValues) ? max($pricePsfValues) : null,
            'min_price_unit' => !empty($priceUnitValues) ? min($priceUnitValues) : null,
            'max_price_unit' => !empty($priceUnitValues) ? max($priceUnitValues) : null,
            'min_maintenance' => !empty($maintenanceValues) ? min($maintenanceValues) : null,
            'max_maintenance' => !empty($maintenanceValues) ? max($maintenanceValues) : null,
        ];

        // Generate text ranges
        $buildupText = null;
        if ($propertyRanges && $propertyRanges->min_buildup && $propertyRanges->max_buildup) {
            if ($propertyRanges->min_buildup == $propertyRanges->max_buildup) {
                $buildupText = number_format($propertyRanges->min_buildup) . ' sqft';
            } else {
                $buildupText = 'From ' . number_format($propertyRanges->min_buildup) . ' sqft - ' . number_format($propertyRanges->max_buildup) . ' sqft';
            }
        }

        $pricePsfText = null;
        if ($propertyRanges && $propertyRanges->min_price_psf && $propertyRanges->max_price_psf) {
            if ($propertyRanges->min_price_psf == $propertyRanges->max_price_psf) {
                $pricePsfText = 'RM ' . number_format($propertyRanges->min_price_psf);
            } else {
                $pricePsfText = 'RM ' . number_format($propertyRanges->min_price_psf) . ' - RM ' . number_format($propertyRanges->max_price_psf);
            }
        }

        $priceUnitText = null;
        if ($propertyRanges && $propertyRanges->min_price_unit && $propertyRanges->max_price_unit) {
            if ($propertyRanges->min_price_unit == $propertyRanges->max_price_unit) {
                $priceUnitText = 'RM ' . number_format($propertyRanges->min_price_unit);
            } else {
                $priceUnitText = 'RM ' . number_format($propertyRanges->min_price_unit) . ' - RM ' . number_format($propertyRanges->max_price_unit);
            }
        }

        $maintenanceText = null;
        if ($propertyRanges && $propertyRanges->min_maintenance && $propertyRanges->max_maintenance) {
            if ($propertyRanges->min_maintenance == $propertyRanges->max_maintenance) {
                $maintenanceText = 'RM ' . number_format($propertyRanges->min_maintenance);
            } else {
                $maintenanceText = 'RM ' . number_format($propertyRanges->min_maintenance) . ' - RM ' . number_format($propertyRanges->max_maintenance);
            }
        }

        $contactPhone = \App\Models\Option::where('option_name', 'OFFICIAL_PHONE_NUMBER')->value('option_value');
        $contactWhatsapp = \App\Models\Option::where('option_name', 'WHATSAPP')->value('option_value');

        // Base data that's always included
        $baseData = [
            'id' => $project->id,
            'is_favourite' => true, // Since this is from favorites, it's always true
            'project_tags' => $project->project_tags,
            // Additional required fields
            'galleries' => $project->galleries()
                ->orderBy('sequence')
                ->get()
                ->map(function ($gallery) {
                    return [
                        'id' => $gallery->id,
                        'sequence' => $gallery->sequence,
                        'image_path' => $gallery->image ? asset('storage/' . $gallery->image) : null,
                        'remarks' => $gallery->remarks,
                    ];
                }),

            'logo' => $project->logo_path,
            'logo_path' => $project->logo_path,
            'min_property_price' => $minPropertyPrice,
            'max_property_price' => $maxPropertyPrice,
            'min_rental_price' => $minRentalPrice,
            'max_rental_price' => $maxRentalPrice,

            // Property unit counts from all properties in this project
            'available_units' => \DB::table('property_units')
                ->join('properties', 'property_units.property_id', '=', 'properties.id')
                ->where('properties.project_id', $project->id)
                ->where('property_units.unit_status', 'available')
                ->where('property_units.status', 10)
                ->count(),

            'for_sale_count' => \DB::table('property_units')
                ->join('properties', 'property_units.property_id', '=', 'properties.id')
                ->where('properties.project_id', $project->id)
                ->where('property_units.unit_type', 'for_sale')
                ->where('property_units.status', 10)
                ->count(),

            'for_rent_count' => \DB::table('property_units')
                ->join('properties', 'property_units.property_id', '=', 'properties.id')
                ->where('properties.project_id', $project->id)
                ->whereIn('property_units.unit_type', ['long_rental', 'short_rental'])
                ->where('property_units.status', 10)
                ->count(),

            'phone_number' => $contactPhone,
            'whatsapp_link' => Helper::whatsAppBaseLink($contactWhatsapp), 
            'translations' => $project->decoded_translations,
            'project_details' => $project->decoded_project_details,
            'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
            'project_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
            'tenure' => $project->tenure,
            'build_up_area_psf' => $project->build_up_area_psf,
            'selling_price_psf' => $project->selling_price_psf,
            'selling_price_unit' => $project->selling_price_unit,
            'maintenance_fee_psf' => $project->maintenance_fee_psf,
            'build_up_area_psf_text' => $buildupText,
            'selling_price_psf_text' => $pricePsfText,
            'selling_price_unit_text' => $priceUnitText,
            'maintenance_fee_psf_text' => $maintenanceText,
            'completion_date' => $project->completion_date,
            'project_status' => $project->project_status,
            'project_status_label' => ProjectService::getProjectStatusLabel($project->project_status),
            'is_pet_friendly' => $project->is_pet_friendly == 1 ?? false,
            'address_line_1' => $project->address_line_1,
            'address_line_2' => $project->address_line_2,
            'address_line_3' => $project->address_line_3,
            'state' => $project->state,
            'postcode' => $project->postcode,
            'latitude' => $project->latitude,
            'longitude' => $project->longitude,
            'location' => [
                'address_line_1' => $project->address_line_1,
                'address_line_2' => $project->address_line_2,
                'address_line_3' => $project->address_line_3,
                'state' => $project->state,
                'postcode' => $project->postcode,
                'latitude' => $project->latitude,
                'longitude' => $project->longitude,
                'locations' => $project->project_locations_details,
            ],
            'amenities_list' => $project->amenities_details,
            'developers' => $project->developers->map(function ($developer) {
                return $developer->name;
            }),
            'country' => $project->country ? $project->country->country_name : null,
            'floorplans' => $project->floorplans->map(function ($floorplan) {
                return [
                    'id' => $floorplan->id,
                    'property_id' => $floorplan->property ? $floorplan->property->id : 0,
                    'sequence' => $floorplan->sequence,
                    'image' => $floorplan->image_path,
                    'image_path' => $floorplan->image_path,
                    'remarks' => $floorplan->remarks,
                    'bedrooms' => $floorplan->bedrooms,
                    'bathrooms' => $floorplan->bathrooms,
                    'balcony' => $floorplan->balcony,
                    'storeroom' => $floorplan->storeroom,
                    'parking_spaces' => $floorplan->parking_spaces,
                    'category' => $floorplan->category,
                    'document_link' => $floorplan->upload_link,
                    'is_pet_friendly' => $floorplan->project->is_pet_friendly == 1 ? 'Yes' : 'No',
                    'completion_date' => $floorplan->project->completion_date,
                    'units_left' => $floorplan->property ? $floorplan->property->units->count() : 0,

                    // pricing etc
                    'build_up_area_psf' => $floorplan->property ? $floorplan->property->build_up_area_psf : Helper::numberFormatV2(0, 2, true),
                    'selling_price_psf' => 'RM' . ($floorplan->property ? $floorplan->property->selling_price_psf : Helper::numberFormatV2(0, 2, true)),
                    'selling_price_unit' => 'RM' . ($floorplan->property ? $floorplan->property->selling_price_unit : Helper::numberFormatV2(0, 2, true)),
                    'maintenance_fee_psf' => 'RM' . ($floorplan->property ? $floorplan->property->maintenance_fee_psf : Helper::numberFormatV2(0, 2, true)),
                ];
            }),
            'floorplans_count' => $project->floorplans->count(),
        ];

        // If per_page is 1, return detailed data like getProject
        if ($perPage == 1) {
            return array_merge($baseData, [
                'description' => $project->description,
                'bedroom_text' => $project->bedroom_text,
                'bathroom_text' => $project->bathroom_text,
                'building_type' => $project->building_type,
                'furnishing_status' => $project->furnishing_status,
                'furnishing_status_label' => ProjectService::getFurnishingStatusLabel($project->furnishing_status),
                'sequence' => $project->sequence,
                'amenities' => $project->amenities,
                'developers' => $project->developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                    ];
                }),
                'country' => $project->country ? [
                    'id' => $project->country->id,
                    'name' => $project->country->country_name,
                ] : null,
            ]);
        }

        // Otherwise return just the base data
        return array_merge($baseData, [
            'title' => $newTitle,
        ]);
    }

    /**
     * Transform property data to match searchPropertiesApi response format
     */
    private function transformPropertyData($property, $perPage)
    {
        $newPropertyName = $property->property_name;

        if ($property->translations) {
            $newPropertyName = $property->decoded_translations ? $property->decoded_translations->en->property_name : $property->property_name;
        }

        // Check if property is purchasable (has available units)
        $currentDate = \Carbon\Carbon::now()->format('Y-m-d');

        // Get units that are not booked (excluding units with active future bookings)
        $bookedUnitIds = \App\Models\Booking::where('property_id', $property->id)
            ->where('status', 10) // Active bookings
            ->whereDate('booking_date', '>=', $currentDate) // Today or future
            ->whereNotNull('property_unit_id')
            ->pluck('property_unit_id')
            ->unique()
            ->toArray();

        // Check if there are available units (status 10, unit_status available, not booked)
        $availableUnitsQuery = \App\Models\PropertyUnit::where('property_id', $property->id)
            ->where('status', 10)
            ->where('unit_status', 'available');

        if (!empty($bookedUnitIds)) {
            $availableUnitsQuery->whereNotIn('id', $bookedUnitIds);
        }

        $isPurchasable = $availableUnitsQuery->exists();
        $contactPhone = Option::where( 'option_name', 'OFFICIAL_PHONE_NUMBER' )->value('option_value');
        $contactWhatsapp = Option::where( 'option_name', 'WHATSAPP' )->value('option_value');

        // Check if property has long rental and short rental units
        $isLongRent = $property->longRentalUnits && $property->longRentalUnits->isNotEmpty();
        $isShortRent = $property->shortRentalUnits && $property->shortRentalUnits->isNotEmpty();

        // Base data that's always included
        $baseData = [
            'id' => $property->id,
            'is_favourite' => true,
            'is_purchasable' => $isPurchasable,
            'is_long_rent' => $isLongRent,
            'is_short_rent' => $isShortRent,
            'property_tags' => $property->property_tags,
            // Additional required fields
            'galleries' => $property->galleries()
                ->orderBy('sequence')
                ->get()
                ->map(function ( $gallery ) {
                    return [
                        'id'       => $gallery->id,
                        'sequence' => $gallery->sequence,
                        'image_path'    => $gallery->image ? asset( 'storage/' . $gallery->image ) : null,
                        'remarks'  => $gallery->remarks,
                    ];
                }),

            'image' => $property->projectfloorplan->image_path,
            'image_path' => $property->projectfloorplan->image_path,
            'remarks' => $property->projectfloorplan->remarks,
            'bedrooms' => (int) $property->bedroom_text,
            'bathrooms' => (int) $property->bathroom_text,
            'balcony' => $property->balcony,
            'storeroom' => $property->storeroom,
            'parking_spaces' => $property->parking_spaces,
            'category' => $property->category,
            'document_link' => $property->upload_link,
            'is_pet_friendly' => $property->project->is_pet_friendly == 1 ? 'Yes' : 'No',
            'completion_date' => $property->project->completion_date,
            'units_left' => $property->projectfloorplan->property ? $property->projectfloorplan->property->units->count() : 0,

            // pricing etc
            'build_up_area_psf' => $property->projectfloorplan->property ? Helper::numberFormatV2($property->projectfloorplan->property->build_up_area_psf,2,true) : Helper::numberFormatV2( 0, 2, true ),
            'selling_price_psf' => 'RM' . ( $property->projectfloorplan->property ? Helper::numberFormatV2($property->projectfloorplan->property->selling_price_psf,2,true) : Helper::numberFormatV2( 0, 2, true ) ),
            'selling_price_unit' => 'RM' . ( $property->projectfloorplan->property ? Helper::numberFormatV2($property->projectfloorplan->property->selling_price_unit,2,true) : Helper::numberFormatV2( 0, 2, true ) ),
            'maintenance_fee_psf' => 'RM' . ( $property->projectfloorplan->property ? Helper::numberFormatV2($property->projectfloorplan->property->maintenance_fee_psf,2,true) : Helper::numberFormatV2( 0, 2, true ) ),
            
            'translations' => $property->decoded_translations,
            'project_translations' => $property->project->decoded_translations,
            'property_details' => $property->decoded_property_details,
            'project_details' => $property->project->decoded_project_details,
            'amenities_list' => $property->project->amenities_details,

            'phone_number' => $contactPhone,
            'whatsapp_link' => Helper::whatsAppBaseLink($contactWhatsapp), 

            // Property units
            'available_units' => $property->units()
                ->where('status', 10)
                ->orderBy('floor', 'asc')
                ->orderBy('unit_number', 'asc')
                ->get()
                ->map(function ($unit) use ($property) {
                    return [
                        'id' => $unit->id,
                        'property_id' => $unit->property_id,
                        'property_name' => $property->property_name ?? null,
                        'unit_number' => $unit->unit_number,
                        'unit_name' => $unit->unit_name,
                        'floor' => $unit->floor,
                        'bedrooms' => $unit->bedrooms,
                        'bathrooms' => $unit->bathrooms,
                        'balcony' => $unit->balcony,
                        'storeroom' => $unit->storeroom,
                        'parking_spaces' => $unit->parking_spaces,
                        'built_up_area' => $unit->built_up_area,
                        'selling_price' => Helper::numberFormatV2($unit->selling_price,2,true),
                        'rental_price' => Helper::numberFormatV2($unit->rental_price,2,true),
                        'maintenance_fee' => Helper::numberFormatV2($unit->maintenance_fee,2,true),
                        'unit_status' => $unit->unit_status,
                        'unit_status_label' => $unit->unit_status_label,
                        'unit_type' => $unit->unit_type,
                        'unit_type_label' => $unit->unit_type_label,
                        'availability_date' => $unit->availability_date,
                        'remarks' => $unit->remarks,
                        'created_at' => $unit->created_at,
                        'updated_at' => $unit->updated_at,
                    ];
            }),

            // 'property_name' => $propertyName,
            // 'short_description' => $property->short_description,
            // 'description' => $property->description,
            // 'thumbnail' => $property->thumbnail_path,
            // 'property_preview' => $property->property_preview_path,
            // 'property_type' => $property->property_type,
            // 'property_type_label' => $property->property_type_label,
            // 'property_status' => $property->property_status,
            // 'property_status_label' => $property->property_status_type_label,
            // 'tenure' => $property->tenure,
            // 'tenure_label' => $property->tenure_type_label,
            // 'building_type' => $property->building_type,
            // 'building_type_label' => $property->building_type_label,
            // 'furnishing_status' => $property->furnishing_status,
            // 'furnishing_status_label' => $property->furnishing_status_type_label,
            // 'bedrooms' => $property->bedrooms,
            // 'bedroom_text' => $property->bedroom_text,
            // 'bathrooms' => $property->bathrooms,
            // 'bathroom_text' => $property->bathroom_text,
            // 'balcony' => $property->balcony,
            // 'storeroom' => $property->storeroom,
            // 'parking_spaces' => $property->parking_spaces,
            // 'lift' => $property->lift,
            // 'furnishing' => $property->furnishing,
            // 'furnishing_label' => $property->furnishing_label,
            // 'build_up_area_psf' => $property->build_up_area_psf,
            // 'selling_price_psf' => $property->selling_price_psf,
            // 'selling_price_unit' => $property->selling_price_unit,
            // 'maintenance_fee_psf' => $property->maintenance_fee_psf,
            // 'completion_date' => $property->completion_date,
            // 'is_pet_friendly' => $property->is_pet_friendly,
            // 'sequence' => $property->sequence,
            // // 'amenities_list' => $property->amenities_details,
            // 'pricing' => $property->pricing,
            // 'unit_left' => $property->unit_left,
            // 'floorplan_type' => $property->floorplan_type,
            // 'block' => $property->block,
            // 'location' => [
            //     'address_line_1' => $property->address_line_1,
            //     'address_line_2' => $property->address_line_2,
            //     'address_line_3' => $property->address_line_3,
            //     'state' => $property->state,
            //     'postcode' => $property->postcode,
            //     'latitude' => $property->latitude,
            //     'longitude' => $property->longitude,
            //     // 'locations' => $property->property_location_details,
            // ],
            // 'project' => $property->project ? [
            //     'id' => $property->project->id,
            //     'title' => $property->project->title,
            //     'translations' => $property->project->decoded_translations,
            // ] : null,
            // 'developer' => $property->developer ? [
            //     'id' => $property->developer->id,
            //     'name' => $property->developer->name,
            // ] : null,
            // 'agent' => $property->agent ? [
            //     'id' => $property->agent->id,
            //     'name' => $property->agent->name,
            // ] : null,
            // 'country' => $property->country ? [
            //     'id' => $property->country->id,
            //     'name' => $property->country->country_name,
            // ] : null,
            // 'galleries' => $property->galleries->map(function ($gallery) {
            //     return [
            //         'id' => $gallery->id,
            //         'sequence' => $gallery->sequence,
            //         'image' => $gallery->image_path,
            //         'remarks' => $gallery->remarks,
            //     ];
            // }),
            // 'floorplans' => $property->floorplans->map(function ($floorplan) {
            //     return [
            //         'id' => $floorplan->id,
            //         'sequence' => $floorplan->sequence,
            //         'image' => $floorplan->floorplan_path,
            //         'remarks' => $floorplan->remarks,
            //     ];
            // }),

            // // Additional required fields
            // 'available_units' => $property->units->where('unit_status', 'available')->count(),
            // 'for_sale_count' => $property->units->where('unit_type', 'for_sale')->count(),
            // 'for_rent_count' => $property->units->whereIn('unit_type', ['long_rental', 'short_rental'])->count(),
            // 'phone_number' => $property->agent ? $property->agent->phone_number : ($property->developer ? $property->developer->phone : null),

            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
        ];

        // If per_page is 1, return detailed data like getProperty
        if ($perPage == 1) {
            return array_merge($baseData, [
                'description' => $property->description,
                'balcony' => $property->balcony,
                'storeroom' => $property->storeroom,
                'parking_spaces' => $property->parking_spaces,
                'lift' => $property->lift,
                'furnishing' => $property->furnishing,
                'furnishing_label' => $property->furnishing_label,
                'sequence' => $property->sequence,
                'amenities_list' => $property->amenities_details,
                'pricing' => $property->pricing,
                'galleries' => $property->galleries->map(function ($gallery) {
                    return [
                        'id' => $gallery->id,
                        'sequence' => $gallery->sequence,
                        'image' => $gallery->image_path,
                        'remarks' => $gallery->remarks,
                    ];
                }),
                'floorplans' => $property->floorplans->map(function ($floorplan) {
                    return [
                        'id' => $floorplan->id,
                        'sequence' => $floorplan->sequence,
                        'image' => $floorplan->floorplan_path,
                        'remarks' => $floorplan->remarks,
                    ];
                }),

                // Additional required fields
                'available_units' => $property->units->where('unit_status', 'available')->count(),
                'for_sale_count' => $property->units->where('unit_type', 'for_sale')->count(),
                'for_rent_count' => $property->units->whereIn('unit_type', ['long_rental', 'short_rental'])->count(),
                'phone_number' => $property->agent ? $property->agent->phone_number : ($property->developer ? $property->developer->phone : null),

                'created_at' => $property->created_at,
                'updated_at' => $property->updated_at,
            ]);
        }

        return $baseData;
    }
}
