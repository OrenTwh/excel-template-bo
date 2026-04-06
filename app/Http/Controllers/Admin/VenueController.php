<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\VenueService;
use App\Models\{ Venue, Sport };

class VenueController extends Controller
{
    public function index( Request $request )
    {
        $this->data['header']['title'] = __( 'Venues' );
        $this->data['content']         = 'admin.venue.index';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'Venues' ), 'class' => 'active' ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request )
    {
        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => 'Venue' ] );
        $this->data['content']         = 'admin.venue.add';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue.index' ), 'text' => __( 'Venues' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.add_x', [ 'title' => 'Venue' ] ), 'class' => 'active' ],
        ];
        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request )
    {
        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => 'Venue' ] );
        $this->data['content']         = 'admin.venue.edit';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue.index' ), 'text' => __( 'Venues' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.edit_x', [ 'title' => 'Venue' ] ), 'class' => 'active' ],
        ];
        $this->data['data']['sports'] = Sport::where( 'status', 10 )->get( [ 'id', 'name' ] );

        return view( 'admin.main' )->with( $this->data );
    }

    public function sportIndex( Request $request )
    {
        $this->data['header']['title'] = __( 'template.venue_sports' );
        $this->data['content']         = 'admin.venue_sport.index';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue.index' ), 'text' => __( 'template.venues' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.venue_sports' ), 'class' => 'active' ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function sportAdd( Request $request )
    {
        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => __( 'template.venue_sports' ) ] );
        $this->data['content']         = 'admin.venue_sport.add';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue.index' ), 'text' => __( 'template.venues' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue_sport.index' ), 'text' => __( 'template.venue_sports' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.add_x', [ 'title' => __( 'template.venue_sports' ) ] ), 'class' => 'active' ],
        ];
        $this->data['data']['venues'] = Venue::where( 'status', 10 )->get()->append( [ 'encrypted_id' ] );
        $this->data['data']['sports'] = Sport::where( 'status', 10 )->get( [ 'id', 'name' ] );

        return view( 'admin.main' )->with( $this->data );
    }

    public function sportEdit( Request $request )
    {
        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => __( 'template.venue_sports' ) ] );
        $this->data['content']         = 'admin.venue_sport.edit';
        $this->data['breadcrumb']      = [
            [ 'url' => route( 'admin.dashboard' ), 'text' => __( 'template.dashboard' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue.index' ), 'text' => __( 'template.venues' ), 'class' => '' ],
            [ 'url' => route( 'admin.module_parent.venue_sport.index' ), 'text' => __( 'template.venue_sports' ), 'class' => '' ],
            [ 'url' => '', 'text' => __( 'template.edit_x', [ 'title' => __( 'template.venue_sports' ) ] ), 'class' => 'active' ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVenueSportsGlobal( Request $request ) { return VenueService::allVenueSportsGlobal( $request ); }
    public function oneVenueSport( Request $request )        { return VenueService::oneVenueSport( $request ); }
    public function importVenueSports( Request $request )    { return VenueService::importVenueSports( $request ); }

    public function allVenues( Request $request )       { return VenueService::allVenues( $request ); }
    public function oneVenue( Request $request )        { return VenueService::oneVenue( $request ); }
    public function createVenue( Request $request )     { return VenueService::createVenue( $request ); }
    public function updateVenue( Request $request )     { return VenueService::updateVenue( $request ); }
    public function updateVenueStatus( Request $request ) { return VenueService::updateVenueStatus( $request ); }

    public function allVenueSports( Request $request )  { return VenueService::allVenueSports( $request ); }
    public function addVenueSport( Request $request )   { return VenueService::addVenueSport( $request ); }
    public function updateVenueSport( Request $request ){ return VenueService::updateVenueSport( $request ); }
    public function updateVenueSportStatus( Request $request ) { return VenueService::updateVenueSportStatus( $request ); }
    public function removeVenueSport( Request $request ){ return VenueService::removeVenueSport( $request ); }
}
