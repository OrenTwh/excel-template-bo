<?php $bc = 'booking_cal'; ?>

@php
    // Build court list keyed by venue encrypted_id for JS cascade
    $courtsByVenue = [];
    $allCourtsFlat = [];
    foreach ( $data['courts'] as $court ) {
        $vEncId = Helper::encode( $court->venueSport?->venue?->id );
        $cEncId = Helper::encode( $court->id );
        $entry  = [ 'id' => $cEncId, 'name' => $court->name, 'venue' => $vEncId ];
        $allCourtsFlat[] = $entry;
        if ( !$vEncId ) continue;
        $courtsByVenue[ $vEncId ][] = [ 'id' => $cEncId, 'name' => $court->name ];
    }
@endphp

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'Booking Calendar' ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court_booking.upcoming' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to Bookings</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Filter bar ────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner py-3">
        <div class="row g-3 align-items-end">

            <div class="col-sm-5 col-md-4">
                <label class="form-label mb-1">Date Range <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="{{ $bc }}_date" placeholder="Select date range" autocomplete="off">
            </div>

            <div class="col-sm-3 col-md-3">
                <label class="form-label mb-1">Venue</label>
                <select class="form-select" id="{{ $bc }}_venue_id">
                    <option value="">All Venues</option>
                    @foreach( $data['venues'] as $venue )
                        <option value="{{ Helper::encode( $venue->id ) }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-3 col-md-3">
                <label class="form-label mb-1">Court</label>
                <select class="form-select" id="{{ $bc }}_court_id">
                    <option value="">All Courts</option>
                    @foreach( $data['courts'] as $court )
                        <option value="{{ Helper::encode( $court->id ) }}"
                            data-venue="{{ Helper::encode( $court->venueSport?->venue?->id ) }}">
                            {{ $court->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-auto">
                <button class="btn btn-primary" id="{{ $bc }}_load">
                    <em class="icon ni ni-search"></em> <span>Load</span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ── Results area ──────────────────────────────────────────────────────────── --}}
<div id="{{ $bc }}_results" style="display:none;">

    {{-- Summary bar --}}
    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap" id="{{ $bc }}_summary">
        <h6 class="mb-0 text-primary" id="{{ $bc }}_date_display">—</h6>
        <div class="d-flex gap-2 flex-wrap" id="{{ $bc }}_badges"></div>
    </div>

    {{-- Bookings table --}}
    <div class="card">
        <div class="card-inner p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="{{ $bc }}_table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap" id="{{ $bc }}_th_date" style="display:none;">Date</th>
                            <th class="text-nowrap">Time Slot</th>
                            <th>Court</th>
                            <th>Venue · Sport</th>
                            <th>User</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="{{ $bc }}_tbody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Select a date range and click Load.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Empty state --}}
<div id="{{ $bc }}_empty" style="display:none;">
    <div class="card">
        <div class="card-inner text-center py-5">
            <em class="icon ni ni-calendar-off fs-1 text-muted"></em>
            <p class="text-muted mt-3 mb-0">No bookings found for the selected date range.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    const courtsByVenue = @json( $courtsByVenue );
    const allCourts     = @json( $allCourtsFlat );

    // ── Flatpickr date range ───────────────────────────────────────────────────
    let fpDate = flatpickr( '#{{ $bc }}_date', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        defaultDate: [ 'today', 'today' ],
        disableMobile: true,
    } );

    // ── Select2 ───────────────────────────────────────────────────────────────
    let $venue = $( '#{{ $bc }}_venue_id' );
    let $court = $( '#{{ $bc }}_court_id' );

    $venue.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'All Venues' } );
    $court.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'All Courts' } );

    // ── Venue → Court cascade ─────────────────────────────────────────────────
    $venue.on( 'change', function() {
        let venueId     = $( this ).val();
        let currentCourt = $court.val();

        $court.empty().append( '<option value="">All Courts</option>' );

        let list = venueId ? ( courtsByVenue[ venueId ] ?? [] ) : allCourts;
        list.forEach( function( c ) {
            $court.append( $( '<option>' ).val( c.id ).text( c.name ) );
        } );

        // restore selection if still in list
        let ids = list.map( c => c.id );
        $court.val( ids.includes( currentCourt ) ? currentCourt : '' ).trigger( 'change.select2' );
    } );

    // ── Status colours & labels ───────────────────────────────────────────────
    let statusLabels = @json( $data['status_labels'] );
    let statusColors = { 1: 'warning', 10: 'primary', 11: 'success', 20: 'secondary', 21: 'danger' };
    let paymentColors = { pending: 'warning', paid: 'success', failed: 'danger', refunded: 'info' };

    function statusBadge( status ) {
        return `<span class="badge bg-${statusColors[ status ] ?? 'light'}">${statusLabels[ status ] ?? status}</span>`;
    }

    function paymentBadge( ps ) {
        return `<span class="badge bg-outline-${paymentColors[ ps ] ?? 'light'}">${ps}</span>`;
    }

    function fmtDate( str ) {
        return str ? new Date( str + 'T00:00:00' ).toLocaleDateString( 'en-MY', {
            weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
        } ) : '—';
    }

    // ── Load bookings ─────────────────────────────────────────────────────────
    $( '#{{ $bc }}_load' ).on( 'click', loadBookings );

    // Auto-load with today
    loadBookings();

    function loadBookings() {
        let selectedDates = fpDate.selectedDates;

        if ( !selectedDates.length ) {
            alert( 'Please select a date range.' );
            return;
        }

        // Flatpickr range: first date is always set; second = first if only one picked
        let dateFrom = fpDate.formatDate( selectedDates[0], 'Y-m-d' );
        let dateTo   = selectedDates[1]
            ? fpDate.formatDate( selectedDates[1], 'Y-m-d' )
            : dateFrom;

        let venueId = $venue.val();
        let courtId = $court.val();

        $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

        let payload = {
            '_token':    '{{ csrf_token() }}',
            'date_from': dateFrom,
            'date_to':   dateTo,
        };
        if ( venueId ) payload['venue_id'] = venueId;
        if ( courtId ) payload['court_id'] = courtId;

        $.ajax( {
            url:     '{{ route( 'admin.court_booking.getBookingsByDate' ) }}',
            type:    'POST',
            data:    payload,
            success: function( response ) {
                $( 'body' ).loading( 'stop' );
                renderBookings( response );
            },
            error: function() {
                $( 'body' ).loading( 'stop' );
                alert( 'Failed to load bookings.' );
            }
        } );
    }

    function renderBookings( response ) {
        let bookings = response.bookings ?? [];
        let dateFrom = response.date_from ?? '';
        let dateTo   = response.date_to   ?? '';
        let isRange  = dateFrom !== dateTo;

        // ── Summary header ────────────────────────────────────────────────────
        $( '#{{ $bc }}_date_display' ).text(
            isRange ? fmtDate( dateFrom ) + ' — ' + fmtDate( dateTo ) : fmtDate( dateFrom )
        );
        $( '#{{ $bc }}_th_date' ).toggle( isRange );

        // Summary badges
        let counts = {};
        bookings.forEach( b => { let s = b.group?.status; counts[ s ] = ( counts[ s ] ?? 0 ) + 1; } );
        let badgesHtml = '';
        Object.entries( counts ).forEach( function( [status, count] ) {
            badgesHtml += `<span class="badge bg-dim bg-${ statusColors[ status ] ?? 'light' }">${ statusLabels[ status ] ?? status }: ${ count }</span>`;
        } );
        $( '#{{ $bc }}_badges' ).html( badgesHtml || '<span class="text-muted small">No bookings</span>' );

        if ( !bookings.length ) {
            $( '#{{ $bc }}_results' ).hide();
            $( '#{{ $bc }}_empty' ).show();
            return;
        }

        $( '#{{ $bc }}_empty' ).hide();
        $( '#{{ $bc }}_results' ).show();

        let rows = '';
        bookings.forEach( function( b ) {
            let courtName = b.court?.name ?? '—';
            let venueName = b.court?.venue_sport?.venue?.name ?? '—';
            let sportName = b.court?.venue_sport?.sport?.name ?? '—';
            let userName  = b.group?.user?.fullname ?? '—';
            let userPhone = b.group?.user?.phone_number
                ? `<small class="text-muted d-block">${ ( b.group.user.calling_code ?? '' ) + ' ' + b.group.user.phone_number }</small>`
                : '';
            let startTime = ( b.start_time ?? '' ).substring( 0, 5 );
            let endTime   = ( b.end_time   ?? '' ).substring( 0, 5 );
            let amount    = b.total_amount ? 'RM ' + parseFloat( b.total_amount ).toFixed( 2 ) : '—';
            let editUrl   = '{{ route( 'admin.court_booking.edit' ) }}?id=' + ( b.group?.encrypted_id ?? '' );
            let date      = ( b.booking_date ?? '' ).substring( 0, 10 );

            let dateCell = isRange ? `<td class="text-nowrap text-muted small">${ fmtDate( date ) }</td>` : '';

            rows += `
                <tr>
                    ${ dateCell }
                    <td class="text-nowrap fw-bold">${ startTime } – ${ endTime }</td>
                    <td>${ courtName }</td>
                    <td><span class="text-nowrap">${ venueName }</span> <span class="text-muted">·</span> <span class="text-nowrap">${ sportName }</span></td>
                    <td>${ userName }${ userPhone }</td>
                    <td class="text-end text-nowrap">${ amount }</td>
                    <td>${ statusBadge( b.group?.status ) }</td>
                    <td>${ paymentBadge( b.group?.payment_status ) }</td>
                    <td class="text-center">
                        @can( 'edit court_bookings' )
                        <a href="${ editUrl }" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">
                            <em class="icon ni ni-edit"></em>
                        </a>
                        @endcan
                    </td>
                </tr>`;
        } );

        $( '#{{ $bc }}_tbody' ).html( rows );
    }

} );
</script>
