<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BookingService,
};

use App\Models\{
    Booking,
};

class BookingController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.bookings' );
        $this->data['content'] = 'admin.booking.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.bookings' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '11' => __( 'datatables.completed' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['booking_type'] = [
            1 => __( 'booking.buy' ),
            2 => __( 'booking.long_rent' ),
            3 => __( 'booking.short_rent' ),
        ];

        $this->data['data']['preferred_time'] = Booking::preferredTimeOptions();

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] );
        $this->data['content'] = 'admin.booking.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.booking.index' ),
                'text' => __( 'template.bookings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] );
        $this->data['content'] = 'admin.booking.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.booking.index' ),
                'text' => __( 'template.bookings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBookings( Request $request ) {

        return BookingService::allBookings( $request );
    }

    public function oneBooking( Request $request ) {

        return BookingService::oneBooking( $request );
    }

    public function createBooking( Request $request ) {

        return BookingService::createBooking( $request );
    }

    public function updateBooking( Request $request ) {

        return BookingService::updateBooking( $request );
    }

    public function updateBookingStatus( Request $request ) {

        return BookingService::updateBookingStatus( $request );
    }

    public function removeBookingProfilePicture( Request $request ) {

        return BookingService::removeBookingProfilePicture( $request );
    }

    public function getAvailableTimeSlots( Request $request ) {

        return BookingService::getAvailableTimeSlots( $request );
    }

    public function markBookingCompleted( Request $request ) {

        return BookingService::markBookingCompleted( $request );
    }

    public function getFloorplansByProperty( Request $request ) {

        return BookingService::getFloorplansByProperty( $request );
    }

    public function getPropertyUnits( Request $request ) {

        return BookingService::getPropertyUnits( $request );
    }
}
