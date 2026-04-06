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
    Amenity,
    FileManager,
};

use Carbon\Carbon;

class AmenityService
{

    public static function createAmenity( $request ) {

        $rules = [
            'title' => 'required|string|max:255|unique:amenities,title',
            'status' => [ 'nullable', 'integer', 'in:10,20' ],
        ];

        $validator = Validator::make( $request->all(), $rules );

        $attributeName = [
            'title' => __('template.title'),
            'status' => __('template.status'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $amenityCreate = Amenity::create([
                'title' => $request->title,
                'status' => $request->status ?? 10,
            ]);

            // Handle icon upload
            if ($request->icon) {
                $icon = explode( ',', $request->icon );
                $iconFiles = FileManager::whereIn( 'id', $icon )->get();

                if ( $iconFiles ) {
                    foreach ( $iconFiles as $iconFile ) {
                        $fileName = explode( '/', $iconFile->file );
                        $fileExtention = pathinfo($fileName[1])['extension'];

                        $target = 'amenity/' . $amenityCreate->id . '/' . $fileName[1];
                        Storage::disk( 'public' )->move( $iconFile->file, $target );

                        $amenityCreate->icon = $target;
                        $amenityCreate->save();

                        $iconFile->status = 10;
                        $iconFile->save();
                    }
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.amenities' ) ) ] ),
        ] );
    }
    
    public static function updateAmenity( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $rules = [
            'id' => [ 'required', 'exists:amenities,id' ],
            'title' => [ 'required', 'string', 'max:255' ],
            'status' => [ 'nullable', 'integer', 'in:10,20' ],
        ];

        $validator = Validator::make( $request->all(), $rules );

        $attributeName = [
            'id' => __('template.id'),
            'title' => __('template.title'),
            'status' => __('template.status'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();
        try {
            $updateAmenity = Amenity::find( $request->id );

            $updateAmenity->title = $request->title;
            $updateAmenity->status = $request->status ?? 10;
            
            // Handle icon upload
            if ($request->icon) {
                $icon = explode( ',', $request->icon );
                $iconFiles = FileManager::whereIn( 'id', $icon )->get();

                if ( $iconFiles ) {
                    foreach ( $iconFiles as $iconFile ) {
                        $fileName = explode( '/', $iconFile->file );
                        $fileExtention = pathinfo($fileName[1])['extension'];

                        $target = 'amenity/' . $updateAmenity->id . '/' . $fileName[1];
                        Storage::disk( 'public' )->move( $iconFile->file, $target );

                        $updateAmenity->icon = $target;

                        $iconFile->status = 10;
                        $iconFile->save();
                    }
                }
            }

            $updateAmenity->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.amenities' ) ) ] ),
        ] );
    }

    public static function allAmenities( $request ) {

        $amenities = Amenity::select( 'amenities.*');

        $filterObject = self::filter( $request, $amenities );
        $amenity = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $amenity->orderBy( 'amenities.title', $dir );
                    break;
                case 2:
                    $amenity->orderBy( 'amenities.created_at', $dir );
                    break;
                case 3:
                    $amenity->orderBy( 'amenities.status', $dir );
                    break;
            }
        } else {
            $amenity->orderBy( 'amenities.created_at', 'desc' );
        }

        $amenityCount = $amenity->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $amenities = $amenity->skip( $offset )->take( $limit )->get();

        if ( $amenities ) {
            $amenities->append( [
                'encrypted_id',
                'icon_path',
            ] );
        }

        $totalRecord = Amenity::count();

        $data = [
            'amenities' => $amenities,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $amenityCount : $totalRecord,
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

                $model->whereBetween( 'amenities.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'amenities.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'amenities.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'amenities.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'amenities.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAmenity( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $amenity = Amenity::find( $request->id );

        $amenity->append( ['encrypted_id','icon_path'] );
        
        return response()->json( $amenity );
    }

    public static function updateAmenityStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateAmenity = Amenity::find( $request->id );
            $updateAmenity->status = $updateAmenity->status == 10 ? 20 : 10;

            $updateAmenity->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'amenity' => $updateAmenity,
                    'message_key' => 'update_amenity_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_amenity_failed',
            ], 500 );
        }
    }

    public static function removeAmenityIcon( $request ) {

        $updateAmenity = Amenity::find( Helper::decode($request->id) );
        
        switch ($request->scope) {
            case 'icon':
                if ($updateAmenity->icon) {
                    Storage::disk('public')->delete( $updateAmenity->icon );
                    $updateAmenity->icon = null;
                }
                break;
            
            default:
                break;
        }

        $updateAmenity->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.amenities' ) ) ] ),
        ] );
    }
}