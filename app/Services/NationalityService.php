<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    Hash,
    Http,
    Validator,
};
use App\Models\{
    ApiLog,
    Nationality,
};

use Illuminate\Validation\Rules\Password;

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class Nationalityservice {

    public static function allNationalities( $request ) {

        $nationality = Nationality::select( 'nationalities.*' );

        $filterObject = self::filter( $request, $nationality );
        $nationality = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case '2':
                    $nationality->orderBy( 'created_at', $dir );
                    break;

                case '3':
                    $nationality->orderBy( 'name', $dir );
                    break;

                case '4':
                    $nationality->orderBy( 'symbol', $dir );
                    break;
            }
        }

        $nationalityCount = $nationality->count();

        $limit = $request->length;
        $offset = $request->start;

        $nationalities = $nationality->skip( $offset )->take( $limit )->get();

        $nationalities->append( [
            'encrypted_id',
        ] );

        $nationality = Nationality::select(
            DB::raw( 'COUNT(nationalities.id) as total'
        ) );

        $filterObject = self::filter( $request, $nationality );
        $nationality = $filterObject['model'];
        $filter = $filterObject['filter'];

        $nationality = $nationality->first();

        $data = [
            'nationalities' => $nationalities,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $nationalityCount : $nationality->total,
            'recordsTotal' => $filter ? Nationality::count() : $nationalityCount,
        ];

        return $data;
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

                $model->whereBetween( 'form_countries.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'form_countries.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->symbol ) ) {
            $model->where( 'symbol', 'LIKE', '%' . $request->symbol . '%' );
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $model->where( function( $query ) use ( $request ) {
                $query->where( 'nationalities.email', 'LIKE', '%' . $request->user . '%' );
                $query->orWhere( 'nationalities.username', 'LIKE', '%' . $request->user , '%' );
                // $query->orWhereHas( 'userDetail', function( $query ) use ( $request ) {
                //     $query->where( 'user_details.fullname', 'LIKE', '%' . $request->user . '%' );
                // } );
            } );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( function( $query ) use ( $request ) {
                $query->where( 'nationalities.phone_number', $request->phone_number );
                $query->orWhere( DB::raw( "CONCAT( calling_code, phone_number )" ), 'LIKE', '%' . $request->phone_number );
            } );
            $filter = true;
        }

        if ( !empty( $request->referral ) ) {
            $model->whereHas( 'referral', function( $query ) use ( $request ) {
                $query->where( 'nationalities.email', $request->referral );
                $query->orWhereHas( 'userDetail', function( $query ) use ( $request ) {
                    $query->where( 'user_details.fullname', 'LIKE', '%' . $request->referral . '%' );
                } );
            } );
            $filter = true;
        }

        if ( !empty( $request->role ) ) {
            $model->where( 'nationalities.role', $request->role );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'nationalities.status', $request->status );
            $filter = true;
        }     

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneNationality( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $nationality = Nationality::find( $request->id );

        return $nationality;
    }

    public static function createNationalityAdmin( $request ) {

        DB::beginTransaction();

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required', 'unique:nationalities,name' ],
            'symbol' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'nationality.name' ),
            'symbol' => __( 'nationality.symbol' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $createNationalityObject = [
                'name' => $request->name,
                'symbol' => $request->symbol,
                'status' => 10,
            ];

            $createNationality = Nationality::create( $createNationalityObject );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.nationalities' ) ) ] ),
        ] );
    }

    public static function updateNationalityAdmin( $request ) {

        DB::beginTransaction();

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required', 'unique:nationalities,name,' . $request->id ],
            'symbol' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'nationality.name' ),
            'symbol' => __( 'nationality.symbol' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();    

        try {

            $updateNationality = Nationality::find( $request->id );

            $updateNationality->name = $request->name;    
            $updateNationality->symbol = $request->symbol;
            $updateNationality->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.nationalities' ) ) ] ),
        ] );
    }

    public static function updateNationalityStatus( $request ) {

        DB::beginTransaction();

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'status' => 'required',
        ] );
        
        $validator->validate();

        try {

            $updateNationality = Nationality::lockForUpdate()->find( $request->id );
            $updateNationality->status = $request->status;
            $updateNationality->save();

            DB::commit();
            
            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.nationalities' ) ) ] ),
            ] );

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }
    }
}