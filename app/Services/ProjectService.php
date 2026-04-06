<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Project,
    ProjectFloorplan,
    FileManager,
    Option,
    ProjectGallery,
    FavouriteProject,
};

use Carbon\Carbon;

class ProjectService
{

    public static function createProject( $request ) {

        $rules = [
            'translations' => [ 'required' ],
            'translations' => [
                    'required',
                    function ( $attribute, $value, $fail ) use ( $request ) {
                        $decoded = json_decode( $value );

                        if ( $decoded->en->title == null ) {
                            $fail( __( 'validation.required', [
                                'attribute' => __( 'project.title' ),
                            ] ) );
                        }
                    },
            ],
            'agent_id' => [ 'nullable', 'exists:agents,id' ],
            'country_id' => [ 'nullable', 'exists:countries,id' ],
            'project_status' => [ 'nullable', 'integer', 'in:1,2' ],
            'amenities' => [ 'nullable' ],
            'property_type' => [ 'nullable', 'integer', 'in:1,2,3' ],
            'tenure' => [ 'nullable', 'string', 'max:255' ],
            'min_bedrooms' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'max_bedrooms' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'bedroom_text' => [ 'nullable', 'string', 'max:255' ],
            'min_bathrooms' => [ 'nullable', 'numeric', 'min:1', 'max:10' ],
            'max_bathrooms' => [ 'nullable', 'numeric', 'min:1', 'max:10' ],
            'bathroom_text' => [ 'nullable', 'string', 'max:255' ],
            'min_carpark' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'max_carpark' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'carpark_text' => [ 'nullable', 'string', 'max:255' ],
            'min_storeroom' => [ 'nullable', 'numeric', 'min:0', 'max:5' ],
            'max_storeroom' => [ 'nullable', 'numeric', 'min:0', 'max:5' ],
            'storeroom_text' => [ 'nullable', 'string', 'max:255' ],
            'balcony' => [ 'nullable', 'boolean' ],
            'building_type' => [ 'nullable' ],
            'furnishing_status' => [ 'nullable', 'integer', 'in:1,2,3' ],
            'block_number' => [ 'nullable', 'integer', 'min:0' ],
            'total_floor' => [ 'nullable', 'integer', 'min:0' ],
            'blocks' => [ 'nullable', 'array' ],
            'blocks.*.name' => [ 'nullable', 'string', 'max:255' ],
            'blocks.*.total_units' => [ 'nullable', 'integer', 'min:0' ],
            'blocks.*.block_prefix' => [ 'required', 'string', 'max:10' ],
            'blocks.*.total_floors' => [ 'nullable', 'integer', 'min:1', 'max:999' ],
            'blocks.*.floor_prefix' => [ 'nullable', 'string', 'max:10' ],
            'build_up_area_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'selling_price_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'selling_price_unit' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'maintenance_fee_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'completion_date' => [ 'nullable', 'date_format:d/m/Y' ],
            'sequence' => [ 'nullable', 'integer', 'min:0' ],
            'is_pet_friendly' => [ 'nullable', 'boolean' ],
            'waze_url' => [ 'nullable' ],
            'google_map_url' => [ 'nullable' ],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'project_details' => ['nullable', 'json'],

            // File rules for floorplans
            'new_floorplans.*' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,avif,webp|max:5120',
            'new_floorplans_remarks.*' => 'required|string|max:255',
        ];

        $validator = Validator::make( $request->all(), $rules );

        $attributeName = [
            'translations' => __('template.content_translations'),
            'translations.en.title' => __('project.title'),
            'developer_id' => __('template.developers'),
            'agent_id' => __('template.agent'),
            'country_id' => __('template.country'),
            'project_status' => __('project.project_status'),
            'amenities' => __('project.amenities'),
            'property_type' => __('project.property_type'),
            'tenure' => __('project.tenure'),
            'min_bedrooms' => __('project.min_bedrooms'),
            'max_bedrooms' => __('project.max_bedrooms'),
            'bedroom_text' => __('project.bedroom_text'),
            'min_bathrooms' => __('project.min_bathrooms'),
            'max_bathrooms' => __('project.max_bathrooms'),
            'bathroom_text' => __('project.bathroom_text'),
            'min_carpark' => __('project.min_carpark'),
            'max_carpark' => __('project.max_carpark'),
            'carpark_text' => __('project.carpark_text'),
            'min_storeroom' => __('project.min_storeroom'),
            'max_storeroom' => __('project.max_storeroom'),
            'storeroom_text' => __('project.storeroom_text'),
            'balcony' => __('project.balcony'),
            'building_type' => __('project.building_type'),
            'furnishing_status' => __('project.furnishing_status'),
            'block_number' => __('project.block_number'),
            'total_floor' => __('project.total_floor'),
            'build_up_area_psf' => __('project.build_up_area_psf'),
            'selling_price_psf' => __('project.selling_price_psf'),
            'selling_price_unit' => __('project.selling_price_unit'),
            'maintenance_fee_psf' => __('project.maintenance_fee_psf'),
            'completion_date' => __('project.completion_date'),
            'sequence' => __('project.sequence'),
            'is_pet_friendly' => __('project.is_pet_friendly'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            // Parse translations from JSON
            $translations = json_decode($request->translations, true);
            if ( isset( $translations['en'] ) ) {
                foreach ( $translations as $lang => &$data ) {
                    foreach ( ['title', 'short_description', 'description'] as $field ) {
                        if ( empty( $data[$field] ) && !empty( $translations['en'][$field] ) ) {
                            $data[$field] = $translations['en'][$field];
                        }
                    }
                }
            }
            $projectCreate = Project::create([
                'title' => $translations['en']['title'],
                'translations' => json_encode( $translations ),
                'country_id' => $request->country_id,
                'project_status' => $request->project_status,
                'amenities' => $request->amenities,
                'property_type' => $request->property_type,
                'tenure' => $request->tenure,
                'min_bedrooms' => $request->min_bedrooms,
                'max_bedrooms' => $request->max_bedrooms,
                'min_bathrooms' => $request->min_bathrooms,
                'max_bathrooms' => $request->max_bathrooms,
                'bedroom_text' => $request->bedroom_text,
                'bathroom_text' => $request->bathroom_text,
                'min_carpark' => $request->min_carpark,
                'max_carpark' => $request->max_carpark,
                'carpark_text' => $request->carpark_text,
                'min_storeroom' => $request->min_storeroom,
                'max_storeroom' => $request->max_storeroom,
                'storeroom_text' => $request->storeroom_text,
                'balcony' => $request->balcony,
                'building_type' => $request->building_type,
                'furnishing_status' => $request->furnishing_status,
                'block_number' => $request->block_number,
                'total_floor' => $request->total_floor,
                'blocks' => $request->blocks,
                'build_up_area_psf' => $request->build_up_area_psf,
                'selling_price_psf' => $request->selling_price_psf,
                'selling_price_unit' => $request->selling_price_unit,
                'maintenance_fee_psf' => $request->maintenance_fee_psf,
                'completion_date' => $request->completion_date
                ? Carbon::createFromFormat('d/m/Y', $request->completion_date)->format('Y-m-d')
                : null,
                'sequence' => $request->sequence,
                'is_pet_friendly' => $request->is_pet_friendly,
                // location
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'address_line_3' => $request->address_line_3,
                'state' => $request->state,
                'project_locations' => $request->project_locations,
                'postcode' => $request->postcode,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'waze_url' => $request->waze_url,
                'google_map_url' => $request->google_map_url,
                'project_details' => $request->project_details,
            ]);

            // Handle developer associations
            if ($request->developers) {
                $developerIds = json_decode($request->developers, true);
                if (is_array($developerIds) && !empty($developerIds)) {
                    $projectCreate->developers()->attach($developerIds);
                }
            }

            // Handle logo upload
            if ($request->logo) {
                $logo = explode( ',', $request->logo );
                $imageFiles = FileManager::whereIn( 'id', $logo )->get();

                if ( $imageFiles ) {
                    foreach ( $imageFiles as $imageFile ) {
                        $fileName = explode( '/', $imageFile->file );
                        $fileExtention = pathinfo($fileName[1])['extension'];

                        $target = 'project/' . $projectCreate->id . '/' . $fileName[1];
                        Storage::disk( 'public' )->move( $imageFile->file, $target );

                        $projectCreate->logo = $target;
                        $projectCreate->save();

                        $imageFile->status = 10;
                        $imageFile->save();
                    }
                }
            }

            // Handle floorplan uploads
            self::handleFloorplanUploads($request, $projectCreate);

            //  Auto-create property based on block data
            // if ($request->block && $projectCreate->id) {
            //     self::createPropertyFromBlocks($projectCreate->id, json_decode($request->block, true));
            // }

            // Handle gallery images (new format from frontend)
            self::handleGalleryImagesCreate($request, $projectCreate->id);

            // Handle new gallery images
            if ($request->hasFile('new_images')) {
                self::handleGalleryUpload($request, $projectCreate->id);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.projects' ) ) ] ),
        ] );
    }
    
    public static function updateProject( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $rules = [
            'translations' => [ 'required' ],
            'translations' => [
                    'required',
                    function ( $attribute, $value, $fail ) use ( $request ) {
                        $decoded = json_decode( $value );

                        if ( $decoded->en->title == null ) {
                            $fail( __( 'validation.required', [
                                'attribute' => __( 'project.title' ),
                            ] ) );
                        }
                    },
            ],
            'agent_id' => [ 'nullable', 'exists:agents,id' ],
            'country_id' => [ 'nullable', 'exists:countries,id' ],
            'project_status' => [ 'nullable', 'integer', 'in:1,2' ],
            'amenities' => [ 'nullable' ],
            'property_type' => [ 'nullable', 'integer', 'in:1,2,3' ],
            'tenure' => [ 'nullable', 'string', 'max:255' ],
            'min_bedrooms' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'max_bedrooms' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'bedroom_text' => [ 'nullable', 'string', 'max:255' ],
            'min_bathrooms' => [ 'nullable', 'numeric', 'min:1', 'max:10' ],
            'max_bathrooms' => [ 'nullable', 'numeric', 'min:1', 'max:10' ],
            'bathroom_text' => [ 'nullable', 'string', 'max:255' ],
            'min_carpark' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'max_carpark' => [ 'nullable', 'numeric', 'min:0', 'max:10' ],
            'carpark_text' => [ 'nullable', 'string', 'max:255' ],
            'min_storeroom' => [ 'nullable', 'numeric', 'min:0', 'max:5' ],
            'max_storeroom' => [ 'nullable', 'numeric', 'min:0', 'max:5' ],
            'storeroom_text' => [ 'nullable', 'string', 'max:255' ],
            'balcony' => [ 'nullable', 'boolean' ],
            'building_type' => [ 'nullable' ],
            'furnishing_status' => [ 'nullable', 'integer', 'in:1,2,3' ],
            'block_number' => [ 'nullable', 'integer', 'min:0' ],
            'total_floor' => [ 'nullable', 'integer', 'min:0' ],
            'blocks' => [ 'nullable', 'array' ],
            'blocks.*.name' => [ 'nullable', 'string', 'max:255' ],
            'blocks.*.total_units' => [ 'nullable', 'integer', 'min:0' ],
            'blocks.*.block_prefix' => [ 'required', 'string', 'max:10' ],
            'blocks.*.total_floors' => [ 'nullable', 'integer', 'min:1', 'max:999' ],
            'blocks.*.floor_prefix' => [ 'nullable', 'string', 'max:10' ],
            'build_up_area_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'selling_price_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'selling_price_unit' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'maintenance_fee_psf' => [ 'nullable', 'numeric', 'min:0', 'max:99999999' ],
            'completion_date' => [ 'nullable', 'date_format:d/m/Y' ],
            'sequence' => [ 'nullable', 'integer', 'min:0' ],
            'is_pet_friendly' => [ 'nullable', 'boolean' ],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                
            'waze_url' => [ 'nullable' ],
            'google_map_url' => [ 'nullable' ],
            'project_details' => ['nullable', 'json'],

            // File rules for floorplans
            'new_floorplans.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,avif,webp|max:5120',
            'new_floorplans_remarks.*' => 'nullable|string|max:255',
        ];

        $validator = Validator::make( $request->all(), $rules );

        $attributeName = [
            'translations' => __('template.content_translations'),
            'agent_id' => __('template.agent'),
            'country_id' => __('template.country'),
            'project_status' => __('project.project_status'),
            'amenities' => __('project.amenities'),
            'property_type' => __('project.property_type'),
            'tenure' => __('project.tenure'),
            'min_bedrooms' => __('project.min_bedrooms'),
            'max_bedrooms' => __('project.max_bedrooms'),
            'bedroom_text' => __('project.bedroom_text'),
            'min_bathrooms' => __('project.min_bathrooms'),
            'max_bathrooms' => __('project.max_bathrooms'),
            'bathroom_text' => __('project.bathroom_text'),
            'min_carpark' => __('project.min_carpark'),
            'max_carpark' => __('project.max_carpark'),
            'carpark_text' => __('project.carpark_text'),
            'min_storeroom' => __('project.min_storeroom'),
            'max_storeroom' => __('project.max_storeroom'),
            'storeroom_text' => __('project.storeroom_text'),
            'balcony' => __('project.balcony'),
            'building_type' => __('project.building_type'),
            'furnishing_status' => __('project.furnishing_status'),
            'block_number' => __('project.block_number'),
            'total_floor' => __('project.total_floor'),
            'build_up_area_psf' => __('project.build_up_area_psf'),
            'selling_price_psf' => __('project.selling_price_psf'),
            'selling_price_unit' => __('project.selling_price_unit'),
            'maintenance_fee_psf' => __('project.maintenance_fee_psf'),
            'completion_date' => __('project.completion_date'),
            'sequence' => __('project.sequence'),
            'is_pet_friendly' => __('project.is_pet_friendly'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();
        try {
            $updateProject = Project::find( $request->id );

            // Parse translations from JSON
            $translations = json_decode($request->translations, true);

            if ( isset( $translations['en'] ) ) {
                foreach ( $translations as $lang => &$data ) {
                    foreach ( ['title', 'short_description', 'description'] as $field ) {

                        if ( empty( $data[$field] ) && !empty( $translations['en'][$field] ) ) {
                            $data[$field] = $translations['en'][$field];
                        }
                    }
                }
            }

            $updateProject->translations = $translations;
            $updateProject->title = $translations['en']['title'];

            // Update new property columns
            $updateProject->country_id = $request->country_id;
            $updateProject->project_status = $request->project_status;
            $updateProject->amenities = $request->amenities;
            $updateProject->property_type = $request->property_type;
            $updateProject->tenure = $request->tenure;
            $updateProject->min_bedrooms = $request->min_bedrooms;
            $updateProject->max_bedrooms = $request->max_bedrooms;
            $updateProject->min_bathrooms = $request->min_bathrooms;
            $updateProject->max_bathrooms = $request->max_bathrooms;
            $updateProject->bedroom_text = $request->bedroom_text;
            $updateProject->bathroom_text = $request->bathroom_text;
            $updateProject->min_carpark = $request->min_carpark;
            $updateProject->max_carpark = $request->max_carpark;
            $updateProject->carpark_text = $request->carpark_text;
            $updateProject->min_storeroom = $request->min_storeroom;
            $updateProject->max_storeroom = $request->max_storeroom;
            $updateProject->storeroom_text = $request->storeroom_text;
            $updateProject->balcony = $request->balcony;
            $updateProject->building_type = $request->building_type;
            $updateProject->furnishing_status = $request->furnishing_status;
            $updateProject->block_number = $request->block_number;
            $updateProject->total_floor = $request->total_floor;
            $updateProject->blocks = $request->blocks;
            $updateProject->build_up_area_psf = $request->build_up_area_psf;
            $updateProject->selling_price_psf = $request->selling_price_psf;
            $updateProject->selling_price_unit = $request->selling_price_unit;
            $updateProject->maintenance_fee_psf = $request->maintenance_fee_psf;
            $updateProject->completion_date = $request->completion_date
            ? Carbon::createFromFormat('d/m/Y', $request->completion_date)->format('Y-m-d')
            : null;
            $updateProject->sequence = $request->sequence;
            $updateProject->is_pet_friendly = $request->is_pet_friendly;

            $updateProject->address_line_1       = $request->address_line_1;
            $updateProject->address_line_2       = $request->address_line_2;
            $updateProject->address_line_3       = $request->address_line_3;
            $updateProject->state                = $request->state;
            $updateProject->project_locations   = $request->project_locations;
            $updateProject->postcode             = $request->postcode;
            $updateProject->longitude            = $request->longitude;
            $updateProject->latitude             = $request->latitude;
            $updateProject->waze_url             = $request->waze_url;
            $updateProject->google_map_url             = $request->google_map_url;
            $updateProject->project_details      = $request->project_details;

            // Handle developer associations
            if ($request->developers) {
                $developerIds = json_decode($request->developers, true);
                if (is_array($developerIds)) {
                    $updateProject->developers()->sync($developerIds);
                }
            } else {
                // If no developers selected, detach all
                $updateProject->developers()->detach();
            }

            // Handle logo upload
            if ($request->logo) {
                $logo = explode( ',', $request->logo );
                $imageFiles = FileManager::whereIn( 'id', $logo )->get();

                if ( $imageFiles ) {
                    foreach ( $imageFiles as $imageFile ) {
                        $fileName = explode( '/', $imageFile->file );
                        $fileExtention = pathinfo($fileName[1])['extension'];

                        $target = 'project/' . $updateProject->id . '/' . $fileName[1];
                        Storage::disk( 'public' )->move( $imageFile->file, $target );

                        $updateProject->logo = $target;
                        $updateProject->save();

                        $imageFile->status = 10;
                        $imageFile->save();
                    }
                }
            }

            // Handle floorplan uploads and updates
            self::handleFloorplanUploads($request, $updateProject);

            if ( $request->has('existing_images') && $request->existing_images != [] && $request->existing_images != '[]') {
                self::handleExistingImages(json_decode( $request->existing_images, true ), $updateProject->id);
            }

            // Handle gallery images (new format from frontend)
            self::handleGalleryImagesUpdate($request, $updateProject->id);

            $updateProject->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.projects' ) ) ] ),
        ] );
    }

    public static function allProjects( $request ) {

        $projects = Project::select( 'projects.*');

        $filterObject = self::filter( $request, $projects );
        $project = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $project->orderBy( 'projects.created_at', $dir );
                    break;
                case 3:
                    $project->orderBy( 'projects.title', $dir );
                    break;
            }
        }

        $projectCount = $project->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $projects = $project->skip( $offset )->take( $limit )->get();

        if ( $projects ) {
            $projects->append( [
                'encrypted_id',
                'logo_path',
                'decoded_translations',
                'decoded_project_details',
                'project_type_label',
                'project_status_type_label',
                'total_active_units',
                'total_active_units_left'
            ] );
        }

        $totalRecord = Project::count();

        $data = [
            'projects' => $projects,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $projectCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'projects.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'projects.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where(function ($q) use ($request) {
                $q->where('projects.translations', 'LIKE', '%' . $request->title . '%')
                  ->orWhere('projects.title', 'LIKE', '%' . $request->title . '%');
            });
            
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->property_type ) ) {
            $model->where( 'property_type', $request->property_type );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->project_status ) ) {
            $model->where( 'project_status', $request->project_status );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneProject( $request ) {

        $request->merge([
            'id' => $request->dataReturn
                ? $request->id
                : Helper::decode($request->id),
        ]);

        $project = Project::with('developers')->find( $request->id );

        $project->append( ['encrypted_id','logo_path','decoded_translations','project_locations_details','decoded_amenities', 'decoded_project_details'] );

        if( !$request->dataReturn ){
            return response()->json( $project );
        }else{
            return response()->json( [
                'success' => true,
                'data' => $project
            ] );
        }
    }

    public static function deleteProject( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'project.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Project::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.projects' ) ) ] ),
        ] );
    }

    public static function updateProjectStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProject = Project::find( $request->id );
            $updateProject->status = $updateProject->status == 10 ? 20 : 10;

            $updateProject->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'project' => $updateProject,
                    'message_key' => 'update_project_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_project_failed',
            ], 500 );
        }
    }

    public static function removeProjectLogoImage( $request ) {

        $updateProject = Project::find( Helper::decode($request->id) );
        
        switch ($request->scope) {
            case 'image':
                Storage::delete( 'public/' . $updateProject->logo );
                $updateProject->logo = null;
                break;
            
            default:
                break;
        }

        $updateProject->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'project.logo' ) ) ] ),
        ] );
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'project/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    private static function handleFloorplanUploads($request, $project)
    {
        // Handle deleted floorplans
        if ($request->has('deleted_floorplans')) {
            $deletedFloorplans = json_decode($request->deleted_floorplans, true);
            if (is_array($deletedFloorplans) && !empty($deletedFloorplans)) {
                foreach ($deletedFloorplans as $floorplanId) {
                    $floorplan = ProjectFloorplan::find($floorplanId);
                    if ($floorplan && $floorplan->project_id == $project->id) {
                        // Delete the files from storage
                        if ($floorplan->image) {
                            Storage::disk('public')->delete($floorplan->image);
                        }
                        if ($floorplan->additional_attachment) {
                            Storage::disk('public')->delete($floorplan->additional_attachment);
                        }
                        // Delete the record
                        $floorplan->delete();
                    }
                }
            }
        }

        // Handle existing floorplan updates (remarks and sequence)
        if ($request->has('existing_floorplans')) {
            $existingFloorplans = json_decode($request->existing_floorplans, true);
            if (is_array($existingFloorplans)) {
                foreach ($existingFloorplans as $floorplanData) {
                    $floorplan = ProjectFloorplan::find($floorplanData['id']);

                    if ($floorplan && $floorplan->project_id == $project->id) {
                        $floorplan->remarks = $floorplanData['remarks'] ?? '';
                        $floorplan->bedrooms = $floorplanData['bedrooms'] ? $floorplanData['bedrooms'] : 0;
                        $floorplan->bathrooms = $floorplanData['bathrooms'] ? $floorplanData['bathrooms'] : 0;
                        $floorplan->balcony = $floorplanData['balcony'] ? $floorplanData['balcony'] : 0;
                        $floorplan->storeroom = $floorplanData['storeroom'] ? $floorplanData['storeroom'] : 0;
                        $floorplan->parking_spaces = $floorplanData['parking_spaces'] ? $floorplanData['parking_spaces'] : 0;
                        $floorplan->upload_link = $floorplanData['upload_link'] ? $floorplanData['upload_link'] : 0;
                        $floorplan->category = $floorplanData['category'] ? $floorplanData['category'] : 0;
                        $floorplan->sequence = $floorplanData['sequence'] ? $floorplanData['sequence'] : 0;
                        $floorplan->save();
                    }
                }
            }
        }

        // Handle replaced floorplan images
        if ($request->has('replaced_floorplans')) {
            $replacedFloorplans = json_decode($request->replaced_floorplans, true);
            if (is_array($replacedFloorplans) && !empty($replacedFloorplans)) {
                foreach ($replacedFloorplans as $floorplanId => $replacementData) {
                    $floorplan = ProjectFloorplan::find($floorplanId);
                    if ($floorplan && $floorplan->project_id == $project->id) {
                        $fileKey = "replaced_floorplan_files.{$floorplanId}";
                        
                        if ($request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);
                            
                            // Validate file type and size
                            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'avif/png', 'webp/png', 'application/pdf'];
                            if (!in_array($file->getMimeType(), $allowedMimes)) {
                                continue; // Skip invalid files
                            }
                            
                            if ($file->getSize() > 5 * 1024 * 1024) { // 5MB limit
                                continue; // Skip files that are too large
                            }
                            
                            // Delete old file if it exists
                            if ($floorplan->image) {
                                Storage::disk('public')->delete($floorplan->image);
                            }
                            
                            // Generate unique filename
                            $filename = time() . '_replaced_' . $floorplanId . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                            
                            // Store the new file
                            $storedPath = $file->storeAs('project/' . $project->id . '/floorplans', $filename, 'public');
                            
                            // Update the floorplan record with the new image
                            $floorplan->image = $storedPath;
                            $floorplan->save();
                        }
                    }
                }
            }
        }

        // Handle additional attachment uploads for existing floorplans (new format from edit.blade.php)
        if ($request->has('existing_floorplans_additional_attachments')) {
            $floorplanIds = json_decode($request->existing_floorplans_additional_attachments, true);
            if (is_array($floorplanIds)) {
                foreach ($floorplanIds as $floorplanId) {
                    $floorplan = ProjectFloorplan::find($floorplanId);
                    if ($floorplan && $floorplan->project_id == $project->id) {
                        $fileKey = "existing_floorplan_additional_attachment.{$floorplanId}";
                        if ($request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);

                            // Validate file type and size
                            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'avif/png', 'webp/png', 'application/pdf'];
                            if (in_array($file->getMimeType(), $allowedMimes) && $file->getSize() <= 5 * 1024 * 1024) {
                                // Delete old additional attachment if it exists
                                if ($floorplan->additional_attachment) {
                                    Storage::disk('public')->delete($floorplan->additional_attachment);
                                }

                                // Generate unique filename
                                $filename = time() . '_attachment_' . $floorplan->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                                // Store the new file
                                $storedPath = $file->storeAs('project/' . $project->id . '/attachments', $filename, 'public');

                                // Update the floorplan record with the new additional attachment
                                $floorplan->additional_attachment = $storedPath;
                                $floorplan->save();
                            }
                        }
                    }
                }
            }
        }

        // Handle additional attachment uploads for existing floorplans (legacy format - fallback)
        $allFloorplans = ProjectFloorplan::where('project_id', $project->id)->get();
        foreach ($allFloorplans as $floorplan) {
            // Handle upload_link updates
            $linkKey = "floorplan_upload_link_{$floorplan->id}";
            if ($request->has($linkKey)) {
                $floorplan->upload_link = $request->input($linkKey);
            }

            // Handle additional_attachment file uploads (legacy format)
            $fileKey = "floorplan_additional_attachment_{$floorplan->id}";
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);

                // Validate file type and size
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'avif/png', 'webp/png', 'application/pdf'];
                if (in_array($file->getMimeType(), $allowedMimes) && $file->getSize() <= 5 * 1024 * 1024) {
                    // Delete old additional attachment if it exists
                    if ($floorplan->additional_attachment) {
                        Storage::disk('public')->delete($floorplan->additional_attachment);
                    }

                    // Generate unique filename
                    $filename = time() . '_attachment_' . $floorplan->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                    // Store the new file
                    $storedPath = $file->storeAs('project/' . $project->id . '/attachments', $filename, 'public');

                    // Update the floorplan record with the new additional attachment
                    $floorplan->additional_attachment = $storedPath;
                }
            }

            $floorplan->save();
        }

        // Handle new floorplan uploads
        $newFloorplansCount = $request->input('new_floorplans_count', 0);
        if ($newFloorplansCount > 0) {
            $maxSequence = ProjectFloorplan::where('project_id', $project->id)->max('sequence') ?? 0;
            
            for ($i = 0; $i < $newFloorplansCount; $i++) {
                $fileKey = "new_floorplans.{$i}";
                $remarksKey = "new_floorplans_remarks.{$i}";
                $bedroomsKey = "new_floorplans_bedrooms.{$i}";
                $bathroomsKey = "new_floorplans_bathrooms.{$i}";
                $balconyKey = "new_floorplans_balcony.{$i}";
                $storeroomKey = "new_floorplans_storeroom.{$i}";
                $parkingSpacesKey = "new_floorplans_parking_spaces.{$i}";
                $uploadLinkKey = "new_floorplans_upload_link.{$i}";
                $categoryKey = "new_floorplans_category.{$i}";
                $additionalAttachmentKey = "new_floorplans_additional_attachment.{$i}";
                
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    
                    // Validate file type and size
                    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'avif/png', 'webp/png', 'application/pdf'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        continue; // Skip invalid files
                    }
                    
                    if ($file->getSize() > 5 * 1024 * 1024) { // 5MB limit
                        continue; // Skip files that are too large
                    }
                    
                    // Generate unique filename
                    $filename = time() . '_' . $i . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = 'project/' . $project->id . '/floorplans/' . $filename;

                    // Store the file
                    $storedPath = $file->storeAs('project/' . $project->id . '/floorplans', $filename, 'public');

                    // Handle additional attachment for this new floorplan
                    $additionalAttachmentPath = null;
                    if ($request->hasFile($additionalAttachmentKey)) {
                        $additionalFile = $request->file($additionalAttachmentKey);

                        // Validate additional attachment file type and size
                        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'avif/png', 'webp/png', 'application/pdf'];
                        if (in_array($additionalFile->getMimeType(), $allowedMimes) && $additionalFile->getSize() <= 5 * 1024 * 1024) {
                            // Generate unique filename for additional attachment
                            $attachmentFilename = time() . '_attachment_' . $i . '_' . Str::random(10) . '.' . $additionalFile->getClientOriginalExtension();
                            $additionalAttachmentPath = $additionalFile->storeAs('project/' . $project->id . '/attachments', $attachmentFilename, 'public');
                        }
                    }

                    // Get remarks or use original filename if empty
                    $remarks = $request->input($remarksKey, '');
                    if (empty($remarks)) {
                        // Use filename without extension
                        $originalName = $file->getClientOriginalName();
                        $remarks = pathinfo($originalName, PATHINFO_FILENAME);
                    }

                    // Create floorplan record
                    ProjectFloorplan::create([
                        'project_id' => $project->id,
                        'sequence' => $maxSequence + $i + 1,
                        'image' => $storedPath,
                        'remarks' => $remarks,
                        'bedrooms' => $request->input($bedroomsKey, 0),
                        'bathrooms' => $request->input($bathroomsKey, 0),
                        'balcony' => $request->input($balconyKey, 0),
                        'storeroom' => $request->input($storeroomKey, 0),
                        'parking_spaces' => $request->input($parkingSpacesKey, 0),
                        'upload_link' => $request->input($uploadLinkKey, 0),
                        'additional_attachment' => $additionalAttachmentPath,
                        'category' => $request->input($categoryKey, 0),
                        'status' => 10,
                    ]);
                }
            }
        }

        // Handle floorplan ordering if provided
        if ($request->has('floorplans_order')) {
            $floorplanOrder = json_decode($request->floorplans_order, true);
            if (is_array($floorplanOrder)) {
                foreach ($floorplanOrder as $index => $floorplanId) {
                    // Handle both existing and new floorplan IDs
                    if (is_numeric($floorplanId)) {
                        $floorplan = ProjectFloorplan::find($floorplanId);
                        if ($floorplan && $floorplan->project_id == $project->id) {
                            $floorplan->sequence = $index + 1;
                            $floorplan->save();
                        }
                    }
                    // New floorplans with string IDs will be handled in sequence by creation order above
                }
            }
        }
    }

    public static function getFloorplans($request)
    {
        $request->merge([
            'project_id' => is_numeric($request->project_id)
                ? $request->project_id
                : Helper::decode($request->project_id),
        ]);

        $floorplans = ProjectFloorplan::where('project_id', $request->project_id)
            ->where('status', 10)
            ->orderBy('sequence')
            ->get();

        $floorplans->each(function ($floorplan) {
            $floorplan->floorplan_url = $floorplan->image_path;
            $floorplan->append( ['additional_attachment_path'] );
        });

        return response()->json([
            'success' => true,
            'floorplans' => $floorplans
        ]);
    }

    public static function getFloorplan($request)
    {
        $floorplans = ProjectFloorplan::where('id', $request->id)
            ->first();

        $floorplans->each(function ($floorplan) {
            $floorplan->floorplan_url = $floorplan->image_path;
        });

        return response()->json([
            'success' => true,
            'data' => $floorplans
        ]);
    }

    /**
     * API: Search Projects with filtering and sorting
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Supported filters:
     * - search: Search in title and translations
     * - id: Specific project ID
     * - project_locations: Array of location IDs
     * - sort: price_high_low, price_low_high, buildup_large_small, buildup_small_large
     * - property_type: Array of property types (1=residential, 2=commercial, 3=industrial)
     * - min_price, max_price: Price range filters
     * - min_buildup, max_buildup: Build-up area range filters
     * - tenure: Array of tenure types (freehold, leasehold)
     * - bedrooms: Array of bedroom counts (0-10)
     * - bathrooms: Array of bathroom counts (1-10)
     * - amenities: Array of amenity IDs
     * - unit_type: Array of unit types (long_rental_unit, short_rental_unit, for_sale_unit)
     * - page: Page number for pagination
     * - per_page: Items per page (max 50)
     */
    public static function searchProjectsApi($request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'id' => 'nullable|integer|exists:projects,id',
            'project_locations' => 'nullable|array',
            'project_locations.*' => 'integer|exists:property_locations,id',
            'sort' => 'nullable|string|in:price_high_low,price_low_high,buildup_large_small,buildup_small_large',
            'property_type' => 'nullable|array',
            'property_type.*' => 'integer|in:1,2,3',
            'project_type' => 'nullable|array',
            'project_type.*' => 'integer|in:1,2,3',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_buildup' => 'nullable|numeric|min:0',
            'max_buildup' => 'nullable|numeric|min:0',
            'tenure' => 'nullable|array',
            'tenure.*' => 'string|in:freehold,leasehold',
            'bedrooms' => 'nullable|array',
            'bedrooms.*' => 'integer|min:0|max:10',
            'bathrooms' => 'nullable|array',
            'bathrooms.*' => 'integer|min:1|max:10',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            'unit_type' => 'nullable|array',
            'unit_type.*' => 'string|in:long_rental_unit,short_rental_unit,for_sale_unit',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Start building the query
            $query = Project::with(['developers', 'country', 'floorplans'])
                ->where('status', 10); // Only active projects

            // 🔹 Filter by price range
            $min = $request->filled( 'min_price' ) ? ( int ) $request->min_price : 0;
            $max = $request->filled( 'max_price' ) ? ( int ) $request->max_price : PHP_INT_MAX;

            // Apply project-level filtering (only include projects where ALL properties are within range)
            // Exclude projects that have any property outside the price range
            $query->whereDoesntHave( 'properties', function ( $propertyQuery ) use ( $min, $max ) {
                $propertyQuery->where(function($q) use ($min, $max) {
                    $q->where(DB::raw('COALESCE(selling_price_unit, 0)'), '<', $min)
                      ->orWhere(DB::raw('COALESCE(selling_price_unit, 0)'), '>', $max);
                });
            });

            // When loading projects, only load matching properties within range
            $query->with( [ 'properties' => function ( $propertyQuery ) use ( $min, $max ) {
                $propertyQuery->whereBetween(
                    DB::raw('COALESCE(selling_price_unit, 0)'),
                    [ $min, $max ]
                );
            } ] );

            // Filter by specific project ID
            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }

            // Search in title and translations
            if ( $request->filled( 'search' ) ) {
                $search = strtolower( $request->search );
            
                $query->where( function ( $q ) use ( $search ) {
                    // Title and state match
                    $q->where(function($subQ) use ($search) {
                        $subQ->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%'])
                             ->orWhereRaw('LOWER(state) LIKE ?', ['%' . $search . '%']);
                    });
            
                    // Find matching property locations
                    $matchingLocations = DB::table( 'property_locations' )
                        ->whereRaw( 'LOWER(title) LIKE ?', [ '%' . $search . '%' ] )
                        ->pluck( 'id' );
            
                    // Apply same JSON-safe filter if found
                    if ( $matchingLocations->isNotEmpty() ) {
                        $q->orWhere( function ( $q2 ) use ( $matchingLocations ) {
                            foreach ( $matchingLocations as $locationId ) {
                                $asNumberJson = $locationId;                      // 44
                                $asStringJson = json_encode( (string) $locationId ); // "44"
                                $like = '%"' . $locationId . '"%';                // fallback LIKE
            
                                $q2->orWhereRaw("
                                    (
                                        JSON_VALID(project_locations)
                                        AND (
                                            JSON_CONTAINS(project_locations, ?)
                                            OR JSON_CONTAINS(project_locations, ?)
                                        )
                                        OR
                                        ( JSON_VALID(JSON_UNQUOTE(project_locations))
                                        AND (
                                            JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?)
                                            OR JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?)
                                        ) )
                                        OR
                                        ( project_locations LIKE ? )
                                    )
                                ", [
                                    json_encode((string) $locationId), // "33"
                                    json_encode((int) $locationId),    // 33
                                    json_encode((string) $locationId),
                                    json_encode((int) $locationId),
                                    '%"'.$locationId.'"%'
                                ]);


                            }
                        } );
                    }
                } );
                
                // Prioritize title matches first, then location/state matches
                $query->orderByRaw("
                    CASE
                        WHEN LOWER(title) = ? THEN 0
                        WHEN LOWER(title) LIKE ? THEN 1
                        WHEN LOWER(title) LIKE ? THEN 2
                        ELSE 3
                    END
                ", [
                    $search,              // exact match (priority 0)
                    $search . '%',        // starts with search term (priority 1)
                    '%' . $search . '%',  // contains search term anywhere in title (priority 2)
                                         // location/state matches get priority 3
                ]);

                // $query->orderBy( 'created_at', 'DESC' );

            }else{
                $query->orderBy( 'created_at', 'DESC' );
            }

            // Filter by property locations (accepts array)
            if ($request->filled('project_locations')) {
                $locationIds = [];
                
                // Handle both array and string (comma-separated) formats for backward compatibility
                if (is_array($request->project_locations)) {
                    $locationIds = $request->project_locations;
                } else {
                    // Legacy string format support
                    $locationIds = array_map('trim', explode(',', $request->project_locations));
                    $locationIds = array_filter($locationIds, 'is_numeric');
                }

                if (!empty($locationIds)) {
                    $query->where(function ($q) use ($locationIds) {
                        foreach ($locationIds as $locationId) {
                            // two JSON forms:
                            //  - numeric JSON value: 44
                            //  - string JSON value: "44"
                            $asNumberJson = $locationId;                     // e.g. 44  (valid JSON literal)
                            $asStringJson = json_encode((string) $locationId); // e.g. "44"
                            $like = '%"'.$locationId.'"%';                   // fallback match inside quoted JSON
                
                            $q->orWhereRaw("
                                (
                                  ( JSON_VALID(project_locations) AND ( JSON_CONTAINS(project_locations, ?) OR JSON_CONTAINS(project_locations, ?) ) )
                                  OR
                                  ( JSON_VALID(JSON_UNQUOTE(project_locations)) AND ( JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?) OR JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?) ) )
                                  OR
                                  ( project_locations LIKE ? )
                                )
                            ", [
                                $asNumberJson, $asStringJson,    // checks when column is valid JSON
                                $asNumberJson, $asStringJson,    // checks when column is a quoted JSON string (double-encoded)
                                $like                             // fallback: match the quoted id inside the stored text
                            ]);
                        }
                    });
                }
                
            }

            if ( $request->filled( 'building_type' ) && is_array( $request->building_type ) ) {
                $buildingTypeRanges = [
                    1 => range( 1, 7 ),   // Residential
                    2 => range( 8, 13 ),  // Commercial
                    3 => range( 14, 17 ), // Industrial / Land
                ];
            
               
                $matchedGroups = [];

                foreach ( $request->building_type as $typeId ) {
                    foreach ( $buildingTypeRanges as $groupId => $range ) {
                        if ( in_array( (int) $typeId, $range ) ) {
                            $matchedGroups[] = $groupId;
                            break; // stop once matched
                        }
                    }
                }

                $matchedGroups = array_values( array_unique( $matchedGroups ) );


                // Merge and ensure both property_type and project_type are arrays
                $request->merge([
                    'property_type' => array_unique(array_merge(
                        (array) $request->property_type,
                        $matchedGroups
                    )),
                    'project_type' => array_unique(array_merge(
                        (array) $request->project_type,
                        $matchedGroups
                    )),
                ]);
            }            
            
            // Filter by property type (accepts array)
            if ($request->filled('property_type')) {
                if (is_array($request->property_type)) {
                    $query->whereIn('property_type', $request->property_type);
                } else {
                    // Legacy single value support
                    $query->where('property_type', $request->property_type);
                }
            }

            if ($request->filled('project_type')) {
                if (is_array($request->project_type)) {
                    $query->whereIn('property_type', $request->project_type);
                } else {
                    // Legacy single value support
                    $query->where('property_type', $request->project_type);
                }
            }

            if ($request->filled('building_type')) {
                if (is_array($request->building_type)) {
                    $query->whereIn('building_type', $request->building_type);
                } else {
                    // Legacy single value support
                    $query->where('building_type', $request->building_type);
                }
            }

            // 🔹 Filter by price range
            if ( $request->filled( 'min_price' ) ) {
                $min = ( int ) $request->min_price;
            
                $query->whereHas( 'properties', function ( $propertyQuery ) use ( $min ) {
                    $propertyQuery->whereRaw(
                        'COALESCE(selling_price_unit, 0) >= ?',
                        [ $min ]
                    );
                });
            }
            
            if ( $request->filled( 'max_price' ) ) {
                $max = ( int ) $request->max_price;
                $min = $request->filled( 'min_price' ) ? ( int ) $request->min_price : 0;
            
                if ( $max >= $min ) {
                    $query->whereHas( 'properties', function ( $propertyQuery ) use ( $max ) {
                        $propertyQuery->whereRaw(
                            'COALESCE(selling_price_unit, 0) <= ?',
                            [ $max ]
                        );
                    });
                }
            }

            // 🔹 Filter by build-up area range
            if ( $request->filled( 'min_buildup' ) ) {
                $minBuildup = (int) $request->min_buildup;

                $query->whereHas( 'properties', function ( $propertyQuery ) use ( $minBuildup ) {
                    $propertyQuery->where( 'build_up_area_psf', '>=', $minBuildup );
                });
            }

            if ( $request->filled( 'max_buildup' ) ) {
                $maxBuildup = (int) $request->max_buildup;

                $query->whereHas( 'properties', function ( $propertyQuery ) use ( $maxBuildup ) {
                    $propertyQuery->where( 'build_up_area_psf', '<=', $maxBuildup );
                });
            }


            // Filter by tenure (accepts array)
            // if ($request->filled('tenure')) {
            //     if (is_array($request->tenure)) {
            //         $query->whereIn('tenure', $request->tenure);
            //     } else {
            //         // Legacy single value support
            //         $query->where('tenure', $request->tenure);
            //     }
            // }

            // Filter by bedrooms array
            if ($request->filled('bedrooms') && is_array($request->bedrooms)) {
                $bedroomValues = $request->bedrooms;
                $query->where(function ($q) use ($bedroomValues) {
                    foreach ($bedroomValues as $bedroomCount) {
                        $q->orWhere(function ($subQuery) use ($bedroomCount) {
                            $subQuery->where(function ($rangeQuery) use ($bedroomCount) {
                                // Check if the bedroom count falls within the project's min/max range
                                $rangeQuery->where('min_bedrooms', '<=', $bedroomCount)
                                          ->where('max_bedrooms', '>=', $bedroomCount);
                            })
                            // Also check legacy single bedroom field for backward compatibility
                            ->orWhere('bedrooms', $bedroomCount);
                        });
                    }
                });
            }

            // Filter by bathrooms array
            if ($request->filled('bathrooms') && is_array($request->bathrooms)) {
                $bathroomValues = $request->bathrooms;
                $query->where(function ($q) use ($bathroomValues) {
                    foreach ($bathroomValues as $bathroomCount) {
                        $q->orWhere(function ($subQuery) use ($bathroomCount) {
                            $subQuery->where(function ($rangeQuery) use ($bathroomCount) {
                                // Check if the bathroom count falls within the project's min/max range
                                $rangeQuery->where('min_bathrooms', '<=', $bathroomCount)
                                          ->where('max_bathrooms', '>=', $bathroomCount);
                            })
                            // Also check legacy single bathroom field for backward compatibility
                            ->orWhere('bathrooms', $bathroomCount);
                        });
                    }
                });
            }

            // Filter by amenities
            if ($request->filled('amenities') && is_array($request->amenities)) {
                $amenityIds = $request->amenities;
                $query->where(function ($q) use ($amenityIds) {
                    foreach ($amenityIds as $amenityId) {
                        $q->orWhereJsonContains('amenities', (string)$amenityId);
                    }
                });
            }

            // Filter by unit type - check property units
            if ($request->filled('unit_type') && is_array($request->unit_type)) {
                $unitTypes = $request->unit_type;
                $query->whereHas('properties.units', function ($unitQuery) use ($unitTypes) {
                    $unitQuery->whereIn('unit_type', $unitTypes)
                             ->where('status', 10);
                });
            }

            // Apply sorting
            switch ( $request->sort ) {
                case 'price_high_low':
                    $query->whereHas( 'properties' )
                        ->with( 'properties' )
                        ->orderByRaw(
                            '(SELECT GREATEST(COALESCE(p.selling_price_psf, 0), COALESCE(p.selling_price_unit, 0)) 
                              FROM properties p 
                              WHERE p.project_id = projects.id 
                              ORDER BY GREATEST(COALESCE(p.selling_price_psf, 0), COALESCE(p.selling_price_unit, 0)) DESC 
                              LIMIT 1
                            ) DESC'
                        );
                    break;
            
                case 'price_low_high':
                    $query->whereHas( 'properties' )
                        ->with( 'properties' )
                        ->orderByRaw(
                            '(SELECT GREATEST(COALESCE(p.selling_price_psf, 0), COALESCE(p.selling_price_unit, 0)) 
                              FROM properties p 
                              WHERE p.project_id = projects.id 
                              ORDER BY GREATEST(COALESCE(p.selling_price_psf, 0), COALESCE(p.selling_price_unit, 0)) ASC 
                              LIMIT 1
                            ) ASC'
                        );
                    break;
            
                case 'buildup_large_small':
                    $query->whereHas( 'properties' )
                        ->with( 'properties' )
                        ->orderByRaw(
                            '(SELECT p.build_up_area_psf 
                              FROM properties p 
                              WHERE p.project_id = projects.id 
                              ORDER BY p.build_up_area_psf DESC 
                              LIMIT 1
                            ) DESC'
                        );
                    break;
            
                case 'buildup_small_large':
                    $query->whereHas( 'properties' )
                        ->with( 'properties' )
                        ->orderByRaw(
                            '(SELECT p.build_up_area_psf 
                              FROM properties p 
                              WHERE p.project_id = projects.id 
                              ORDER BY p.build_up_area_psf ASC 
                              LIMIT 1
                            ) ASC'
                        );
                    break;
            
                // default:
                //     $query->orderBy( 'created_at', 'DESC' );
                //     break;
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $perPage = min($perPage, 50); // Limit to 50 items per page max
            
            $projects = $query->paginate($perPage);

            // Transfothe data for API response
            $projects->getCollection()->transform(function ($project) use ($perPage, $request) {

                $newTitle = $project->title;
                if( $project->translations ){
                    $newTitle = $project->decoded_translations ? $project->decoded_translations->en->title : $project->title;
                }

                // Check if project is favorited by the authenticated user
                $isFavourite = false;

                if (auth('user')->check()) {
                    $isFavourite = FavouriteProject::where('user_id', auth('user')->user()->id)
                        ->where('project_id', $project->id)
                        ->where('status', 10)
                        ->where('favourite_type', 1)
                        ->exists();
                }

                $propertyPricesQuery = \DB::table( 'properties' )
                    ->where( 'project_id', $project->id )
                    ->where( 'status', 10 )
                    ->whereNotNull( 'selling_price_unit' )
                    ->where( 'selling_price_unit', '>', 0 );

                // 🔹 Apply min_price filter (if provided)
                if ( $request->filled( 'min_price' ) ) {
                    $min = ( int ) $request->min_price;
                    $propertyPricesQuery->where( 'selling_price_unit', '>=', $min );
                }

                // 🔹 Apply max_price filter (if provided)
                if ( $request->filled( 'max_price' ) ) {
                    $max = ( int ) $request->max_price;
                    $propertyPricesQuery->where( 'selling_price_unit', '<=', $max );
                }

                // 🔹 Get min and max within range
                $propertyPrices = $propertyPricesQuery
                    ->selectRaw( 'MIN(selling_price_unit) as min_price, MAX(selling_price_unit) as max_price' )
                    ->first();


                $minPropertyPrice = $propertyPrices ? Helper::numberFormatV2($propertyPrices->min_price,2,true) : Helper::numberFormatV2( 0, 2, true );
                $maxPropertyPrice = $propertyPrices ? Helper::numberFormatV2($propertyPrices->max_price,2,true) : Helper::numberFormatV2( 0, 2, true );

                // Calculate min/max rental prices from property units
                $rentalPrices = \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('properties.status', 10)
                    ->where('property_units.status', 10)
                    ->whereNotNull('property_units.rental_price')
                    ->where('property_units.rental_price', '>', 0)
                    ->selectRaw('MIN(property_units.rental_price) as min_rental, MAX(property_units.rental_price) as max_rental')
                    ->first();

                $minRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->min_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );
                $maxRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->max_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );

                // Calculate min/max values from block JSON data of properties under this project
                $properties = \DB::table('properties')
                    ->where('project_id', $project->id)
                    ->where('status', 10)
                    ->get(['build_up_area_psf', 'selling_price_psf', 'selling_price_unit']);

                $buildupValues = [];
                $pricePsfValues = [];
                $priceUnitValues = [];
                $maintenanceValues = [];

                foreach ($properties as $property) {
                    if ($property->build_up_area_psf > 0) {
                        $buildupValues[] = $property->build_up_area_psf;

                        if ($project->maintenance_fee_psf > 0) {
                            $maintenanceValues[] = $property->build_up_area_psf * $project->maintenance_fee_psf;
                        }
                    }

                    if ($property->selling_price_psf > 0) {
                        $pricePsfValues[] = $property->selling_price_psf;
                    }

                    if ($property->selling_price_unit > 0) {
                        $priceUnitValues[] = $property->selling_price_unit;
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

                // Extract buildup values for API response
                $minBuildup = $propertyRanges->min_buildup ?? 0;
                $maxBuildup = $propertyRanges->max_buildup ?? 0;

                // Generate text ranges
                $buildupText = $minBuildup && $maxBuildup
                    ? ($minBuildup == $maxBuildup ? Helper::numberFormatV2($minBuildup ?? 0, 2, true) . ' sqft' : 'From ' . Helper::numberFormatV2($minBuildup ?? 0, 2, true) . ' sqft - ' . Helper::numberFormatV2($maxBuildup ?? 0, 2, true) . ' sqft')
                    : null;

                $pricePsfText = $propertyRanges->min_price_psf && $propertyRanges->max_price_psf
                    ? ($propertyRanges->min_price_psf == $propertyRanges->max_price_psf
                        ? '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true)
                        : '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_psf ?? 0, 2, true))
                    : null;

                $priceUnitText = $propertyRanges->min_price_unit && $propertyRanges->max_price_unit
                    ? ($propertyRanges->min_price_unit == $propertyRanges->max_price_unit
                        ? '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true)
                        : '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_unit ?? 0, 2, true))
                    : null;

                $maintenanceText = $propertyRanges->min_maintenance && $propertyRanges->max_maintenance
                    ? ($propertyRanges->min_maintenance == $propertyRanges->max_maintenance
                        ? '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true)
                        : '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_maintenance ?? 0, 2, true))
                    : null;

                $contactPhone = Option::where( 'option_name', 'OFFICIAL_PHONE_NUMBER' )->value('option_value') ?? "";
                $contactWhatsapp = Option::where( 'option_name', 'WHATSAPP' )->value('option_value') ?? "";

                // Base data that's always included
                $baseData = [
                    'id' => $project->id,
                    'is_favourite' => $isFavourite,
                    'project_tags' => $project->project_tags,
                    // Additional required fields
                    'galleries' => $project->galleries()
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

                    'logo' => $project->logo_path,
                    'logo_path' => $project->logo_path,
                    'min_property_price' => $minPropertyPrice,
                    'max_property_price' => $maxPropertyPrice,
                    'min_buildup' => $minBuildup,
                    'max_buildup' => $maxBuildup,
                    'min_rental_price' => $minRentalPrice,
                    'max_rental_price' => $maxRentalPrice,

                    // Property unit counts from all properties in this project
                    'available_units' => \DB::table('property_units')
                        ->join('properties', 'property_units.property_id', '=', 'properties.id')
                        ->where('properties.project_id', $project->id)
                        ->where('property_units.status', 10)
                        ->where('unit_type', 1)
                        ->count(),

                    'for_sale_count' => \DB::table('property_units')
                        ->join('properties', 'property_units.property_id', '=', 'properties.id')
                        ->where('properties.project_id', $project->id)
                        ->where('property_units.unit_type', 1)
                        ->where('property_units.status', 10)
                        ->count(),

                    'for_rent_count' => \DB::table('property_units')
                        ->join('properties', 'property_units.property_id', '=', 'properties.id')
                        ->where('properties.project_id', $project->id)
                        ->whereIn('property_units.unit_type', [2, 3])
                        ->where('property_units.status', 10)
                        ->count(),

                    'phone_number' => $contactPhone,
                    'whatsapp_link' => Helper::whatsAppBaseLink($contactWhatsapp), 
                    'translations' => $project->decoded_translations,
                    'project_details' => $project->decoded_project_details,
                    'property_type_label' => self::getPropertyTypeLabel($project->property_type),
                    'project_type_label' => self::getPropertyTypeLabel($project->property_type),
                    // 'tenure' => $project->tenure,
                    'location' => [
                        'address_line_1' => $project->address_line_1,
                        'address_line_2' => $project->address_line_2,
                        'address_line_3' => $project->address_line_3,
                        'state' => $project->state,
                        'postcode' => $project->postcode,
                        'latitude' => $project->latitude,
                        'longitude' => $project->longitude,
                        'waze_url' => $project->waze_url ?? null,
                        'google_map_url' => $project->google_map_url ?? null,
                        'locations' => $project->project_locations_details,
                    ],
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
                    'project_status_label' => self::getProjectStatusLabel($project->project_status),
                    'is_pet_friendly' => $project->is_pet_friendly == 1 ?? false,
                    'address_line_1' => $project->address_line_1,
                    'address_line_2' => $project->address_line_2,
                    'address_line_3' => $project->address_line_3,
                    'state' => $project->state,
                    'postcode' => $project->postcode,
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude,
                    'amenities_list' => $project->amenities_details,
                    'developers' => $project->developers->map(function ($developer) {
                        return $developer->name;
                    }),
                    'country' => $project->country ? $project->country->country_name : null,
                    'floorplans' => $project->floorplans
                        ->filter(function ($floorplan){
                            return $floorplan->property !== null; // only keep floorplans with property
                        })
                        ->values()
                        ->map(function ($floorplan) use ($request) {
                        return [
                            'property_id' => $floorplan->property ? $floorplan->property->id : 0,
                            'sequence' => $floorplan->sequence,
                            'property_tags' => $floorplan->project->project_tags,
                            'image' => $floorplan->image_path,
                            'image_path' => $floorplan->image_path,
                            'remarks' => $floorplan->remarks,
                            'bedrooms' => $floorplan->property ? (int) $floorplan->property->bedroom_text : 0,
                            'bathrooms' => $floorplan->property ? (int) $floorplan->property->bathroom_text : 0,
                            'balcony' => $floorplan->property ? $floorplan->property->balcony : 0,
                            'storeroom' => $floorplan->property ? $floorplan->property->storeroom : 0,
                            'parking_spaces' => $floorplan->property ? $floorplan->property->parking_spaces : 0,
                            'category' => $floorplan->category,
                            'document_link' => $floorplan->upload_link,
                            'is_pet_friendly' => $floorplan->project->is_pet_friendly == 1 ? 'Yes' : 'No',
                            'completion_date' => $floorplan->project->completion_date,
                            'units_left' => $floorplan->property ?
                                self::getUnitsCountByType($floorplan->property, $request->unit_type, 'available') : 0,
                            // pricing etc
                            'build_up_area_psf' => $floorplan->property ? Helper::numberFormatV2($floorplan->property->build_up_area_psf, 2, true) : Helper::numberFormatV2( 0, 2, true ),
                            'selling_price_psf' => 'RM' . ( $floorplan->property ? Helper::numberFormatV2($floorplan->property->selling_price_psf, 2, true) : Helper::numberFormatV2( 0, 2, true ) ),
                            'selling_price_unit' => 'RM' . ( $floorplan->property ? Helper::numberFormatV2($floorplan->property->selling_price_unit, 2, true) : Helper::numberFormatV2( 0, 2, true ) ),
                            'maintenance_fee_psf' => 'RM' . ( $floorplan->property ? Helper::numberFormatV2($floorplan->property->maintenance_fee_psf, 2, true) : Helper::numberFormatV2( 0, 2, true ) ),
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
                        'furnishing_status_label' => self::getFurnishingStatusLabel($project->furnishing_status),
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
                        'floorplans' => $project->floorplans->map(function ($floorplan) {
                            return [
                                'id' => $floorplan->id,
                                'sequence' => $floorplan->sequence,
                                'image' => $floorplan->image_path,
                                'remarks' => $floorplan->remarks,
                                'bedrooms' => $floorplan->bedrooms,
                                'bathrooms' => $floorplan->bathrooms,
                                'balcony' => $floorplan->balcony,
                                'storeroom' => $floorplan->storeroom,
                                'parking_spaces' => $floorplan->parking_spaces,
                            ];
                        })->values(),
                        'created_at' => $project->created_at,
                        'updated_at' => $project->updated_at,
                    ]);
                }

                return $baseData;
            });

            return response()->json([
                'success' => true,
                'data' => $projects->items(),
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching projects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get single project details
     *
     * @queryParam id integer required The project ID. Example: 1
     * @queryParam unit_type integer Filter units by type. 1=For Sale, 2=Long Rental, 3=Short Rental, 4=Appointment. If empty, returns all available units. Example: 1
     */
    public static function oneProjectApi($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:projects,id',
            'unit_type' => 'nullable|integer|in:1,2,3,4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'errors' => $validator->errors()
            ], 404);
        }

        try {
            $project = Project::with(['developers', 'country', 'floorplans.properties.prices'])
                ->where('id', $request->id)
                ->where('status', 10)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or inactive'
                ], 404);
            }

            $newTitle = $project->title;

            if( $project->translations ){
                $newTitle = $project->decoded_translations ? $project->decoded_translations->en->title : $project->title;
            }

            // Calculate min/max property prices for this project
            $propertyPrices = \DB::table('properties')
                ->where('project_id', $project->id)
                ->where('status', 10)
                ->whereNotNull('selling_price_unit')
                ->where('selling_price_unit', '>', 0)
                ->selectRaw('MIN(COALESCE(min_selling_price_unit, selling_price_unit)) as min_price, MAX(selling_price_unit) as max_price')
                ->first();

            $minPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->min_price,2, true) : Helper::numberFormatV2( 0, 2, true );
            $maxPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->max_price,2, true) : Helper::numberFormatV2( 0, 2, true );

            // Calculate min/max rental prices from property units
            $rentalPrices = \DB::table('property_units')
                ->join('properties', 'property_units.property_id', '=', 'properties.id')
                ->where('properties.project_id', $project->id)
                ->where('properties.status', 10)
                ->where('property_units.status', 10)
                ->whereNotNull('property_units.rental_price')
                ->where('property_units.rental_price', '>', 0)
                ->selectRaw('MIN(property_units.rental_price) as min_rental, MAX(property_units.rental_price) as max_rental')
                ->first();

            $minRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->min_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );
            $maxRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->max_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );

            // Calculate min/max values from block JSON data of properties under this project
            $properties = \DB::table('properties')
                ->where('project_id', $project->id)
                ->where('status', 10)
                ->get([
                    'build_up_area_psf', 'min_build_up_area_psf',
                    'selling_price_psf', 'min_selling_price_psf',
                    'selling_price_unit', 'min_selling_price_unit',
                    'maintenance_fee_psf', 'min_maintenance_fee_psf'
                ]);

            $buildupValues = [];
            $pricePsfValues = [];
            $priceUnitValues = [];
            $maintenanceValues = [];

            foreach ($properties as $property) {
                // Collect build up area values (both min and max)
                if ($property->build_up_area_psf > 0) {
                    $buildupValues[] = $property->build_up_area_psf;
                }
                if ($property->min_build_up_area_psf > 0) {
                    $buildupValues[] = $property->min_build_up_area_psf;
                }

                // Collect selling price PSF values (both min and max)
                if ($property->selling_price_psf > 0) {
                    $pricePsfValues[] = $property->selling_price_psf;
                }
                if ($property->min_selling_price_psf > 0) {
                    $pricePsfValues[] = $property->min_selling_price_psf;
                }

                // Collect selling price unit values (both min and max)
                if ($property->selling_price_unit > 0) {
                    $priceUnitValues[] = $property->selling_price_unit;
                }
                if ($property->min_selling_price_unit > 0) {
                    $priceUnitValues[] = $property->min_selling_price_unit;
                }

                // Collect maintenance fee values (both min and max)
                if ($property->maintenance_fee_psf > 0) {
                    $maintenanceValues[] = $property->maintenance_fee_psf;
                }
                if ($property->min_maintenance_fee_psf > 0) {
                    $maintenanceValues[] = $property->min_maintenance_fee_psf;
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

            // Extract buildup values for API response
            $minBuildup = $propertyRanges->min_buildup ?? 0;
            $maxBuildup = $propertyRanges->max_buildup ?? 0;

            // Generate text ranges
            $buildupText = null;
            if ($propertyRanges && $propertyRanges->min_buildup && $propertyRanges->max_buildup) {
                if ($propertyRanges->min_buildup == $propertyRanges->max_buildup) {
                    $buildupText = Helper::numberFormatV2($propertyRanges->min_buildup ?? 0, 2, true) . ' sqft';
                } else {
                    $buildupText = 'From ' . Helper::numberFormatV2($propertyRanges->min_buildup ?? 0, 2, true) . ' sqft - ' . Helper::numberFormatV2($propertyRanges->max_buildup ?? 0, 2, true) . ' sqft';
                }
            }

            $pricePsfText = null;
            if ($propertyRanges && $propertyRanges->min_price_psf && $propertyRanges->max_price_psf) {
                if ($propertyRanges->min_price_psf == $propertyRanges->max_price_psf) {
                    $pricePsfText = '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true);
                } else {
                    $pricePsfText = '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_psf ?? 0, 2, true);
                }
            }

            $priceUnitText = null;
            if ($propertyRanges && $propertyRanges->min_price_unit && $propertyRanges->max_price_unit) {
                if ($propertyRanges->min_price_unit == $propertyRanges->max_price_unit) {
                    $priceUnitText = '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true);
                } else {
                    $priceUnitText = '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_unit ?? 0, 2, true);
                }
            }

            $maintenanceText = null;
            if ($propertyRanges && $propertyRanges->min_maintenance && $propertyRanges->max_maintenance) {
                if ($propertyRanges->min_maintenance == $propertyRanges->max_maintenance) {
                    $maintenanceText = '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true);
                } else {
                    $maintenanceText = '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_maintenance ?? 0, 2, true);
                }
            }

            $contactPhone = Option::where( 'option_name', 'OFFICIAL_PHONE_NUMBER' )->value('option_value') ?? "";
            $contactWhatsapp = Option::where( 'option_name', 'WHATSAPP' )->value('option_value') ?? "";

            // Check if project is favorited by the authenticated user
            $isFavourite = false;
            if (auth('user')->check()) {
                $isFavourite = FavouriteProject::where('user_id', auth('user')->user()->id)
                    ->where('project_id', $project->id)
                    ->where('status', 10)
                    ->where('favourite_type', 1)
                    ->exists();
            }
    
            $projectData = [
                'id' => $project->id,
                'is_favourite' => $isFavourite,
                'project_tags' => $project->project_tags,

                // Additional required fields
                'galleries' => (function() use ($project) {
                    $galleries = $project->galleries()
                        ->orderBy('sequence')
                        ->get()
                        ->map(function ( $gallery ) {
                            return [
                                'id'       => $gallery->id,
                                'sequence' => $gallery->sequence ?? null,
                                'image_path'    => $gallery->image ? asset( 'storage/' . $gallery->image ) : null,
                                'remarks'  => $gallery->remarks ?? null,
                            ];
                        });

                    // If no galleries, return placeholder
                    if ($galleries->isEmpty()) {
                        return collect([[
                            'id' => 0,
                            'sequence' => 1,
                            'image_path' => asset( 'admin/images/placeholder.png' ) . Helper::assetVersion(),
                            'remarks' => null,
                        ]]);
                    }

                    return $galleries;
                })(),

                'logo' => $project->logo_path ?? null,
                'logo_path' => $project->logo_path ?? null,
                'min_property_price' => $minPropertyPrice,
                'max_property_price' => $maxPropertyPrice,
                'min_buildup' => $minBuildup,
                'max_buildup' => $maxBuildup,
                'min_rental_price' => $minRentalPrice,
                'max_rental_price' => $maxRentalPrice,

                // Property unit counts from all properties in this project
                'available_units' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('property_units.status', 10)
                    ->where('unit_type', 1)
                    ->count(),

                'for_sale_count' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('property_units.unit_type', 1)
                    ->where('property_units.status', 10)
                    ->count(),

                'for_rent_count' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->whereIn('property_units.unit_type', [2,3])
                    ->where('property_units.status', 10)
                    ->count(),

                'phone_number' => $contactPhone,
                'whatsapp_link' => Helper::whatsAppBaseLink($contactWhatsapp), 
                'translations' => $project->decoded_translations,
                'project_details' => $project->decoded_project_details,
                'property_type_label' => self::getPropertyTypeLabel($project->property_type),
                'project_type_label' => self::getPropertyTypeLabel($project->property_type),
                // 'tenure' => $project->tenure,
                'location' => [
                    'address_line_1' => $project->address_line_1,
                    'address_line_2' => $project->address_line_2,
                    'address_line_3' => $project->address_line_3,
                    'state' => $project->state,
                    'postcode' => $project->postcode,
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude,
                    'waze_url' => $project->waze_url ?? null,
                    'google_map_url' => $project->google_map_url ?? null,
                    'locations' => $project->project_locations_details,
                ],
                'amenities_list' => $project->amenities_details,

                // Floorplans section - Only floorplan data
                'floorplans_count' => $project->floorplans->count(),
                'floorplans' => $project->floorplans
                ->flatMap(function ($floorplan) use ($request) {
                    // Handle multiple properties per floorplan
                    $properties = $floorplan->properties;
                    $project = $floorplan->project;

                    // If no properties, return floorplan with default values
                    // if ($properties->isEmpty()) {
                    //     return [[
                    //         'property_id'      => 0,
                    //         'translations'     => [],
                    //         'sequence'         => $floorplan->sequence,
                    //         'image'            => $floorplan->image_path,
                    //         'image_path'       => $floorplan->image_path,
                    //         'galleries'        => [],
                    //         'property_tags'    => $project->project_tags ?? [],
                    //         'remarks'          => $floorplan->remarks,
                    //         'bedrooms'         => (int)($floorplan->bedrooms ?? 0),
                    //         'bathrooms'        => (int)($floorplan->bathrooms ?? 0),
                    //         'balcony'          => $floorplan->balcony ?? 0,
                    //         'storeroom'        => $floorplan->storeroom ?? 0,
                    //         'parking_spaces'   => $floorplan->parking_spaces ?? 0,
                    //         'category'         => $floorplan->category,
                    //         'document_link'    => $floorplan->upload_link,
                    //         'is_pet_friendly'  => ($project->is_pet_friendly == 1) ? 'Yes' : 'No',
                    //         'completion_date'  => $project->completion_date,
                    //         'units_left'       => 0,
                    //         'build_up_area_psf'    => Helper::numberFormatV2(0, 2, true),
                    //         'selling_price_psf'    => 'RM' . Helper::numberFormatV2(0, 2, true),
                    //         'selling_price_unit'   => 'RM' . Helper::numberFormatV2(0, 2, true),
                    //         'maintenance_fee_psf'  => 'RM' . Helper::numberFormatV2(0, 2, true),
                    //         'available_units'  => [],
                    //     ]];
                    // }

                    // For each property, create a floorplan entry
                    return $properties->map(function ($property) use ($floorplan, $request, $project) {

                        return [
                            'property_id'      => $property->id,
                            'translations'     => $property->decoded_translations ?? [],
                            'sequence'         => $floorplan->sequence,
                            'image'            => $floorplan->image_path,
                            'image_path'       => $floorplan->image_path,

                            'galleries' => (function() use ($property) {
                                $galleries = $property->galleries()
                                    ->orderBy('sequence')
                                    ->get()
                                    ->map(function ($gallery) {
                                        return [
                                            'id'          => $gallery->id,
                                            'sequence'    => $gallery->sequence ?? null,
                                            'image_path'  => $gallery->image ? asset('storage/' . $gallery->image) : null,
                                            'remarks'     => $gallery->remarks ?? null,
                                        ];
                                    });

                                // If no galleries, return placeholder
                                if ($galleries->isEmpty()) {
                                    return collect([[
                                        'id' => 0,
                                        'sequence' => 1,
                                        'image_path' => asset('admin/images/placeholder.png') . Helper::assetVersion(),
                                        'remarks' => null,
                                    ]]);
                                }

                                return $galleries;
                            })(),

                            'property_tags'    => $project->project_tags ?? [],
                            'remarks'          => $floorplan->remarks,
                            'bedrooms'         => (int)($property->bedroom_text ?? 0),
                            'bathrooms'        => (int)($property->bathroom_text ?? 0),
                            'balcony'          => $property->balcony ?? 0,
                            'storeroom'        => $property->storeroom ?? 0,
                            'parking_spaces'   => $property->parking_spaces ?? 0,
                            'category'         => $floorplan->category,
                            'document_link'    => $floorplan->upload_link,
                            'is_pet_friendly'  => ($project->is_pet_friendly == 1) ? 'Yes' : 'No',
                            'completion_date'  => $project->completion_date,

                            'units_left'       => self::getUnitsCountByType($property, $request->unit_type, 'available'),

                            // pricing etc
                            'build_up_area_psf'    => Helper::numberFormatV2($property->build_up_area_psf ?? 0, 2, true),
                            'selling_price_psf'    => 'RM' . Helper::numberFormatV2($property->selling_price_psf ?? 0, 2, true),
                            'selling_price_unit'   => 'RM' . Helper::numberFormatV2($property->selling_price_unit ?? 0, 2, true),
                            'maintenance_fee_psf'  => 'RM' . Helper::numberFormatV2($property->maintenance_fee_psf ?? 0, 2, true),

                            // Property prices
                            'prices' => (function() use ($property) {
                                $prices = [];

                                // Add default MYR from property's own fields
                                $prices['myr'] = [
                                    'build_up_area_psf' => Helper::numberFormatV2($property->build_up_area_psf ?? 0, 2, true),
                                    'selling_price_psf' => Helper::numberFormatV2($property->selling_price_psf ?? 0, 2, true),
                                    'selling_price_unit' => Helper::numberFormatV2($property->selling_price_unit ?? 0, 2, true),
                                    'maintenance_fee_psf' => Helper::numberFormatV2($property->maintenance_fee_psf ?? 0, 2, true),
                                ];

                                // Add additional currencies from PropertyPrice if available
                                if ($property->prices && $property->prices->isNotEmpty()) {
                                    foreach ($property->prices as $price) {
                                        $currencyCode = strtolower($price->currency_code);
                                        $prices[$currencyCode] = [
                                            'build_up_area_psf' => Helper::numberFormatV2($price->build_up_area_psf, 2, true),
                                            'selling_price_psf' => Helper::numberFormatV2($price->selling_price_psf, 2, true),
                                            'selling_price_unit' => Helper::numberFormatV2($price->selling_price_unit, 2, true),
                                            'maintenance_fee_psf' => Helper::numberFormatV2($price->maintenance_fee_psf, 2, true),
                                        ];
                                    }
                                }

                                return $prices;
                            })(),

                            'available_units' => self::getUnitsByType($property, $request->unit_type, 'available')
                                ->orderBy('floor', 'asc')
                                ->orderBy('unit_number', 'asc')
                                ->get()
                                ->map(function ($unit) use ($property, $project) {
                                    return [
                                        'id'                 => $unit->id,
                                        'property_id'        => $unit->property_id,
                                        'thumbnail'          => $property->thumbnail,
                                        'thumbnail_path'     => $property->thumbnail_path,
                                        'translations'       => $property->decoded_translations ?? $project->decoded_translations ?? [],
                                        'property_name'      => $property->name,
                                        'unit_number'        => $unit->unit_number,
                                        'block_number'       => $unit->block_name,
                                        'unit_name'          => $unit->unit_name,
                                        'floor'              => $unit->floor,
                                        'display_floor'              => $unit->display_floor,
                                        'bedrooms'           => $unit->bedrooms,
                                        'bathrooms'          => $unit->bathrooms,
                                        'balcony'            => $unit->balcony,
                                        'storeroom'          => $unit->storeroom,
                                        'parking_spaces'     => $unit->parking_spaces,
                                        'built_up_area'      => $unit->built_up_area,
                                        'selling_price'      => Helper::numberFormatV2($unit->selling_price, 2, true),
                                        'rental_price'       => Helper::numberFormatV2($unit->rental_price, 2, true),
                                        'maintenance_fee'    => Helper::numberFormatV2($unit->maintenance_fee, 2, true),
                                        'unit_status'        => $unit->unit_status,
                                        'unit_status_label'  => $unit->unit_status_label,
                                        'unit_type'          => $unit->unit_type,
                                        'unit_type_label'    => $unit->unit_type_label,
                                        'availability_date'  => $unit->availability_date,
                                        'remarks'            => $unit->remarks,
                                        'created_at'         => $unit->created_at,
                                        'updated_at'         => $unit->updated_at,
                                    ];
                                }),
                        ];
                    });
            })->values(),


                // Properties section - All properties with their details
                'properties_count' => \App\Models\Property::where('project_id', $project->id)
                    ->where('status', 10)
                    ->count(),
                'properties' => \App\Models\Property::where('project_id', $project->id)
                    ->where('status', 10)
                    ->with(['units', 'galleries', 'prices'])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($property) use ($request, $project) {
                        return [
                            'id'               => $property->id,
                            'floorplan_id'     => $property->project_floorplan_id,
                            'thumbnail'     => $property->thumbnail,
                            'thumbnail_path'     => $property->thumbnail_path,
                            'translations'     => $property->decoded_translations ?? [],
                            'galleries'        => (function() use ($property) {
                                $galleries = $property->galleries()
                                    ->orderBy('sequence')
                                    ->get()
                                    ->map(function ($gallery) {
                                        return [
                                            'id'          => $gallery->id,
                                            'sequence'    => $gallery->sequence ?? null,
                                            'image_path'  => $gallery->image ? asset('storage/' . $gallery->image) : null,
                                            'remarks'     => $gallery->remarks ?? null,
                                        ];
                                    });

                                // If no galleries, return placeholder
                                if ($galleries->isEmpty()) {
                                    return collect([[
                                        'id' => 0,
                                        'sequence' => 1,
                                        'image_path' => asset('admin/images/placeholder.png') . Helper::assetVersion(),
                                        'remarks' => null,
                                    ]]);
                                }

                                return $galleries;
                            })(),
                            'property_tags'    => $project->project_tags ?? [],
                            'bedrooms'         => (int)($property->bedroom_text ?? 0),
                            'bathrooms'        => (int)($property->bathroom_text ?? 0),
                            'balcony'          => $property->balcony ?? 0,
                            'storeroom'        => $property->storeroom ?? 0,
                            'parking_spaces'   => $property->parking_spaces ?? 0,
                            'is_pet_friendly'  => ($project->is_pet_friendly == 1) ? 'Yes' : 'No',
                            'completion_date'  => $project->completion_date,
                            'units_left'       => self::getUnitsCountByType($property, $request->unit_type, 'available'),
                            'build_up_area_psf'    => Helper::numberFormatV2($property->build_up_area_psf ?? 0, 2, true),
                            'selling_price_psf'    => 'RM' . Helper::numberFormatV2($property->selling_price_psf ?? 0, 2, true),
                            'selling_price_unit'   => 'RM' . Helper::numberFormatV2($property->selling_price_unit ?? 0, 2, true),
                            'maintenance_fee_psf'  => 'RM' . Helper::numberFormatV2($property->maintenance_fee_psf ?? 0, 2, true),

                            // Property prices
                            'prices' => (function() use ($property) {
                                $prices = [];

                                // Add default MYR from property's own fields
                                $prices['myr'] = [
                                    'build_up_area_psf' => Helper::numberFormatV2($property->build_up_area_psf ?? 0, 2, true),
                                    'selling_price_psf' => Helper::numberFormatV2($property->selling_price_psf ?? 0, 2, true),
                                    'selling_price_unit' => Helper::numberFormatV2($property->selling_price_unit ?? 0, 2, true),
                                    'maintenance_fee_psf' => Helper::numberFormatV2($property->maintenance_fee_psf ?? 0, 2, true),
                                ];

                                // Add additional currencies from PropertyPrice if available
                                if ($property->prices && $property->prices->isNotEmpty()) {
                                    foreach ($property->prices as $price) {
                                        $currencyCode = strtolower($price->currency_code);
                                        $prices[$currencyCode] = [
                                            'build_up_area_psf' => Helper::numberFormatV2($price->build_up_area_psf, 2, true),
                                            'selling_price_psf' => Helper::numberFormatV2($price->selling_price_psf, 2, true),
                                            'selling_price_unit' => Helper::numberFormatV2($price->selling_price_unit, 2, true),
                                            'maintenance_fee_psf' => Helper::numberFormatV2($price->maintenance_fee_psf, 2, true),
                                        ];
                                    }
                                }

                                return $prices;
                            })(),

                            'available_units'  => self::getUnitsByType($property, $request->unit_type, 'available')
                                ->orderBy('floor', 'asc')
                                ->orderBy('unit_number', 'asc')
                                ->get()
                                ->map(function ($unit) use ($property, $project) {
                                    return [
                                        'id'                 => $unit->id,
                                        'property_id'        => $unit->property_id,
                                        'translations'       => $property->decoded_translations ?? $project->decoded_translations ?? [],
                                        'property_name'      => $property->name,
                                        'unit_number'        => $unit->unit_number,
                                        'block_number'       => $unit->block_name,
                                        'unit_name'          => $unit->unit_name,
                                        'floor'              => $unit->floor,
                                        'display_floor'              => $unit->display_floor,
                                        'bedrooms'           => $unit->bedrooms,
                                        'bathrooms'          => $unit->bathrooms,
                                        'balcony'            => $unit->balcony,
                                        'storeroom'          => $unit->storeroom,
                                        'parking_spaces'     => $unit->parking_spaces,
                                        'built_up_area'      => $unit->built_up_area,
                                        'selling_price'      => Helper::numberFormatV2($unit->selling_price, 2, true),
                                        'rental_price'       => Helper::numberFormatV2($unit->rental_price, 2, true),
                                        'maintenance_fee'    => Helper::numberFormatV2($unit->maintenance_fee, 2, true),
                                        'unit_status'        => $unit->unit_status,
                                        'unit_status_label'  => $unit->unit_status_label,
                                        'unit_type'          => $unit->unit_type,
                                        'unit_type_label'    => $unit->unit_type_label,
                                        'availability_date'  => $unit->availability_date,
                                        'remarks'            => $unit->remarks,
                                        'created_at'         => $unit->created_at,
                                        'updated_at'         => $unit->updated_at,
                                    ];
                                }),
                        ];
                    })->values(),


                'build_up_area_psf' => $project->build_up_area_psf,
                'selling_price_psf' => $project->selling_price_psf,
                'selling_price_unit' => $project->selling_price_unit,
                'maintenance_fee_psf' => $project->maintenance_fee_psf,
                'build_up_area_psf_text' => $buildupText,
                'selling_price_psf_text' => $pricePsfText,
                'selling_price_unit_text' => $priceUnitText,
                'maintenance_fee_psf_text' => $maintenanceText,
                'bedroom_text' => $project->bedroom_text,
                'bathroom_text' => $project->bathroom_text,
                'building_type' => $project->building_type,
                'furnishing_status' => $project->furnishing_status,
                'furnishing_status_label' => self::getFurnishingStatusLabel($project->furnishing_status),
                'build_up_area_psf' => $project->build_up_area_psf,
                'selling_price_psf' => $project->selling_price_psf,
                'selling_price_unit' => $project->selling_price_unit,
                'maintenance_fee_psf' => $project->maintenance_fee_psf,
                'completion_date' => $project->completion_date,
                'project_status' => $project->project_status,
                'project_status_label' => self::getProjectStatusLabel($project->project_status),
                'is_pet_friendly' => $project->is_pet_friendly,
                'sequence' => $project->sequence,
                'developers' => $project->developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                    ];
                }),
                'country' => $project->country ? $project->country->country_name : null,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ];

            // Create search history if user is authenticated

            if (auth('user')->check()) {
                try {
                    \App\Models\SearchHistory::create([
                        'user_id' => auth('user')->user()->id,
                        'search_type' => 'for_sale',
                        // 'search_query' => null,
                        // 'search_filters' => null,
                        // 'results_count' => 1,
                        // 'ip_address' => request()->ip(),
                        // 'user_agent' => request()->userAgent(),
                        'status' => 10,
                        'project_id' => $project->id,
                        'property_id' => null,
                        'property_unit_id' => null,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the API response
                    \Log::error('Failed to create search history: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $projectData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching project details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get property type label
     */
    public static function getPropertyTypeLabel($type)
    {
        switch ($type) {
            case 1:
                return 'Residential';
            case 2:
                return 'Commercial';
            case 3:
                return 'Industrial';
            default:
                return null;
        }
    }

    /**
     * Helper method to get project status label
     */
    public static function getProjectStatusLabel($status)
    {
        switch ($status) {
            case 1:
                return 'Completed';
            case 2:
                return 'Under Construction';
            default:
                return null;
        }
    }

    /**
     * Helper method to get furnishing status label
     */
    public static function getFurnishingStatusLabel($status)
    {
        switch ($status) {
            case 1:
                return 'Fully Furnished';
            case 2:
                return 'Partially Furnished';
            case 3:
                return 'Bare Unit';
            default:
                return null;
        }
    }

    public static function autocompleteProjectsApi($request)
    {
        $rules = [
            'q' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:20',
            'project_locations' => 'nullable|array',
            'project_locations.*' => 'integer|exists:property_locations,id',
            'property_type' => 'nullable|array',
            'property_type.*' => 'integer|in:1,2,3',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('q');
            $limit = min($request->input('limit', 10), 20);

            $projects = Project::query()
                ->where('status', 10)
                ->where(function ($q) use ($query) {
                    $q->whereRaw("MATCH(searchable_titles) AGAINST(? IN BOOLEAN MODE)", [ $query ])
                    ->orWhereRaw('LOWER(searchable_titles) LIKE ?', [ '%' . strtolower($query) . '%' ]);
                });

            // Apply location filter
            if ($request->filled('project_locations')) {
                $locationIds = [];
                
                // Handle both array and string (comma-separated) formats for backward compatibility
                if (is_array($request->project_locations)) {
                    $locationIds = $request->project_locations;
                } else {
                    // Legacy string format support
                    $locationIds = array_map('trim', explode(',', $request->project_locations));
                    $locationIds = array_filter($locationIds, 'is_numeric');
                }

                if (!empty($locationIds)) {
                    $projects->where(function ($q) use ($locationIds) {
                        foreach ($locationIds as $locationId) {
                            // two JSON forms:
                            //  - numeric JSON value: 44
                            //  - string JSON value: "44"
                            $asNumberJson = $locationId;                     // e.g. 44  (valid JSON literal)
                            $asStringJson = json_encode((string) $locationId); // e.g. "44"
                            $like = '%"'.$locationId.'"%';                   // fallback match inside quoted JSON
                
                            $q->orWhereRaw("
                                (
                                  ( JSON_VALID(project_locations) AND ( JSON_CONTAINS(project_locations, ?) OR JSON_CONTAINS(project_locations, ?) ) )
                                  OR
                                  ( JSON_VALID(JSON_UNQUOTE(project_locations)) AND ( JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?) OR JSON_CONTAINS(JSON_UNQUOTE(project_locations), ?) ) )
                                  OR
                                  ( project_locations LIKE ? )
                                )
                            ", [
                                $asNumberJson, $asStringJson,    // checks when column is valid JSON
                                $asNumberJson, $asStringJson,    // checks when column is a quoted JSON string (double-encoded)
                                $like                             // fallback: match the quoted id inside the stored text
                            ]);
                        }
                    });
                }
                
            }            

            // Apply property type filter
            if ($request->filled('property_type')) {
                $propertyTypes = $request->property_type;
                $projects->whereIn('property_type', $propertyTypes);
            }

            $results = $projects->select([
                    'id',
                    'title',
                    'area',
                    'state',
                    'logo',
                    'property_type',
                    'tenure',
                    'project_locations',
                    'translations'
                ])
                ->orderByRaw("
                    CASE
                        WHEN LOWER(title) LIKE ? THEN 1
                        WHEN LOWER(title) LIKE ? THEN 2
                        ELSE 3
                    END
                ", [ strtolower($query) . '%', '%' . strtolower($query) . '%' ])
                ->orderBy('title')

                ->limit($limit)
                ->get()
                ->map(function ($project) {
                    $translations = $project->decoded_translations;
                    $translatedTitle = $translations && isset($translations->en->title) ? $translations->en->title : $project->title;

                    return [
                        'id' => $project->id,
                        'title' => $translatedTitle,
                        'location' => trim($project->area . ', ' . $project->state, ', '),
                        'logo_url' => $project->logo_path,
                        'property_type' => $project->property_type,
                        'property_type_label' => $project->getPropertyTypeLabelAttribute(),
                        'tenure' => $project->tenure,
                        'tenure_label' => $project->getTenureTypeLabelAttribute(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total_results' => $results->count(),
                    'limit' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch autocomplete suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public static function removeProjectGalleryImage( $request ) {

        $updateProject = Project::with( ['galleries'] )->find( Helper::decode($request->id) );
        
        switch ($request->scope) {
            case 'image':
                Storage::delete( 'public/' . $updateProject->image );
                $updateProject->image = null;
                if($updateProject->voucher){
                    $updateProject->voucher->image = null;
                    $updateProject->voucher->save();
                }
                break;

            case 'unclaimed_image':
                Storage::delete( 'public/' . $updateProject->unclaimed_image );
                $updateProject->unclaimed_image = null;
                break;

            case 'claiming_image':
                Storage::delete( 'public/' . $updateProject->claiming_image );
                $updateProject->claiming_image = null;
                break;

            case 'claimed_image':
                Storage::delete( 'public/' . $updateProject->claimed_image );
                $updateProject->claimed_image = null;
                break;
            
            default:
                # code...
                break;
        }

        $updateProject->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'property.image' ) ) ] ),
        ] );
    }

    private static function handleGalleryUpload($request, $projectId)
    {
        $images = $request->file('new_images');
        $remarks = $request->get('remarks', []);
        
        // Get the current max sequence for this project
        $maxSequence = ProjectGallery::where('project_id', $projectId)
            ->max('sequence') ?? 0;

        foreach ($images as $index => $image) {
            if ($image && $image->isValid()) {
                // Generate unique filename
                $filename = time() . '_' . $projectId . '_' . $index . '.' . $image->getClientOriginalExtension();
                
                // Store image in storage/app/public/properties/{project_id}/
                $imagePath = $image->storeAs(
                    'properties/' . $projectId, 
                    $filename, 
                    'public'
                );

                // Create gallery record
                ProjectGallery::create([
                    'project_id' => $projectId,
                    'sequence' => $maxSequence + $index + 1,
                    'image' => $imagePath,
                    'remarks' => $remarks[$index] ?? null,
                    'status' => 10
                ]);
            }
        }
    }

    /**
     * Handle existing images updates
     */
    private static function handleExistingImages($existingImages, $projectId)
    {
       
        foreach ($existingImages as $imageData) {
            $gallery = ProjectGallery::where('id', $imageData['id'])
                ->where('project_id', $projectId)
                ->first();
                
            if ($gallery) {
                // Check if image should be deleted
                if (isset($imageData['delete']) && $imageData['delete']) {
                    // Delete physical file
                    if (Storage::disk('public')->exists($gallery->image)) {
                        Storage::disk('public')->delete($gallery->image);
                    }
                    $gallery->delete();
                } else {
                    // Update image data
                    $gallery->update([
                        'remarks' => $imageData['remarks'] ?? $gallery->remarks,
                        'sequence' => $imageData['sequence'] ?? $gallery->sequence,
                    ]);
                }
            }
        }
    }

    private static function handleGalleryImagesCreate($request, $projectId)
    {
        if ($request->has('new_images') && is_array($request->new_images)) {
            $remarks = $request->get('new_images_remarks', []);

            // Get the current max sequence for this project
            $maxSequence = ProjectGallery::where('project_id', $projectId)
                ->max('sequence') ?? 0;

            foreach ($request->new_images as $index => $image) {
                if ($image && $image->isValid()) {
                    // Generate unique filename
                    $filename = time() . '_' . $projectId . '_' . $index . '.' . $image->getClientOriginalExtension();

                    // Store image in storage/app/public/properties/{project_id}/
                    $imagePath = $image->storeAs(
                        'properties/' . $projectId,
                        $filename,
                        'public'
                    );

                    // Create gallery record
                    ProjectGallery::create([
                        'project_id' => $projectId,
                        'image' => $imagePath,
                        'remarks' => $remarks[$index] ?? '',
                        'sequence' => $maxSequence + $index + 1,
                        'status' => 1
                    ]);
                }
            }
        }
    }

    private static function handleGalleryImagesUpdate($request, $projectId)
    {
        // Handle new images if any
        if ($request->has('new_images') && is_array($request->new_images)) {
            self::handleGalleryImagesCreate($request, $projectId);
        }

        // Handle existing image updates if any
        if ($request->has('existing_images') && $request->existing_images != [] && $request->existing_images != '[]') {
            self::handleExistingImages(json_decode($request->existing_images, true), $projectId);
        }

        // Handle deleted images if provided in separate parameter
        if ($request->has('deleted_images') && $request->deleted_images != [] && $request->deleted_images != '[]') {
            $deletedImageIds = json_decode($request->deleted_images, true);
            if (is_array($deletedImageIds)) {
                foreach ($deletedImageIds as $imageId) {
                    $gallery = ProjectGallery::where('id', $imageId)
                        ->where('project_id', $projectId)
                        ->first();

                    if ($gallery) {
                        // Delete physical file
                        if (Storage::disk('public')->exists($gallery->image)) {
                            Storage::disk('public')->delete($gallery->image);
                        }
                        $gallery->delete();
                    }
                }
            }
        }

        // Handle image ordering
        if ($request->has('images_order') && $request->images_order != [] && $request->images_order != '[]') {
            $imageOrder = json_decode($request->images_order, true);

            if (is_array($imageOrder)) {
                foreach ($imageOrder as $sequence => $imageId) {
                    $updated = ProjectGallery::where('id', $imageId)->first(); // sequence starts from 1

                    if ($updated) {
                        $updated->sequence = $sequence + 1;
                        $updated->save();
                    } else {
                        \Log::warning("Image ID {$imageId} not found when updating sequence");
                    }
                }
            }
        }

        // Handle primary image ID (for future implementation)
        if ($request->has('primary_image_id') && $request->primary_image_id) {
            // Note: Primary image functionality would need a database field
            // This could be implemented by adding a thumbnail field to projects table
            // or an is_primary field to projects_galleries table
            \Log::info('Primary image ID received: ' . $request->primary_image_id);
        }

        DB::commit();
    }

    public static function deleteGalleryImage($id)
    {
        $gallery = ProjectGallery::findOrFail($id);
        
        // Delete physical file
        if (Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }
        
        $gallery->delete();
        
        return true;
    }

    /**
     * Update gallery images order
     */
    public static function updateGalleryOrder($request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:projects_galleries,id',
            'images.*.sequence' => 'required|integer'
        ]);

        foreach ($request->images as $imageData) {
            ProjectGallery::where('id', $imageData['id'])
                ->update(['sequence' => $imageData['sequence']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gallery order updated successfully'
        ]);
    }

    public static function getGallery($request)
    {
        $galleries = ProjectGallery::where('project_id', helper::decode($request->project_id))
            ->where('status', 1)
            ->orderBy('sequence')
            ->get()
            ->map(function($gallery) {
                return [
                    'id' => $gallery->id,
                    'image_url' => $gallery->image_path,
                    'image_path' => $gallery->image,
                    'remarks' => $gallery->remarks,
                    'sequence' => $gallery->sequence,
                ];
            });

        return response()->json($galleries);
    }

    /**
     * Get units query by type using existing Property relationships
     *
     * @param Property $property
     * @param int|null $unitType
     * @param string|null $unitStatus Filter by unit_status (e.g., 'available')
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function getUnitsByType($property, $unitType = null, $unitStatus = null)
    {

        switch($unitType) {
            case 1:
                $query = $property->forSaleUnits();
                break;
            case 2:
                $query = $property->longRentalUnits();
                break;
            case 3:
                $query = $property->shortRentalUnits();
                break;
            default:
                $query = $property->forSaleUnits(); // Default units relationship (includes unit_type 4 and null)
                break;
        }

        return $query;
    }

    /**
     * Get units count by type using existing Property relationships
     *
     * @param Property $property
     * @param int|null $unitType
     * @param string|null $unitStatus Filter by unit_status (e.g., 'available')
     * @return int
     */
    public static function getUnitsCountByType($property, $unitType = null, $unitStatus = null)
    {
        return self::getUnitsByType($property, $unitType, $unitStatus)->count();
    }

    /**
     * Send contact form email to admin
     *
     * @param Request $request
     * @return JsonResponse
     */
    public static function sendContactFormApi($request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $name = $request->name;
            $email = $request->email;
            $subject = $request->subject;
            $message = $request->message;

            // Prepare HTML email content
            $htmlContent = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #d9ae80; color: white; padding: 20px; text-align: center; }
                        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                        .field { margin-bottom: 15px; }
                        .label { font-weight: bold; color: #555; }
                        .value { margin-top: 5px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Contact Form Submission</h2>
                        </div>
                        <div class='content'>
                            <div class='field'>
                                <div class='label'>Name:</div>
                                <div class='value'>{$name}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Email:</div>
                                <div class='value'>{$email}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Subject:</div>
                                <div class='value'>{$subject}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Message:</div>
                                <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                            </div>
                        </div>
                        <div class='footer'>
                            <p>This email was sent from the Xpark contact form.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Send email to admin using Brevo
            $adminEmail = 'admin@xparkcreation.com';
            $adminName = 'Xpark Admin';
            $emailSubject = "Contact Form: {$subject}";

            $result = Helper::sendBrevoEmail(
                $adminEmail,
                $adminName,
                $emailSubject,
                $htmlContent
            );
            
            if ($result && isset($result['messageId'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contact form submitted successfully. We will get back to you soon.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again later.',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the contact form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public static function oneProjectApiV2($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:projects,id',
            'unit_type' => 'nullable|integer|in:1,2,3,4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'errors' => $validator->errors()
            ], 404);
        }

        try {
            $project = Project::with(['developers', 'country', 'floorplans'])
                ->where('id', $request->id)
                ->where('status', 10)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or inactive'
                ], 404);
            }

            $newTitle = $project->title;

            if( $project->translations ){
                $newTitle = $project->decoded_translations ? $project->decoded_translations->en->title : $project->title;
            }

            // Calculate min/max property prices for this project
            $propertyPrices = \DB::table('properties')
                ->where('project_id', $project->id)
                ->where('status', 10)
                ->whereNotNull('selling_price_unit')
                ->where('selling_price_unit', '>', 0)
                ->selectRaw('MIN(COALESCE(min_selling_price_unit, selling_price_unit)) as min_price, MAX(selling_price_unit) as max_price')
                ->first();

            $minPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->min_price,2, true) : Helper::numberFormatV2( 0, 2, true );
            $maxPropertyPrice = $propertyPrices ? Helper::numberFormatV2( $propertyPrices->max_price,2, true) : Helper::numberFormatV2( 0, 2, true );

            // Calculate min/max rental prices from property units
            $rentalPrices = \DB::table('property_units')
                ->join('properties', 'property_units.property_id', '=', 'properties.id')
                ->where('properties.project_id', $project->id)
                ->where('properties.status', 10)
                ->where('property_units.status', 10)
                ->whereNotNull('property_units.rental_price')
                ->where('property_units.rental_price', '>', 0)
                ->selectRaw('MIN(property_units.rental_price) as min_rental, MAX(property_units.rental_price) as max_rental')
                ->first();

            $minRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->min_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );
            $maxRentalPrice = $rentalPrices ? Helper::numberFormatV2($rentalPrices->max_rental, 2, true) : Helper::numberFormatV2( 0, 2, true );

            // Calculate min/max values from block JSON data of properties under this project
            $properties = \DB::table('properties')
                ->where('project_id', $project->id)
                ->where('status', 10)
                ->get([
                    'build_up_area_psf', 'min_build_up_area_psf',
                    'selling_price_psf', 'min_selling_price_psf',
                    'selling_price_unit', 'min_selling_price_unit',
                    'maintenance_fee_psf', 'min_maintenance_fee_psf'
                ]);

            $buildupValues = [];
            $pricePsfValues = [];
            $priceUnitValues = [];
            $maintenanceValues = [];

            foreach ($properties as $property) {
                // Collect build up area values (both min and max)
                if ($property->build_up_area_psf > 0) {
                    $buildupValues[] = $property->build_up_area_psf;
                }
                if ($property->min_build_up_area_psf > 0) {
                    $buildupValues[] = $property->min_build_up_area_psf;
                }

                // Collect selling price PSF values (both min and max)
                if ($property->selling_price_psf > 0) {
                    $pricePsfValues[] = $property->selling_price_psf;
                }
                if ($property->min_selling_price_psf > 0) {
                    $pricePsfValues[] = $property->min_selling_price_psf;
                }

                // Collect selling price unit values (both min and max)
                if ($property->selling_price_unit > 0) {
                    $priceUnitValues[] = $property->selling_price_unit;
                }
                if ($property->min_selling_price_unit > 0) {
                    $priceUnitValues[] = $property->min_selling_price_unit;
                }

                // Collect maintenance fee values (both min and max)
                if ($property->maintenance_fee_psf > 0) {
                    $maintenanceValues[] = $property->maintenance_fee_psf;
                }
                if ($property->min_maintenance_fee_psf > 0) {
                    $maintenanceValues[] = $property->min_maintenance_fee_psf;
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

            // Extract buildup values for API response
            $minBuildup = $propertyRanges->min_buildup ?? 0;
            $maxBuildup = $propertyRanges->max_buildup ?? 0;

            // Generate text ranges
            $buildupText = null;
            if ($propertyRanges && $propertyRanges->min_buildup && $propertyRanges->max_buildup) {
                if ($propertyRanges->min_buildup == $propertyRanges->max_buildup) {
                    $buildupText = Helper::numberFormatV2($propertyRanges->min_buildup ?? 0, 2, true) . ' sqft';
                } else {
                    $buildupText = 'From ' . Helper::numberFormatV2($propertyRanges->min_buildup ?? 0, 2, true) . ' sqft - ' . Helper::numberFormatV2($propertyRanges->max_buildup ?? 0, 2, true) . ' sqft';
                }
            }

            $pricePsfText = null;
            if ($propertyRanges && $propertyRanges->min_price_psf && $propertyRanges->max_price_psf) {
                if ($propertyRanges->min_price_psf == $propertyRanges->max_price_psf) {
                    $pricePsfText = '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true);
                } else {
                    $pricePsfText = '' . Helper::numberFormatV2($propertyRanges->min_price_psf ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_psf ?? 0, 2, true);
                }
            }

            $priceUnitText = null;
            if ($propertyRanges && $propertyRanges->min_price_unit && $propertyRanges->max_price_unit) {
                if ($propertyRanges->min_price_unit == $propertyRanges->max_price_unit) {
                    $priceUnitText = '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true);
                } else {
                    $priceUnitText = '' . Helper::numberFormatV2($propertyRanges->min_price_unit ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_price_unit ?? 0, 2, true);
                }
            }

            $maintenanceText = null;
            if ($propertyRanges && $propertyRanges->min_maintenance && $propertyRanges->max_maintenance) {
                if ($propertyRanges->min_maintenance == $propertyRanges->max_maintenance) {
                    $maintenanceText = '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true);
                } else {
                    $maintenanceText = '' . Helper::numberFormatV2($propertyRanges->min_maintenance ?? 0, 2, true) . ' - ' . Helper::numberFormatV2($propertyRanges->max_maintenance ?? 0, 2, true);
                }
            }

            $contactPhone = Option::where( 'option_name', 'OFFICIAL_PHONE_NUMBER' )->value('option_value') ?? "";
            $contactWhatsapp = Option::where( 'option_name', 'WHATSAPP' )->value('option_value') ?? "";

            // Check if project is favorited by the authenticated user
            $isFavourite = false;
            if (auth('user')->check()) {
                $isFavourite = FavouriteProject::where('user_id', auth('user')->user()->id)
                    ->where('project_id', $project->id)
                    ->where('status', 10)
                    ->where('favourite_type', 1)
                    ->exists();
            }
    
            $projectData = [
                'id' => $project->id,
                'is_favourite' => $isFavourite,
                'project_tags' => $project->project_tags,

                // Additional required fields
                'galleries' => (function() use ($project) {
                    $galleries = $project->galleries()
                        ->orderBy('sequence')
                        ->get()
                        ->map(function ( $gallery ) {
                            return [
                                'id'       => $gallery->id,
                                'sequence' => $gallery->sequence ?? null,
                                'image_path'    => $gallery->image ? asset( 'storage/' . $gallery->image ) : null,
                                'remarks'  => $gallery->remarks ?? null,
                            ];
                        });

                    // If no galleries, return placeholder
                    if ($galleries->isEmpty()) {
                        return collect([[
                            'id' => 0,
                            'sequence' => 1,
                            'image_path' => asset( 'admin/images/placeholder.png' ) . Helper::assetVersion(),
                            'remarks' => null,
                        ]]);
                    }

                    return $galleries;
                })(),

                'logo' => $project->logo_path ?? null,
                'logo_path' => $project->logo_path ?? null,
                'min_property_price' => $minPropertyPrice,
                'max_property_price' => $maxPropertyPrice,
                'min_buildup' => $minBuildup,
                'max_buildup' => $maxBuildup,
                'min_rental_price' => $minRentalPrice,
                'max_rental_price' => $maxRentalPrice,

                // Property unit counts from all properties in this project
                'available_units_count' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('property_units.status', 10)
                    ->where('unit_type', 1)
                    ->count(),

                    
                'available_units' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('property_units.status', 10)
                    ->where('unit_type', 1)
                    ->count(),

                'for_sale_count' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->where('property_units.unit_type', 1)
                    ->where('property_units.status', 10)
                    ->count(),

                'for_rent_count' => \DB::table('property_units')
                    ->join('properties', 'property_units.property_id', '=', 'properties.id')
                    ->where('properties.project_id', $project->id)
                    ->whereIn('property_units.unit_type', [2,3])
                    ->where('property_units.status', 10)
                    ->count(),

                'phone_number' => $contactPhone,
                'whatsapp_link' => Helper::whatsAppBaseLink($contactWhatsapp), 
                'translations' => $project->decoded_translations,
                'project_details' => $project->decoded_project_details,
                'property_type_label' => self::getPropertyTypeLabel($project->property_type),
                'project_type_label' => self::getPropertyTypeLabel($project->property_type),
                // 'tenure' => $project->tenure,
                'location' => [
                    'address_line_1' => $project->address_line_1,
                    'address_line_2' => $project->address_line_2,
                    'address_line_3' => $project->address_line_3,
                    'state' => $project->state,
                    'postcode' => $project->postcode,
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude,
                    'waze_url' => $project->waze_url ?? null,
                    'google_map_url' => $project->google_map_url ?? null,
                    'locations' => $project->project_locations_details,
                ],
                'amenities_list' => $project->amenities_details,

                // Floorplans section - Only floorplan data
                'floorplans_count' => $project->floorplans->count(),
                'floorplans' => $project->floorplans->map(function ($floorplan) {
                    return [
                        'id'              => $floorplan->id,
                        'sequence'        => $floorplan->sequence,
                        'image'           => $floorplan->image_path,
                        'image_path'      => $floorplan->image_path,
                        'remarks'         => $floorplan->remarks,
                        'bedrooms'        => $floorplan->bedrooms,
                        'bathrooms'       => $floorplan->bathrooms,
                        'balcony'         => $floorplan->balcony,
                        'storeroom'       => $floorplan->storeroom,
                        'parking_spaces'  => $floorplan->parking_spaces,
                        'category'        => $floorplan->category,
                        'document_link'   => $floorplan->upload_link,
                        'properties_count' => $floorplan->properties->count(),
                    ];
                })->values(),

                // Properties section - All properties with their details
                'properties_count' => \App\Models\Property::where('project_id', $project->id)
                    ->where('status', 10)
                    ->count(),
                'properties' => \App\Models\Property::where('project_id', $project->id)
                    ->where('status', 10)
                    ->with(['units', 'galleries', 'prices'])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($property) use ($request, $project) {
                        return [
                            'id'               => $property->id,
                            'floorplan_id'     => $property->project_floorplan_id,
                            'thumbnail'     => $property->thumbnail,
                            'thumbnail_path'     => $property->thumbnail_path,
                            'translations'     => $property->decoded_translations ?? [],
                            'galleries'        => (function() use ($property) {
                                $galleries = $property->galleries()
                                    ->orderBy('sequence')
                                    ->get()
                                    ->map(function ($gallery) {
                                        return [
                                            'id'          => $gallery->id,
                                            'sequence'    => $gallery->sequence ?? null,
                                            'image_path'  => $gallery->image ? asset('storage/' . $gallery->image) : null,
                                            'remarks'     => $gallery->remarks ?? null,
                                        ];
                                    });

                                // If no galleries, return placeholder
                                if ($galleries->isEmpty()) {
                                    return collect([[
                                        'id' => 0,
                                        'sequence' => 1,
                                        'image_path' => asset('admin/images/placeholder.png') . Helper::assetVersion(),
                                        'remarks' => null,
                                    ]]);
                                }

                                return $galleries;
                            })(),
                            'property_tags'    => $project->project_tags ?? [],
                            'bedrooms'         => (int)($property->bedroom_text ?? 0),
                            'bathrooms'        => (int)($property->bathroom_text ?? 0),
                            'balcony'          => $property->balcony ?? 0,
                            'storeroom'        => $property->storeroom ?? 0,
                            'parking_spaces'   => $property->parking_spaces ?? 0,
                            'is_pet_friendly'  => ($project->is_pet_friendly == 1) ? 'Yes' : 'No',
                            'completion_date'  => $project->completion_date,
                            'units_left'       => self::getUnitsCountByType($property, $request->unit_type, 'available'),
                            'build_up_area_psf'    => Helper::numberFormatV2($property->build_up_area_psf ?? 0, 2, true),
                            'selling_price_psf'    => 'RM' . Helper::numberFormatV2($property->selling_price_psf ?? 0, 2, true),
                            'selling_price_unit'   => 'RM' . Helper::numberFormatV2($property->selling_price_unit ?? 0, 2, true),
                            'maintenance_fee_psf'  => 'RM' . Helper::numberFormatV2($property->maintenance_fee_psf ?? 0, 2, true),
                            // 'available_units'  => self::getUnitsByType($property, $request->unit_type, 'available')
                            // ->orderBy('floor', 'asc')
                            // ->orderBy('unit_number', 'asc')
                            // ->get()
                            // ->count(),
                            
                            'available_units_count'  => self::getUnitsByType($property, $request->unit_type, 'available')
                            ->orderBy('floor', 'asc')
                            ->orderBy('unit_number', 'asc')
                            ->get()
                            ->count(),
                        ];
                    })->values(),


                'build_up_area_psf' => $project->build_up_area_psf,
                'selling_price_psf' => $project->selling_price_psf,
                'selling_price_unit' => $project->selling_price_unit,
                'maintenance_fee_psf' => $project->maintenance_fee_psf,
                'build_up_area_psf_text' => $buildupText,
                'selling_price_psf_text' => $pricePsfText,
                'selling_price_unit_text' => $priceUnitText,
                'maintenance_fee_psf_text' => $maintenanceText,
                'bedroom_text' => $project->bedroom_text,
                'bathroom_text' => $project->bathroom_text,
                'building_type' => $project->building_type,
                'furnishing_status' => $project->furnishing_status,
                'furnishing_status_label' => self::getFurnishingStatusLabel($project->furnishing_status),
                'build_up_area_psf' => $project->build_up_area_psf,
                'selling_price_psf' => $project->selling_price_psf,
                'selling_price_unit' => $project->selling_price_unit,
                'maintenance_fee_psf' => $project->maintenance_fee_psf,
                'completion_date' => $project->completion_date,
                'project_status' => $project->project_status,
                'project_status_label' => self::getProjectStatusLabel($project->project_status),
                'is_pet_friendly' => $project->is_pet_friendly,
                'sequence' => $project->sequence,
                'developers' => $project->developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                    ];
                }),
                'country' => $project->country ? $project->country->country_name : null,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ];

            // Create search history if user is authenticated

            if (auth('user')->check()) {
                try {
                    \App\Models\SearchHistory::create([
                        'user_id' => auth('user')->user()->id,
                        'search_type' => 'for_sale',
                        // 'search_query' => null,
                        // 'search_filters' => null,
                        // 'results_count' => 1,
                        // 'ip_address' => request()->ip(),
                        // 'user_agent' => request()->userAgent(),
                        'status' => 10,
                        'project_id' => $project->id,
                        'property_id' => null,
                        'property_unit_id' => null,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the API response
                    \Log::error('Failed to create search history: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $projectData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching project details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}