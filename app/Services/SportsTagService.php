<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;
use App\Models\SportsTag;

class SportsTagService
{
    public static function allSportsTags( $request )
    {
        $tags = SportsTag::select( 'sports_tags.*' )->orderBy('created_at', 'DESC');

        $filter = false;

        if ( !empty( $request->name ) ) {
            $tags->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $tags->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->created_date ) ) {
            $dates = explode( ' to ', $request->created_date );
            if ( count( $dates ) == 2 ) {
                $tags->whereBetween( DB::raw( 'DATE(created_at)' ), [ trim( $dates[0] ), trim( $dates[1] ) ] );
            } else {
                $tags->whereDate( 'created_at', trim( $dates[0] ) );
            }
            $filter = true;
        }

        $total  = SportsTag::count();
        $count  = $tags->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $result = $tags->orderBy( 'name' )->skip( $request->start )->take( $limit )->get();

        $result->append( [ 'encrypted_id', 'icon_path' ] );

        return response()->json( [
            'sports_tags'     => $result,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $count : $total,
            'recordsTotal'    => $total,
        ] );
    }

    public static function oneSportsTag( $request )
    {
        $tag = SportsTag::find( Helper::decode( $request->id ) );
        $tag->append( [ 'encrypted_id', 'icon_path' ] );

        return response()->json( $tag );
    }

    public static function createSportsTag( $request )
    {
        $validator = Validator::make( $request->all(), [
            'name'   => [ 'required', 'string', 'max:255', 'unique:sports_tags,name' ],
            'icon'   => [ 'nullable', 'mimes:jpeg,jpg,png,svg', 'max:2048' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $data = [
                'name'   => $request->name,
                'slug'   => Str::slug( $request->name ),
                'status' => 10,
            ];

            if ( $request->hasFile( 'icon' ) ) {
                $data['icon'] = $request->file( 'icon' )->store( 'sports_tags/icons', [ 'disk' => 'public' ] );
            }

            $tag = SportsTag::create( $data );

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => 'Amenity' ] ),
            'data'    => [ 'id' => $tag->id, 'encrypted_id' => $tag->encrypted_id ],
        ] );
    }

    public static function updateSportsTag( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $validator = Validator::make( $request->all(), [
            'id'   => [ 'required', 'exists:sports_tags,id' ],
            'name' => [ 'required', 'string', 'max:255', 'unique:sports_tags,name,' . $request->id ],
            'icon' => [ 'nullable', 'mimes:jpeg,jpg,png,svg', 'max:2048' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $tag = SportsTag::find( $request->id );
            $tag->name = $request->name;
            $tag->slug = Str::slug( $request->name );

            if ( $request->hasFile( 'icon' ) ) {
                if ( $tag->icon ) {
                    Storage::disk( 'public' )->delete( $tag->icon );
                }
                $tag->icon = $request->file( 'icon' )->store( 'sports_tags/icons', [ 'disk' => 'public' ] );
            }

            $tag->save();

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Amenity' ] ) ] );
    }

    public static function updateSportsTagStatus( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $tag         = SportsTag::find( $request->id );
        $tag->status = $request->status;
        $tag->save();

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Amenity' ] ) ] );
    }

    public static function deleteSportsTag( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $tag = SportsTag::find( $request->id );

        if ( !$tag ) {
            return response()->json( [ 'message' => 'Not found' ], 404 );
        }

        if ( $tag->icon ) {
            Storage::disk( 'public' )->delete( $tag->icon );
        }

        $tag->delete();

        return response()->json( [ 'message' => __( 'template.x_deleted', [ 'title' => 'Amenity' ] ) ] );
    }
}
