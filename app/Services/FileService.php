<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    FileManager,
};

use Helper;

use Carbon\Carbon;

class FileService
{
    public static function upload( $request ) {

        // Check if amenity is set and true
        if ( $request->has( 'amenity' ) && $request->boolean( 'amenity' ) ) {
            // Amenity upload: only png & svg
            $validator = Validator::make( $request->all(), [
                'file' => [ 'required', 'mimes:jpeg,jpg,png,svg' ],
            ] );
        } else {
            // Normal upload
            $validator = Validator::make( $request->all(), [
                'file' => [ 'required', 'mimes:jpeg,jpg,png,pdf' ],
            ] );
        }
    
        $attributeName = [
            'file' => __( 'banner.image' ),
        ];
    
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
    
        $validator->setAttributeNames( $attributeName )->validate();
    
        // Handle storage
        $file = $request->file( 'file' );
        $extension = $file->getClientOriginalExtension();
    
        $filePath = $file->store( 'file-managers', [ 'disk' => 'public' ] );
    
        $createFile = FileManager::create( [
            'name' => $file->getClientOriginalName(),
            'file' => $filePath,
            'type' => $extension === 'pdf' ? 1 : 2,
        ] );
    
        return response()->json( [
            'status' => 200,
            'data'   => $createFile,
        ] );
    }
    
}