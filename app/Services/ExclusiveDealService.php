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
    ExclusiveDeal,
    FileManager,
};

class ExclusiveDealService
{

    public static function createExclusiveDeal( $request ) {

        $validator = Validator::make( $request->all(), [
            'file' => [ 'required','mimes:jpeg,jpg,png' ],
        ] );

        $attributeName = [
            'file' => __( 'exclusive_deal.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $dealCreate = ExclusiveDeal::create([
                'title' => null,
                'description' => null,
                'sequence' => 1,
                'status' => 10,
            ]);

            $path = $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] );

            $dealCreate->image = $path;
            $dealCreate->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.exclusive_deals' ) ) ] ),
            'data' => [
                'id' => $dealCreate->id,
                'encrypted_id' => $dealCreate->encrypted_id,
                'url' => $dealCreate->image_path,
            ],
            'status' => 200
        ] );
    }

    public static function updateExclusiveDeal( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'exclusive_deal.title' ),
            'description' => __( 'exclusive_deal.description' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateDeal = ExclusiveDeal::find( $request->id );

            $updateDeal->title = $request->title;
            $updateDeal->description = $request->description;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $target = 'exclusive_deal/' . $updateDeal->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                    $updateDeal->image = $target;
                    $updateDeal->save();

                    $imageFile->status = 10;
                    $imageFile->save();
                }
            }

            $updateDeal->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.exclusive_deals' ) ) ] ),
        ] );
    }

    public static function allExclusiveDeals( $request ) {

        $deals = ExclusiveDeal::select( 'exclusive_deals.*' );

        $filterObject = self::filter( $request, $deals );
        $deal = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $deal->orderBy( 'exclusive_deals.created_at', $dir );
                    break;
                case 3:
                    $deal->orderBy( 'exclusive_deals.title', $dir );
                    break;
                case 4:
                    $deal->orderBy( 'exclusive_deals.description', $dir );
                    break;
            }
        }

        $dealCount = $deal->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $deals = $deal->skip( $offset )->take( $limit )->get();

        if ( $deals ) {
            $deals->append( [
                'encrypted_id',
                'image_path',
            ] );
        }

        $totalRecord = ExclusiveDeal::count();

        $data = [
            'exclusive_deals' => $deals,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $dealCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'exclusive_deals.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneExclusiveDeal( $request ) {

        $deal = ExclusiveDeal::find( $request->id );

        $deal->append( ['encrypted_id', 'image_path'] );

        return response()->json( $deal );
    }

    public static function oneExclusiveDealClient( $request ) {

        $deal = ExclusiveDeal::find( $request->id );

        $deal->append( ['encrypted_id', 'image_path'] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_exclusive_deal_success',
            'data' => $deal,
        ] );
    }

    public static function deleteExclusiveDeal( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );

        $attributeName = [
            'id' => __( 'exclusive_deal.id' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            ExclusiveDeal::find( $request->id )->delete();

            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.exclusive_deals' ) ) ] ),
        ] );
    }

    public static function updateExclusiveDealStatus( $request ) {

        DB::beginTransaction();

        try {

            $updateDeal = ExclusiveDeal::find( $request->id );
            $updateDeal->status = $updateDeal->status == 10 ? 20 : 10;

            $updateDeal->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'exclusive_deal' => $updateDeal,
                    'message_key' => 'update_exclusive_deal_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_exclusive_deal_failed',
            ], 500 );
        }
    }

    public static function removeExclusiveDealImage( $request ) {

        $updateDeal = ExclusiveDeal::find( $request->id );

        Storage::delete( 'public/' . $updateDeal->image );
        $updateDeal->image = null;

        $updateDeal->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'exclusive_deal.image' ) ) ] ),
        ] );
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'exclusive_deal/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function getExclusiveDeals( $request )
    {
        $deals = ExclusiveDeal::where( 'status', 10 );
        $deals = $deals->orderBy( 'sequence' )->get();

        foreach ( $deals as $deal ) {
            $deal->append( ['image_path'] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_exclusive_deals_success',
            'data' => $deals,
        ] );
    }

    public static function updateOrder( $request ) {
        foreach ( $request->order as $index => $id ) {
            ExclusiveDeal::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
}
