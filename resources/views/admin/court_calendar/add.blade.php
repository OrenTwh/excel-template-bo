<?php $cc = 'court_calendar_create'; ?>

{{-- Build courts JSON for cascading --}}
@php
    $usersJson  = $data['users']->map( fn( $u ) => [ 'id' => $u->id, 'label' => $u->fullname . ' (' . $u->email . ')' ] )->values();
    $courtsJson = $data['courts']->map( fn( $c ) => [
        'id'              => $c->id,
        'name'            => $c->name,
        'venue_id'        => $c->venueSport->venue->id    ?? null,
        'venue_name'      => $c->venueSport->venue->name   ?? '—',
        'sport_id'        => $c->venueSport->sport->id    ?? null,
        'sport_name'      => $c->venueSport->sport->name   ?? '—',
        'open_time'       => $c->venueSport->open_time     ?? null,
        'close_time'      => $c->venueSport->close_time    ?? null,
        'operating_days'  => $c->venueSport->operating_days ?? [],
        'slot_duration'   => $c->venueSport->slot_duration  ?? 60,
        'price_per_slot'  => $c->venueSport->price_per_slot ?? null,
        'price_per_hour'  => $c->price_per_hour,
    ] )->values();
@endphp

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Add Court Schedule</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> Back to List
            </a>
        </div>
    </div>
</div>

{{-- ── Step 1: Court Selection ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Step 1 — Select Court</h6>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Sport <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_sport_select">
                    <option value="">— Select Sport —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cc }}_venue_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Venue <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_venue_select">
                    <option value="">— Select Venue —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cc }}_court_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Court <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_court_id">
                    <option value="">— Select Court —</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Context card --}}
        <div id="{{ $cc }}_context_card" class="alert alert-light border mt-1" style="display:none;">
            <div class="row g-3">
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Operating Hours</div>
                    <strong id="{{ $cc }}_ctx_hours">—</strong>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Slot Duration</div>
                    <strong id="{{ $cc }}_ctx_slot">—</strong>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Base Price / Slot</div>
                    <strong id="{{ $cc }}_ctx_price">—</strong>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Court Price / hr</div>
                    <strong id="{{ $cc }}_ctx_court_price">—</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Step 2: Schedule Entry ────────────────────────────────────────────────── --}}
<div class="card mb-4" id="{{ $cc }}_schedule_card" style="display:none;">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Step 2 — Schedule Entry</h6>

        {{-- Specific date --}}
        <div class="mb-3 row">
            <label for="{{ $cc }}_date" class="col-sm-3 col-form-label">Date <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <input type="text" class="form-control" id="{{ $cc }}_date" placeholder="YYYY-MM-DD">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Time range --}}
        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Time Range <span class="text-danger">*</span></label>
            <div class="col-sm-3 pe-sm-1 mb-2 mb-sm-0">
                <label class="form-label small text-muted mb-1">Start</label>
                <input type="text" class="form-control" id="{{ $cc }}_start_time">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3 ps-sm-1">
                <label class="form-label small text-muted mb-1">End</label>
                <input type="text" class="form-control" id="{{ $cc }}_end_time">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-3">Availability &amp; Pricing</h6>

        {{-- is_available --}}
        <div class="mb-3 row d-none">
            <label class="col-sm-3 col-form-label">Slot Available?</label>
            <div class="col-sm-9 d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="{{ $cc }}_is_available" checked>
                    <label class="form-check-label" for="{{ $cc }}_is_available">Available</label>
                </div>
                <span class="text-muted small">Turn off to block this slot (e.g. maintenance, holiday).</span>
            </div>
        </div>

        {{-- Unavailability reason — shown when is_available is OFF --}}
        <div class="mb-3 row" id="{{ $cc }}_reason_row" style="display:none;">
            <label for="{{ $cc }}_unavailability_reason" class="col-sm-3 col-form-label">Reason</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $cc }}_unavailability_reason" placeholder="e.g. Maintenance, Public holiday...">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- Special price --}}
        <div class="mb-3 row">
            <label for="{{ $cc }}_special_price" class="col-sm-3 col-form-label">Special Price</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" step="0.01" min="0" class="form-control" id="{{ $cc }}_special_price" placeholder="Leave blank to use default">
                </div>
                <div class="form-text">Overrides the court's default price for this slot only.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-3">Event Settings</h6>

        <input type="hidden" id="{{ $cc }}_is_event" value="1">

        <div id="{{ $cc }}_event_fields">
            <div class="mb-3 row">
                <label for="{{ $cc }}_event_title" class="col-sm-3 col-form-label">Event Title <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="{{ $cc }}_event_title" placeholder="e.g. Saturday Badminton Open">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $cc }}_event_description" class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="{{ $cc }}_event_description" rows="3" placeholder="Optional event details..."></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $cc }}_max_participants" class="col-sm-3 col-form-label">Max Participants</label>
                <div class="col-sm-4">
                    <input type="number" min="1" class="form-control" id="{{ $cc }}_max_participants" placeholder="Leave blank for unlimited">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $cc }}_price_per_participant" class="col-sm-3 col-form-label">Price per Participant</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" step="0.01" min="0" class="form-control" id="{{ $cc }}_price_per_participant" placeholder="0.00 = Free">
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $cc }}_external_form_link" class="col-sm-3 col-form-label">External Form Link</label>
                <div class="col-sm-9">
                    <input type="url" class="form-control" id="{{ $cc }}_external_form_link" placeholder="https://forms.google.com/...">
                    <div class="form-text">Optional redirect link to an external registration form (e.g. Google Form).</div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="overline-title text-primary-alt mb-3">Organiser</h6>

        <div class="mb-3 row">
            <label for="{{ $cc }}_created_by_user_id" class="col-sm-3 col-form-label">On Behalf of User</label>
            <div class="col-sm-6">
                <select class="form-select" id="{{ $cc }}_created_by_user_id">
                    <option value="">— Admin (no user) —</option>
                </select>
                <div class="form-text">Leave blank to create as an admin event. Select a user to create on their behalf.</div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="btn btn-outline-secondary me-1">Cancel</a>
            <button id="{{ $cc }}_submit" type="button" class="btn btn-primary">Save Schedule</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $cc }}';

        // ── Build lookup from blade data ──────────────────────────────────────
        const courts = {!! json_encode( $courtsJson ) !!};
        const users  = {!! json_encode( $usersJson ) !!};

        // Populate user select
        let userSel = $( fc + '_created_by_user_id' );
        users.forEach( function( u ) {
            userSel.append( $( '<option>' ).val( u.id ).text( u.label ) );
        } );
        userSel.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: '— Admin (no user) —' } );

        // Group: sportId → [ {venueId, venueName, courts:[...]} ]
        let sportMap = {};
        courts.forEach( function( c ) {
            if ( !c.sport_id ) return;
            if ( !sportMap[ c.sport_id ] ) {
                sportMap[ c.sport_id ] = { name: c.sport_name, venues: {} };
            }
            if ( !sportMap[ c.sport_id ].venues[ c.venue_id ] ) {
                sportMap[ c.sport_id ].venues[ c.venue_id ] = { name: c.venue_name, courts: [] };
            }
            sportMap[ c.sport_id ].venues[ c.venue_id ].courts.push( c );
        } );

        // Populate Sport select
        let sportSel = $( fc + '_sport_select' );
        Object.keys( sportMap ).forEach( function( sid ) {
            sportSel.append( $( '<option>' ).val( sid ).text( sportMap[ sid ].name ) );
        } );
        sportSel.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( fc + '_venue_select' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( fc + '_court_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );

        // ── Sport → Venue cascade ─────────────────────────────────────────────
        sportSel.on( 'change', function() {
            let sid        = $( this ).val();
            let venueSel   = $( fc + '_venue_select' );
            let courtSel   = $( fc + '_court_id' );

            venueSel.empty().append( '<option value="">— Select Venue —</option>' );
            courtSel.empty().append( '<option value="">— Select Court —</option>' );
            $( fc + '_venue_row, ' + fc + '_court_row' ).hide();
            $( fc + '_context_card, ' + fc + '_schedule_card' ).hide();

            if ( !sid || !sportMap[ sid ] ) return;

            Object.keys( sportMap[ sid ].venues ).forEach( function( vid ) {
                venueSel.append( $( '<option>' ).val( vid ).text( sportMap[ sid ].venues[ vid ].name ) );
            } );
            $( fc + '_venue_row' ).show();
            venueSel.trigger( 'change' );
        } );

        // ── Venue → Court cascade ─────────────────────────────────────────────
        $( fc + '_venue_select' ).on( 'change', function() {
            let sid      = sportSel.val();
            let vid      = $( this ).val();
            let courtSel = $( fc + '_court_id' );

            courtSel.empty().append( '<option value="">— Select Court —</option>' );
            $( fc + '_court_row' ).hide();
            $( fc + '_context_card, ' + fc + '_schedule_card' ).hide();

            if ( !sid || !vid || !sportMap[ sid ] || !sportMap[ sid ].venues[ vid ] ) return;

            sportMap[ sid ].venues[ vid ].courts.forEach( function( c ) {
                courtSel.append( $( '<option>' ).val( c.id ).text( c.name ) );
            } );
            $( fc + '_court_row' ).show();
            courtSel.trigger( 'change' );
        } );

        // ── Court → context card + date picker ───────────────────────────────
        $( fc + '_court_id' ).on( 'change', function() {
            let cid = $( this ).val();
            $( fc + '_context_card, ' + fc + '_schedule_card' ).hide();
            if ( !cid ) return;

            let c = courts.find( function( x ) { return x.id == cid; } );
            if ( !c ) return;

            $( fc + '_ctx_hours' ).text( ( c.open_time ? c.open_time.substring(0,5) : '—' ) + ' – ' + ( c.close_time ? c.close_time.substring(0,5) : '—' ) );
            $( fc + '_ctx_slot' ).text( c.slot_duration + ' min' );
            $( fc + '_ctx_price' ).text( c.price_per_slot ? 'RM ' + parseFloat( c.price_per_slot ).toFixed(2) : '—' );
            $( fc + '_ctx_court_price' ).text( c.price_per_hour ? 'RM ' + parseFloat( c.price_per_hour ).toFixed(2) : '—' );
            $( fc + '_context_card' ).show();
            $( fc + '_schedule_card' ).show();

            // Update date picker to only allow operating days
            updateDatePicker( c.operating_days );
        } );


        // ── Available toggle ──────────────────────────────────────────────────
        $( fc + '_is_available' ).on( 'change', function() {
            if ( $( this ).is( ':checked' ) ) {
                $( this ).next( 'label' ).text( 'Available' );
                $( fc + '_reason_row' ).hide();
            } else {
                $( this ).next( 'label' ).text( 'Unavailable' );
                $( fc + '_reason_row' ).show();
            }
        } );

        // ── Flatpickr time pickers ────────────────────────────────────────────
        $( fc + '_start_time' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true } );
        $( fc + '_end_time' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true } );

        const dayNames = [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ];

        let fpDate = $( fc + '_date' ).flatpickr( {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disableMobile: true,
        } );

        function updateDatePicker( operatingDays ) {
            let current = $( fc + '_date' ).val();

            fpDate.destroy();
            fpDate = $( fc + '_date' ).flatpickr( {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                disableMobile: true,
                enable: operatingDays && operatingDays.length
                    ? [ function( date ) { return operatingDays.includes( dayNames[ date.getDay() ] ); } ]
                    : undefined,
                onReady: function( selectedDates, dateStr, instance ) {
                    // Restore previously selected date only if it's still valid
                    if ( current ) {
                        let d = new Date( current );
                        if ( !operatingDays || !operatingDays.length || operatingDays.includes( dayNames[ d.getDay() ] ) ) {
                            instance.setDate( current );
                        }
                    }
                }
            } );
        }

        // ── Submit ────────────────────────────────────────────────────────────
        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'court_id',     $( fc + '_court_id' ).val() );
            formData.append( 'is_recurring', 0 );
            formData.append( 'date',         $( fc + '_date' ).val() );

            formData.append( 'start_time', $( fc + '_start_time' ).val() );
            formData.append( 'end_time',   $( fc + '_end_time' ).val() );
            formData.append( 'is_available', $( fc + '_is_available' ).is( ':checked' ) ? 1 : 0 );

            let reason = $( fc + '_unavailability_reason' ).val();
            if ( reason ) formData.append( 'unavailability_reason', reason );

            let specialPrice = $( fc + '_special_price' ).val();
            if ( specialPrice ) formData.append( 'special_price', specialPrice );

            formData.append( 'is_event', 1 );
            formData.append( 'event_title',       $( fc + '_event_title' ).val() );
            formData.append( 'event_description', $( fc + '_event_description' ).val() );
            let maxP = $( fc + '_max_participants' ).val();
            if ( maxP ) formData.append( 'max_participants', maxP );
            let priceP = $( fc + '_price_per_participant' ).val();
            if ( priceP ) formData.append( 'price_per_participant', priceP );
            let extLink = $( fc + '_external_form_link' ).val();
            if ( extLink ) formData.append( 'external_form_link', extLink );

            let userId = $( fc + '_created_by_user_id' ).val();
            if ( userId ) formData.append( 'created_by_user_id', userId );

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court_calendar.createCourtCalendar' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function() {
                        window.location.href = '{{ route( 'admin.module_parent.court_calendar.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

    } );
</script>
