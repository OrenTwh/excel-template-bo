<?php $cc = 'court_calendar_edit'; ?>

@php
    $usersJson  = $data['users']->map( fn( $u ) => [ 'id' => $u->id, 'label' => $u->fullname . ' (' . $u->email . ')' ] )->values();
    $courtsJson = $data['courts']->map( fn( $c ) => [
        'id'             => $c->id,
        'name'           => $c->name,
        'venue_id'       => $c->venueSport->venue->id    ?? null,
        'venue_name'     => $c->venueSport->venue->name   ?? '—',
        'sport_id'       => $c->venueSport->sport->id    ?? null,
        'sport_name'     => $c->venueSport->sport->name   ?? '—',
        'open_time'      => $c->venueSport->open_time     ?? null,
        'close_time'     => $c->venueSport->close_time    ?? null,
        'operating_days' => $c->venueSport->operating_days ?? [],
        'slot_duration'  => $c->venueSport->slot_duration  ?? 60,
        'price_per_slot' => $c->venueSport->price_per_slot ?? null,
        'price_per_hour' => $c->price_per_hour,
    ] )->values();
@endphp

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Edit Court Schedule</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> Back to List
            </a>
        </div>
    </div>
</div>

{{-- ── Court Selection ──────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Court</h6>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Sport <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_sport_select">
                    <option value="">— Select Sport —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cc }}_venue_row">
            <label class="col-sm-3 col-form-label">Venue <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_venue_select">
                    <option value="">— Select Venue —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cc }}_court_row">
            <label class="col-sm-3 col-form-label">Court <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cc }}_court_id">
                    <option value="">— Select Court —</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

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

{{-- ── Schedule Entry ────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Schedule Entry</h6>

        <div class="mb-3 row">
            <label for="{{ $cc }}_date" class="col-sm-3 col-form-label">Date <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <input type="text" class="form-control" id="{{ $cc }}_date" placeholder="YYYY-MM-DD">
                <div class="invalid-feedback"></div>
            </div>
        </div>

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

        <div class="mb-3 row d-none">
            <label class="col-sm-3 col-form-label">Slot Available?</label>
            <div class="col-sm-9 d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="{{ $cc }}_is_available" checked>
                    <label class="form-check-label" for="{{ $cc }}_is_available">Available</label>
                </div>
                <span class="text-muted small">Turn off to block this slot.</span>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cc }}_reason_row" style="display:none;">
            <label for="{{ $cc }}_unavailability_reason" class="col-sm-3 col-form-label">Reason</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $cc }}_unavailability_reason" placeholder="e.g. Maintenance, Public holiday...">
                <div class="invalid-feedback"></div>
            </div>
        </div>

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

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Host as Event?</label>
            <div class="col-sm-9 d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="{{ $cc }}_is_event">
                    <label class="form-check-label" for="{{ $cc }}_is_event">Enable Event Mode</label>
                </div>
                <span class="text-muted small">Allow users to join this slot as a hosted event.</span>
            </div>
        </div>

        <div id="{{ $cc }}_event_fields" style="display:none;">
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
                <div class="form-text">Leave blank for an admin event. Select a user to assign or reassign the organiser.</div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="btn btn-outline-secondary me-1">Cancel</a>
            <button id="{{ $cc }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $cc }}';

        // ── Build lookup ──────────────────────────────────────────────────────
        const courts = {!! json_encode( $courtsJson ) !!};
        const users  = {!! json_encode( $usersJson ) !!};

        // Populate user select
        let userSel = $( fe + '_created_by_user_id' );
        users.forEach( function( u ) {
            userSel.append( $( '<option>' ).val( u.id ).text( u.label ) );
        } );
        userSel.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: '— Admin (no user) —' } );

        let sportMap = {};
        courts.forEach( function( c ) {
            if ( !c.sport_id ) return;
            if ( !sportMap[ c.sport_id ] ) sportMap[ c.sport_id ] = { name: c.sport_name, venues: {} };
            if ( !sportMap[ c.sport_id ].venues[ c.venue_id ] )
                sportMap[ c.sport_id ].venues[ c.venue_id ] = { name: c.venue_name, courts: [] };
            sportMap[ c.sport_id ].venues[ c.venue_id ].courts.push( c );
        } );

        // Populate Sport select
        let sportSel = $( fe + '_sport_select' );
        Object.keys( sportMap ).forEach( function( sid ) {
            sportSel.append( $( '<option>' ).val( sid ).text( sportMap[ sid ].name ) );
        } );

        sportSel.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( fe + '_venue_select' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( fe + '_court_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );

        // ── Cascade functions (same as add) ───────────────────────────────────
        sportSel.on( 'change', function() {
            let sid = $( this ).val();
            let vs  = $( fe + '_venue_select' );
            vs.empty().append( '<option value="">— Select Venue —</option>' );
            $( fe + '_court_id' ).empty().append( '<option value="">— Select Court —</option>' );
            $( fe + '_context_card' ).hide();
            if ( !sid || !sportMap[ sid ] ) return;
            Object.keys( sportMap[ sid ].venues ).forEach( function( vid ) {
                vs.append( $( '<option>' ).val( vid ).text( sportMap[ sid ].venues[ vid ].name ) );
            } );
            vs.trigger( 'change' );
        } );

        $( fe + '_venue_select' ).on( 'change', function() {
            let sid      = sportSel.val();
            let vid      = $( this ).val();
            let courtSel = $( fe + '_court_id' );
            courtSel.empty().append( '<option value="">— Select Court —</option>' );
            $( fe + '_context_card' ).hide();
            if ( !sid || !vid ) return;
            sportMap[ sid ].venues[ vid ].courts.forEach( function( c ) {
                courtSel.append( $( '<option>' ).val( c.id ).text( c.name ) );
            } );
            courtSel.trigger( 'change' );
        } );

        $( fe + '_court_id' ).on( 'change', function() {
            let cid = $( this ).val();
            $( fe + '_context_card' ).hide();
            if ( !cid ) return;
            let c = courts.find( function( x ) { return x.id == cid; } );
            if ( !c ) return;
            $( fe + '_ctx_hours' ).text( ( c.open_time ? c.open_time.substring(0,5) : '—' ) + ' – ' + ( c.close_time ? c.close_time.substring(0,5) : '—' ) );
            $( fe + '_ctx_slot' ).text( c.slot_duration + ' min' );
            $( fe + '_ctx_price' ).text( c.price_per_slot ? 'RM ' + parseFloat( c.price_per_slot ).toFixed(2) : '—' );
            $( fe + '_ctx_court_price' ).text( c.price_per_hour ? 'RM ' + parseFloat( c.price_per_hour ).toFixed(2) : '—' );
            $( fe + '_context_card' ).show();

            // Update date picker to only allow operating days
            updateDatePicker( c.operating_days );
        } );

        // Helper: restore cascade from a court_id
        function setCascadeFromCourtId( courtId ) {
            let c = courts.find( function( x ) { return x.id == courtId; } );
            if ( !c || !c.sport_id || !c.venue_id ) return;

            // Set sport
            sportSel.val( c.sport_id ).trigger( 'change.select2' );

            // Populate venues
            let vs = $( fe + '_venue_select' );
            vs.empty().append( '<option value="">— Select Venue —</option>' );
            if ( sportMap[ c.sport_id ] ) {
                Object.keys( sportMap[ c.sport_id ].venues ).forEach( function( vid ) {
                    vs.append( $( '<option>' ).val( vid ).text( sportMap[ c.sport_id ].venues[ vid ].name ) );
                } );
            }
            vs.val( c.venue_id ).trigger( 'change.select2' );

            // Populate courts
            let courtSel = $( fe + '_court_id' );
            courtSel.empty().append( '<option value="">— Select Court —</option>' );
            if ( sportMap[ c.sport_id ] && sportMap[ c.sport_id ].venues[ c.venue_id ] ) {
                sportMap[ c.sport_id ].venues[ c.venue_id ].courts.forEach( function( ct ) {
                    courtSel.append( $( '<option>' ).val( ct.id ).text( ct.name ) );
                } );
            }
            courtSel.val( courtId ).trigger( 'change' );
        }


        // ── Available toggle ──────────────────────────────────────────────────
        $( fe + '_is_available' ).on( 'change', function() {
            if ( $( this ).is( ':checked' ) ) {
                $( this ).next( 'label' ).text( 'Available' );
                $( fe + '_reason_row' ).hide();
            } else {
                $( this ).next( 'label' ).text( 'Unavailable' );
                $( fe + '_reason_row' ).show();
            }
        } );

        // ── Event toggle ──────────────────────────────────────────────────────
        $( fe + '_is_event' ).on( 'change', function() {
            if ( $( this ).is( ':checked' ) ) {
                $( fe + '_event_fields' ).slideDown( 150 );
            } else {
                $( fe + '_event_fields' ).slideUp( 150 );
            }
        } );

        // ── Flatpickr ─────────────────────────────────────────────────────────
        let fpStart = $( fe + '_start_time' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true } );
        let fpEnd   = $( fe + '_end_time' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true } );

        const dayNames = [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ];

        let fpDate = $( fe + '_date' ).flatpickr( {
            dateFormat: 'Y-m-d',
            disableMobile: true,
        } );

        function updateDatePicker( operatingDays, preserveDate ) {
            fpDate.destroy();
            fpDate = $( fe + '_date' ).flatpickr( {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                enable: operatingDays && operatingDays.length
                    ? [ function( date ) { return operatingDays.includes( dayNames[ date.getDay() ] ); } ]
                    : undefined,
                onReady: function( selectedDates, dateStr, instance ) {
                    if ( preserveDate ) {
                        let d = new Date( preserveDate );
                        if ( !operatingDays || !operatingDays.length || operatingDays.includes( dayNames[ d.getDay() ] ) ) {
                            instance.setDate( preserveDate );
                        }
                    }
                }
            } );
        }

        // ── Load existing record ──────────────────────────────────────────────
        getCourtCalendar();

        function getCourtCalendar() {
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.court_calendar.oneCourtCalendar' ) }}',
                type: 'POST',
                data: { 'id': '{{ request( 'id' ) }}', '_token': '{{ csrf_token() }}' },
                success: function( response ) {

                    // Restore cascade
                    setCascadeFromCourtId( response.court_id );

                    // Update date picker with this court's operating days before setting the date
                    let loadedCourt = courts.find( function( x ) { return x.id == response.court_id; } );
                    let loadedDate  = response.date ? response.date.substring( 0, 10 ) : null;
                    if ( loadedCourt ) {
                        updateDatePicker( loadedCourt.operating_days, loadedDate );
                    } else if ( loadedDate ) {
                        fpDate.setDate( loadedDate );
                    }


                    fpStart.setDate( response.start_time );
                    fpEnd.setDate( response.end_time );

                    $( fe + '_is_available' ).prop( 'checked', !!response.is_available ).trigger( 'change' );
                    $( fe + '_unavailability_reason' ).val( response.unavailability_reason || '' );
                    $( fe + '_special_price' ).val( response.special_price || '' );

                    // Event fields
                    $( fe + '_is_event' ).prop( 'checked', !!response.is_event ).trigger( 'change' );
                    if ( response.is_event ) {
                        $( fe + '_event_title' ).val( response.event_title || '' );
                        $( fe + '_event_description' ).val( response.event_description || '' );
                        $( fe + '_max_participants' ).val( response.max_participants || '' );
                        $( fe + '_price_per_participant' ).val( response.price_per_participant || '' );
                        $( fe + '_external_form_link' ).val( response.external_form_link || '' );
                    }

                    // Organiser
                    if ( response.created_by_user_id ) {
                        userSel.val( response.created_by_user_id ).trigger( 'change' );
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        // ── Submit ────────────────────────────────────────────────────────────
        $( fe + '_submit' ).click( function() {

            resetInputValidation();
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id',           '{{ request( 'id' ) }}' );
            formData.append( 'court_id',     $( fe + '_court_id' ).val() );
            formData.append( 'is_recurring', 0 );
            formData.append( 'date',         $( fe + '_date' ).val() );

            formData.append( 'start_time',  $( fe + '_start_time' ).val() );
            formData.append( 'end_time',    $( fe + '_end_time' ).val() );
            formData.append( 'is_available', $( fe + '_is_available' ).is( ':checked' ) ? 1 : 0 );

            let reason = $( fe + '_unavailability_reason' ).val();
            if ( reason ) formData.append( 'unavailability_reason', reason );

            let specialPrice = $( fe + '_special_price' ).val();
            if ( specialPrice ) formData.append( 'special_price', specialPrice );

            let isEvent = $( fe + '_is_event' ).is( ':checked' );
            formData.append( 'is_event', isEvent ? 1 : 0 );
            if ( isEvent ) {
                formData.append( 'event_title',           $( fe + '_event_title' ).val() );
                formData.append( 'event_description',     $( fe + '_event_description' ).val() );
                let maxP = $( fe + '_max_participants' ).val();
                if ( maxP ) formData.append( 'max_participants', maxP );
                let priceP = $( fe + '_price_per_participant' ).val();
                if ( priceP ) formData.append( 'price_per_participant', priceP );
                let extLink = $( fe + '_external_form_link' ).val();
                if ( extLink ) formData.append( 'external_form_link', extLink );
            }

            let userId = $( fe + '_created_by_user_id' ).val();
            if ( userId ) formData.append( 'created_by_user_id', userId );

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court_calendar.updateCourtCalendar' ) }}',
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
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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
