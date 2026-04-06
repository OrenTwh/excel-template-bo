<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SportsTag;
use App\Services\SportsTagService;

class SportsTagController extends Controller
{
    public function index( Request $request )
    {
        $this->data['header']['title'] = __( 'template.amenities' );
        $this->data['content']         = 'admin.sports_tag.index';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.amenities' ), 'class' => 'active' ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    /**
     * Select2 AJAX search — used by sport tags selector & venue amenities selector.
     * Supports optional ?ids[]=1&ids[]=2 for pre-loading selected values.
     */
    public function all( Request $request )
    {
        $query = SportsTag::where( 'status', 10 );

        if ( $request->filled( 'name' ) ) {
            $query->where( 'name', 'like', '%' . $request->name . '%' );
        }

        if ( $request->filled( 'ids' ) ) {
            $query->whereIn( 'id', (array) $request->ids );
        }

        $limit  = $request->input( 'length', 20 );
        $offset = $request->input( 'start', 0 );

        $total = $query->count();
        $tags  = $query->orderBy( 'name' )->skip( $offset )->take( $limit )->get( [ 'id', 'name', 'icon' ] );

        $tags->append( [ 'icon_path' ] );

        return response()->json( [
            'sports_tags'     => $tags,
            'recordsFiltered' => $total,
        ] );
    }

    public function allSportsTags( Request $request )   { return SportsTagService::allSportsTags( $request ); }
    public function oneSportsTag( Request $request )    { return SportsTagService::oneSportsTag( $request ); }
    public function createSportsTag( Request $request ) { return SportsTagService::createSportsTag( $request ); }
    public function updateSportsTag( Request $request ) { return SportsTagService::updateSportsTag( $request ); }
    public function updateSportsTagStatus( Request $request ) { return SportsTagService::updateSportsTagStatus( $request ); }
    public function deleteSportsTag( Request $request ) { return SportsTagService::deleteSportsTag( $request ); }
}
