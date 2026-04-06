<?php $court_booking_edit = 'court_booking_edit'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => 'Court Booking' ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_booking_number" class="col-sm-3 col-form-label">{{ __( 'Booking Number' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_edit }}_booking_number" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_court_id" class="col-sm-3 col-form-label">{{ __( 'Court' ) }}</label>
                    <div class="col-sm-9">
                        <select id="{{ $court_booking_edit }}_court_id" style="width:100%">
                            <option value="">{{ __( 'Select Court' ) }}</option>
                            @foreach($data['courts'] as $court)
                                <option value="{{ $court->id }}"
                                    data-venue="{{ $court->venueSport->venue->name ?? '' }}"
                                    data-slot-duration="{{ $court->venueSport->slot_duration ?? 0 }}"
                                    data-price-per-slot="{{ $court->venueSport->price_per_slot ?? 0 }}"
                                    data-open-time="{{ $court->venueSport->open_time ?? '' }}"
                                    data-close-time="{{ $court->venueSport->close_time ?? '' }}"
                                >{{ $court->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row" id="{{ $court_booking_edit }}_court_info" style="display:none;">
                    <div class="col-sm-9 offset-sm-3">
                        <div class="alert alert-info py-2 mb-0">
                            <div class="d-flex flex-wrap gap-3 small">
                                <span><strong>{{ __( 'Slot Duration' ) }}:</strong> <span id="{{ $court_booking_edit }}_slot_duration_text">-</span></span>
                                <span><strong>{{ __( 'Price / Slot' ) }}:</strong> <span id="{{ $court_booking_edit }}_price_per_slot_text">-</span></span>
                                <span><strong>{{ __( 'Operating Hours' ) }}:</strong> <span id="{{ $court_booking_edit }}_operating_hours_text">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_user_id" class="col-sm-3 col-form-label">{{ __( 'court_booking.user' ) }}</label>
                    <div class="col-sm-9">
                        <select id="{{ $court_booking_edit }}_user_id" style="width:100%">
                            <option value="">{{ __( 'Select User' ) }}</option>
                            @foreach($data['users'] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_booking_date" class="col-sm-3 col-form-label">{{ __( 'Booking Date' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_edit }}_booking_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_start_time" class="col-sm-3 col-form-label">{{ __( 'Start Time' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_edit }}_start_time">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_end_time" class="col-sm-3 col-form-label">{{ __( 'End Time' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_edit }}_end_time">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_slots" class="col-sm-3 col-form-label">{{ __( 'Number of Slots' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="{{ $court_booking_edit }}_slots" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_total_price" class="col-sm-3 col-form-label">{{ __( 'Total Price' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="{{ $court_booking_edit }}_total_price" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_status" class="col-sm-3 col-form-label">{{ __( 'Booking Status' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $court_booking_edit }}_status">
                            @foreach($data['booking_status'] as $key => $status)
                                <option value="{{ $key }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_payment_status" class="col-sm-3 col-form-label">{{ __( 'Payment Status' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $court_booking_edit }}_payment_status">
                            @foreach($data['payment_status'] as $key => $status)
                                <option value="{{ $key }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_edit }}_notes" class="col-sm-3 col-form-label">{{ __( 'Notes' ) }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="min-height: 80px;" id="{{ $court_booking_edit }}_notes"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $court_booking_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $court_booking_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $court_booking_edit }}';
        let courtSlotInfo = {};
        let startPicker   = null;
        let endPicker     = null;

        // ── Select2: Court ────────────────────────────────────────────────────
        $( fe + '_court_id' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '{{ __( 'Select Court' ) }}',
            allowClear: true,
            matcher: function( params, data ) {
                if ( $.trim( params.term ) === '' ) return data;
                let term  = params.term.toLowerCase();
                let name  = ( data.text || '' ).toLowerCase();
                let venue = ( $( data.element ).data( 'venue' ) || '' ).toLowerCase();
                return ( name.indexOf( term ) > -1 || venue.indexOf( term ) > -1 ) ? data : null;
            },
            templateResult: function( data ) {
                if ( !data.id ) return data.text;
                let venue = $( data.element ).data( 'venue' );
                return venue
                    ? $( '<span>' + data.text + ' <small class="text-muted">(' + venue + ')</small></span>' )
                    : data.text;
            },
        } );

        // ── Select2: User ─────────────────────────────────────────────────────
        $( fe + '_user_id' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '{{ __( 'Select User' ) }}',
            allowClear: true,
        } );

        // ── Booking date picker ───────────────────────────────────────────────
        $( fe + '_booking_date' ).flatpickr( {
            dateFormat: "Y-m-d",
        } );

        // ── Helpers: init / destroy time pickers ─────────────────────────────
        function initTimePickers( options ) {
            if ( startPicker ) startPicker.destroy();
            if ( endPicker )   endPicker.destroy();

            let base = {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
            };

            startPicker = $( fe + '_start_time' ).flatpickr( Object.assign( {}, base, options, {
                onClose: calculateSlots,
            } ) );

            endPicker = $( fe + '_end_time' ).flatpickr( Object.assign( {}, base, options, {
                onClose: calculateSlots,
            } ) );
        }

        // Default pickers (no constraints until court is set)
        initTimePickers( {} );

        // ── Court change: update info panel & reinit pickers ─────────────────
        $( fe + '_court_id' ).on( 'change', function( e, skipClear ) {
            let selected     = $( fe + '_court_id option:selected' );
            let slotDuration = parseInt( selected.data( 'slot-duration' ) || 0 );
            let pricePerSlot = parseFloat( selected.data( 'price-per-slot' ) || 0 );
            let openTime     = selected.data( 'open-time' ) || '';
            let closeTime    = selected.data( 'close-time' ) || '';

            if ( !skipClear ) {
                $( fe + '_start_time' ).val( '' );
                $( fe + '_end_time' ).val( '' );
                $( fe + '_slots' ).val( '' );
                $( fe + '_total_price' ).val( '' );
            }

            if ( slotDuration && openTime && closeTime ) {
                courtSlotInfo = { slotDuration, pricePerSlot, openTime, closeTime };

                $( fe + '_slot_duration_text' ).text( slotDuration + ' min' );
                $( fe + '_price_per_slot_text' ).text( 'RM ' + pricePerSlot.toFixed( 2 ) );
                $( fe + '_operating_hours_text' ).text( openTime + ' – ' + closeTime );
                $( fe + '_court_info' ).slideDown( 150 );

                initTimePickers( {
                    minTime: openTime,
                    maxTime: closeTime,
                    minuteIncrement: slotDuration,
                } );
            } else {
                courtSlotInfo = {};
                $( fe + '_court_info' ).slideUp( 150 );
                initTimePickers( {} );
            }
        } );

        // ── Slot & price calculation ──────────────────────────────────────────
        function calculateSlots() {
            let startTime = $( fe + '_start_time' ).val();
            let endTime   = $( fe + '_end_time' ).val();

            if ( !startTime || !endTime || !courtSlotInfo.slotDuration ) {
                $( fe + '_slots' ).val( '' );
                $( fe + '_total_price' ).val( '' );
                return;
            }

            let start       = new Date( '2000-01-01 ' + startTime );
            let end         = new Date( '2000-01-01 ' + endTime );
            let diffMinutes = ( end - start ) / 1000 / 60;

            if ( diffMinutes > 0 ) {
                let slots = Math.floor( diffMinutes / courtSlotInfo.slotDuration );
                $( fe + '_slots' ).val( slots );
                $( fe + '_total_price' ).val( ( slots * courtSlotInfo.pricePerSlot ).toFixed( 2 ) );
            } else {
                $( fe + '_slots' ).val( '' );
                $( fe + '_total_price' ).val( '' );
            }
        }

        // ── Cancel ────────────────────────────────────────────────────────────
        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.court_booking.index' ) }}';
        } );

        // ── Submit ────────────────────────────────────────────────────────────
        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id',             '{{ request( 'id' ) }}' );
            formData.append( 'court_id',       $( fe + '_court_id' ).val() );
            formData.append( 'user_id',        $( fe + '_user_id' ).val() );
            formData.append( 'booking_date',   $( fe + '_booking_date' ).val() );
            formData.append( 'start_time',     $( fe + '_start_time' ).val() );
            formData.append( 'end_time',       $( fe + '_end_time' ).val() );
            formData.append( 'status',         $( fe + '_status' ).val() );
            formData.append( 'payment_status', $( fe + '_payment_status' ).val() );
            formData.append( 'notes',          $( fe + '_notes' ).val() );
            formData.append( '_token',         '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court_booking.updateCourtBooking' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function( event ) {
                        window.location.href = '{{ route( 'admin.module_parent.court_booking.index' ) }}';
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

        // ── Load booking data ─────────────────────────────────────────────────
        getCourtBooking();

        function getCourtBooking() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.court_booking.oneCourtBooking' ) }}',
                type: 'POST',
                data: {
                    'id':     '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    let firstItem = response.court_bookings && response.court_bookings[0];

                    $( fe + '_booking_number' ).val( response.booking_no );
                    $( fe + '_status' ).val( response.status );
                    $( fe + '_payment_status' ).val( response.payment_status );
                    $( fe + '_notes' ).val( firstItem ? firstItem.notes : '' );
                    $( fe + '_total_price' ).val( response.total_amount );

                    // Booking date
                    let dateFp = document.querySelector( fe + '_booking_date' )._flatpickr;
                    if ( dateFp ) dateFp.setDate( firstItem ? firstItem.booking_date : '' );

                    // User Select2
                    $( fe + '_user_id' ).val( response.user_id ).trigger( 'change' );

                    // Court Select2 — trigger with skipClear=true so times aren't wiped
                    $( fe + '_court_id' ).val( firstItem ? firstItem.court_id : '' ).trigger( 'change', [ true ] );

                    // Set times & recalculate after pickers are re-inited
                    setTimeout( function() {
                        if ( startPicker ) startPicker.setDate( firstItem ? firstItem.start_time : '' );
                        if ( endPicker )   endPicker.setDate( firstItem ? firstItem.end_time : '' );
                        calculateSlots();
                    }, 50 );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>
