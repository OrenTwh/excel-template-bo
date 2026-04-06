<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helper;

class UtilsController extends Controller
{
    public function showcase( Request $request )
    {
        $this->data['header']['title'] = 'Utils Showcase';
        $this->data['content']         = 'admin.utils.showcase';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => '',                          'text' => 'Utils Showcase',            'class' => 'active' ],
        ];

        $this->data['data']['calling_codes']  = Helper::getCallingCodes();
        $this->data['data']['select_options'] = [
            [ 'value' => '1', 'text' => 'Option A' ],
            [ 'value' => '2', 'text' => 'Option B' ],
            [ 'value' => '3', 'text' => 'Option C' ],
        ];
        $this->data['data']['multiselect_options'] = [
            [ 'value' => '1', 'text' => 'Category 1' ],
            [ 'value' => '2', 'text' => 'Category 2' ],
            [ 'value' => '3', 'text' => 'Category 3' ],
            [ 'value' => '4', 'text' => 'Category 4' ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function ckeUpload( Request $request )
    {
        if ( $request->hasFile( 'upload' ) ) {
            $file     = $request->file( 'upload' );
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs( 'admin/images/utils/ckeditor', $filename, 'public' );

            return response()->json( [ 'uploaded' => true, 'url' => asset( 'storage/' . $path ) ] );
        }

        return response()->json( [ 'uploaded' => false, 'error' => [ 'message' => 'No file uploaded' ] ] );
    }

    public function getCallingCodes( Request $request )
    {
        try {
            $codes = collect( Helper::getCallingCodes() )->map( function( $label, $code ) {
                return [ 'code' => $code, 'label' => $label ];
            } )->values();

            return response()->json( [ 'status' => 'success', 'data' => $codes ] );
        } catch ( \Exception $e ) {
            return response()->json( [ 'status' => 'error', 'message' => 'Failed to retrieve calling codes' ], 500 );
        }
    }
}
