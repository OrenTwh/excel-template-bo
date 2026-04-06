<?php $st = 'avail_stat'; ?>

@php
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
            <h3 class="nk-block-title page-title">{{ __( 'Availability Statistics' ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.court_booking.booking_calendar' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-calendar"></em> <span>Booking Calendar</span>
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
                <input type="text" class="form-control" id="{{ $st }}_date" placeholder="Select date range" autocomplete="off">
            </div>

            <div class="col-sm-3 col-md-3">
                <label class="form-label mb-1">Venue</label>
                <select class="form-select" id="{{ $st }}_venue_id">
                    <option value="">All Venues</option>
                    @foreach( $data['venues'] as $venue )
                        <option value="{{ Helper::encode( $venue->id ) }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-3 col-md-3">
                <label class="form-label mb-1">Court</label>
                <select class="form-select" id="{{ $st }}_court_id">
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
                <button class="btn btn-primary" id="{{ $st }}_load">
                    <em class="icon ni ni-search"></em> <span>Load</span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ── Legend ────────────────────────────────────────────────────────────────── --}}
<div class="d-flex gap-3 flex-wrap mb-3 align-items-center small" id="{{ $st }}_legend" style="display:none!important;">
    <span class="fw-bold text-muted">Legend:</span>
    <span><span class="avail-cell av-available d-inline-block me-1" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;"></span> Available</span>
    <span><span class="avail-cell av-booked d-inline-block me-1" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;"></span> Booked</span>
    <span><span class="avail-cell av-partial d-inline-block me-1" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;"></span> Partial</span>
    <span><span class="avail-cell av-blocked d-inline-block me-1" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;"></span> Blocked</span>
    <span><span class="avail-cell av-closed d-inline-block me-1" style="width:16px;height:16px;border-radius:3px;vertical-align:middle;"></span> Closed</span>
</div>

{{-- ── Grid output area ─────────────────────────────────────────────────────── --}}
<div id="{{ $st }}_output"></div>

{{-- ── Empty state ──────────────────────────────────────────────────────────── --}}
<div id="{{ $st }}_empty" style="display:none;">
    <div class="card">
        <div class="card-inner text-center py-5">
            <em class="icon ni ni-calendar-off fs-1 text-muted"></em>
            <p class="text-muted mt-3 mb-0">No courts found for the selected filters.</p>
        </div>
    </div>
</div>

<style>
.avail-grid { border-collapse: collapse; font-size: 0.75rem; }
.avail-grid th, .avail-grid td { border: 1px solid #e5e9f0; padding: 0; text-align: center; }
.avail-grid .court-label { min-width: 130px; max-width: 160px; text-align: left; padding: 4px 8px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.avail-grid .hour-header { width: 44px; min-width: 44px; padding: 3px 2px; font-size: 0.7rem; color: #8094ae; }
.avail-cell { display: block; width: 44px; height: 36px; cursor: default; position: relative; }
a.avail-cell { cursor: pointer; }
.av-available { background: #d4edda; }
.av-booked    { background: #f5c6cb; }
.av-partial   { background: #ffeeba; }
.av-blocked   { background: #dee2e6; background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(0,0,0,.06) 4px, rgba(0,0,0,.06) 6px); }
.av-closed    { background: #f8f9fa; }
.avail-cell .cell-text { font-size: 0.65rem; line-height: 1; display: flex; align-items: center; justify-content: center; height: 100%; color: #555; font-weight: 600; }
</style>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    const courtsByVenue = @json( $courtsByVenue );
    const allCourts     = @json( $allCourtsFlat );

    // ── Flatpickr date range ───────────────────────────────────────────────────
    let fpDate = flatpickr( '#{{ $st }}_date', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        defaultDate: [ 'today', 'today' ],
        disableMobile: true,
    } );

    // ── Select2 ───────────────────────────────────────────────────────────────
    let $venue = $( '#{{ $st }}_venue_id' );
    let $court = $( '#{{ $st }}_court_id' );

    $venue.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'All Venues' } );
    $court.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'All Courts' } );

    // ── Venue → Court cascade ─────────────────────────────────────────────────
    $venue.on( 'change', function() {
        let venueId      = $( this ).val();
        let currentCourt = $court.val();

        $court.empty().append( '<option value="">All Courts</option>' );
        let list = venueId ? ( courtsByVenue[ venueId ] ?? [] ) : allCourts;
        list.forEach( c => $court.append( $( '<option>' ).val( c.id ).text( c.name ) ) );

        let ids = list.map( c => c.id );
        $court.val( ids.includes( currentCourt ) ? currentCourt : '' ).trigger( 'change.select2' );
    } );

    // ── Load ──────────────────────────────────────────────────────────────────
    $( '#{{ $st }}_load' ).on( 'click', loadData );
    loadData();

    function loadData() {
        let selectedDates = fpDate.selectedDates;
        if ( !selectedDates.length ) { alert( 'Please select a date range.' ); return; }

        let dateFrom = fpDate.formatDate( selectedDates[0], 'Y-m-d' );
        let dateTo   = selectedDates[1] ? fpDate.formatDate( selectedDates[1], 'Y-m-d' ) : dateFrom;

        $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

        let payload = { '_token': '{{ csrf_token() }}', 'date_from': dateFrom, 'date_to': dateTo };
        let venueId = $venue.val();
        let courtId = $court.val();
        if ( venueId ) payload['venue_id'] = venueId;
        if ( courtId ) payload['court_id'] = courtId;

        $.ajax( {
            url:     '{{ route( 'admin.court_booking.getAvailabilityData' ) }}',
            type:    'POST',
            data:    payload,
            success: function( r ) { $( 'body' ).loading( 'stop' ); renderGrid( r ); },
            error:   function() {
                $( 'body' ).loading( 'stop' );
                alert( 'Failed to load availability data.' );
            }
        } );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function fmtDate( str ) {
        return str ? new Date( str + 'T00:00:00' ).toLocaleDateString( 'en-MY', {
            weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
        } ) : '—';
    }

    function toMins( hhmm ) {
        let [h, m] = hhmm.split(':').map(Number);
        return h * 60 + (m || 0);
    }

    // Does interval [aStart, aEnd) overlap with [bStart, bEnd)?  (all in H:i strings)
    function overlaps( aStart, aEnd, bStart, bEnd ) {
        return toMins( aStart ) < toMins( bEnd ) && toMins( aEnd ) > toMins( bStart );
    }

    // ── Render grid ───────────────────────────────────────────────────────────
    function renderGrid( response ) {
        let courts   = response.courts   ?? [];
        let dates    = response.dates    ?? [];
        let bookings = response.bookings ?? {};
        let blocks   = response.blocks   ?? {};

        $( '#{{ $st }}_output' ).empty();
        $( '#{{ $st }}_empty' ).hide();

        if ( !courts.length || !dates.length ) {
            $( '#{{ $st }}_empty' ).show();
            return;
        }

        $( '#{{ $st }}_legend' ).show().css( 'display', 'flex' );

        // Determine display hour range: min(open) to max(close) across all courts
        let minOpen  = courts.reduce( (m, c) => Math.min( m, toMins( c.open_time  ) ), 24 * 60 );
        let maxClose = courts.reduce( (m, c) => Math.max( m, toMins( c.close_time ) ),  0 );
        // Round to whole hours; clamp to 06:00–24:00
        let startHour = Math.max( 6,  Math.floor( minOpen  / 60 ) );
        let endHour   = Math.min( 24, Math.ceil(  maxClose / 60 ) );

        // Generate hour columns, e.g. ['06:00','07:00',...,'21:00'] (each = 1-hr slot start)
        let hours = [];
        for ( let h = startHour; h < endHour; h++ ) {
            hours.push( String(h).padStart(2,'0') + ':00' );
        }

        dates.forEach( function( date ) {
            let dateBookings = bookings[ date ] ?? {};
            let dateBlocks   = blocks[ date ]   ?? {};

            // ── Build card per date ──────────────────────────────────────────
            let headerHtml = `<h6 class="mb-3 text-primary">${ fmtDate( date ) }</h6>`;

            // Hour header row
            let hdrCells = hours.map( h => `<th class="hour-header">${ h.substring(0,2) }</th>` ).join('');
            let thead = `<thead><tr><th class="court-label">Court</th>${ hdrCells }</tr></thead>`;

            // Court rows
            let tbody = '<tbody>';
            courts.forEach( function( court ) {
                let cId         = court.id;
                let cap         = court.capacity;
                let openMins    = toMins( court.open_time );
                let closeMins   = toMins( court.close_time );
                let cBookings   = dateBookings[ cId ] ?? [];
                let cBlocks     = dateBlocks[ cId ]   ?? [];
                let courtLabel  = `<span title="${ court.venue_name } · ${ court.sport_name }">${ court.name }</span>`;

                let cells = hours.map( function( h ) {
                    let slotStart = h;
                    let slotEnd   = String( parseInt(h) + 1 ).padStart(2,'0') + ':00';
                    let sMins     = toMins( slotStart );
                    let eMins     = toMins( slotEnd );

                    // Outside operating hours
                    if ( sMins < openMins || eMins > closeMins ) {
                        return `<td><span class="avail-cell av-closed" title="Closed"></span></td>`;
                    }

                    // Calendar blocked?
                    let blockedMatch = cBlocks.find( b => overlaps( slotStart, slotEnd, b.start_time, b.end_time ) );
                    if ( blockedMatch ) {
                        let blockUrl = '{{ route( 'admin.court_calendar.edit' ) }}?id=' + blockedMatch.encrypted_id;
                        return `<td><a href="${ blockUrl }" class="avail-cell av-blocked d-block" title="Blocked — click to edit calendar block" style="text-decoration:none;"></a></td>`;
                    }

                    // Sum participants for overlapping active bookings
                    let overlapping = cBookings.filter( b => overlaps( slotStart, slotEnd, b.start_time, b.end_time ) );
                    let totalPax    = overlapping.reduce( (s, b) => s + ( b.participants ?? 1 ), 0 );
                    let firstBooking = overlapping.length ? overlapping[0] : null;
                    let isComplete   = firstBooking && firstBooking.group?.status == {{ \App\Models\CourtBooking::STATUS_COMPLETE }};
                    let bookUrl      = ( firstBooking && !isComplete ) ? '{{ route( 'admin.court_booking.edit' ) }}?id=' + ( firstBooking.group_encrypted_id ?? '' ) : null;

                    if ( cap > 1 ) {
                        // Shared/capacity court
                        if ( totalPax === 0 ) {
                            return `<td><span class="avail-cell av-available" title="Available (${ cap } spots)"><span class="cell-text">${ cap }</span></span></td>`;
                        } else if ( totalPax < cap ) {
                            let left = cap - totalPax;
                            let title = `${ totalPax }/${ cap } spots taken · ${ left } left${ isComplete ? '' : ' — click to edit booking' }`;
                            return bookUrl
                                ? `<td><a href="${ bookUrl }" class="avail-cell av-partial d-block" title="${ title }" style="text-decoration:none;"><span class="cell-text">${ left }/${ cap }</span></a></td>`
                                : `<td><span class="avail-cell av-partial" title="${ title }"><span class="cell-text">${ left }/${ cap }</span></span></td>`;
                        } else {
                            let title = `Full (${ totalPax }/${ cap })${ isComplete ? '' : ' — click to edit booking' }`;
                            return bookUrl
                                ? `<td><a href="${ bookUrl }" class="avail-cell av-booked d-block" title="${ title }" style="text-decoration:none;"><span class="cell-text">Full</span></a></td>`
                                : `<td><span class="avail-cell av-booked" title="${ title }"><span class="cell-text">Full</span></span></td>`;
                        }
                    } else {
                        // Exclusive court
                        if ( overlapping.length === 0 ) {
                            return `<td><span class="avail-cell av-available" title="Available"></span></td>`;
                        } else {
                            let title = isComplete ? 'Completed' : 'Booked — click to edit booking';
                            return bookUrl
                                ? `<td><a href="${ bookUrl }" class="avail-cell av-booked d-block" title="${ title }" style="text-decoration:none;"></a></td>`
                                : `<td><span class="avail-cell av-booked" title="${ title }"></span></td>`;
                        }
                    }
                } ).join('');

                tbody += `<tr><td class="court-label">${ courtLabel }</td>${ cells }</tr>`;
            } );
            tbody += '</tbody>';

            let table = `<div class="table-responsive"><table class="avail-grid w-auto">${ thead }${ tbody }</table></div>`;

            $( '#{{ $st }}_output' ).append(`
                <div class="card mb-3">
                    <div class="card-inner">
                        ${ headerHtml }
                        ${ table }
                    </div>
                </div>`);
        } );
    }

} );
</script>
