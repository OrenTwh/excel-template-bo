<?php $court_booking_create = 'court_booking_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => 'Court Booking' ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_court_id" class="col-sm-3 col-form-label">{{ __( 'Court' ) }}</label>
                    <div class="col-sm-9">
                        <select id="{{ $court_booking_create }}_court_id" style="width:100%">
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

                <div class="mb-3 row" id="{{ $court_booking_create }}_court_info" style="display:none;">
                    <div class="col-sm-9 offset-sm-3">
                        <div class="alert alert-info py-2 mb-0">
                            <div class="d-flex flex-wrap gap-3 small">
                                <span><strong>{{ __( 'Slot Duration' ) }}:</strong> <span id="{{ $court_booking_create }}_slot_duration_text">-</span></span>
                                <span><strong>{{ __( 'Price / Slot' ) }}:</strong> <span id="{{ $court_booking_create }}_price_per_slot_text">-</span></span>
                                <span><strong>{{ __( 'Operating Hours' ) }}:</strong> <span id="{{ $court_booking_create }}_operating_hours_text">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_user_id" class="col-sm-3 col-form-label">{{ __( 'court_booking.user' ) }}</label>
                    <div class="col-sm-9">
                        <select id="{{ $court_booking_create }}_user_id" style="width:100%">
                            <option value="">{{ __( 'Select User' ) }}</option>
                            @foreach($data['users'] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_booking_date" class="col-sm-3 col-form-label">{{ __( 'Booking Date' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_create }}_booking_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_start_time" class="col-sm-3 col-form-label">{{ __( 'Start Time' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_create }}_start_time" placeholder="{{ __( 'Select court first' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_end_time" class="col-sm-3 col-form-label">{{ __( 'End Time' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $court_booking_create }}_end_time" placeholder="{{ __( 'Select court first' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_slots" class="col-sm-3 col-form-label">{{ __( 'Number of Slots' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="{{ $court_booking_create }}_slots" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_total_price" class="col-sm-3 col-form-label">{{ __( 'Total Price' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="{{ $court_booking_create }}_total_price" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_status" class="col-sm-3 col-form-label">{{ __( 'Booking Status' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $court_booking_create }}_status">
                            @foreach($data['booking_status'] as $key => $status)
                                <option value="{{ $key }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_payment_status" class="col-sm-3 col-form-label">{{ __( 'Payment Status' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $court_booking_create }}_payment_status">
                            @foreach($data['payment_status'] as $key => $status)
                                <option value="{{ $key }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $court_booking_create }}_notes" class="col-sm-3 col-form-label">{{ __( 'Notes' ) }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="min-height: 80px;" id="{{ $court_booking_create }}_notes"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $court_booking_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $court_booking_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $court_booking_create }}';
        let courtSlotInfo = {};
        let startPicker = null;
        let endPicker   = null;

        // ── Select2: Court ────────────────────────────────────────────────────
        $( fc + '_court_id' ).select2( {
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
        $( fc + '_user_id' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '{{ __( 'Select User' ) }}',
            allowClear: true,
        } );

        // ── Booking date picker ───────────────────────────────────────────────
        $( fc + '_booking_date' ).flatpickr( {
            dateFormat: "Y-m-d",
            minDate: "today",
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

            startPicker = $( fc + '_start_time' ).flatpickr( Object.assign( {}, base, options, {
                onClose: calculateSlots,
            } ) );

            endPicker = $( fc + '_end_time' ).flatpickr( Object.assign( {}, base, options, {
                onClose: calculateSlots,
            } ) );
        }

        // Default pickers (no constraints)
        initTimePickers( {} );

        // ── Court change: update info panel & reinit pickers ─────────────────
        $( fc + '_court_id' ).on( 'change', function() {
            let selected     = $( fc + '_court_id option:selected' );
            let slotDuration = parseInt( selected.data( 'slot-duration' ) || 0 );
            let pricePerSlot = parseFloat( selected.data( 'price-per-slot' ) || 0 );
            let openTime     = selected.data( 'open-time' ) || '';
            let closeTime    = selected.data( 'close-time' ) || '';

            // Clear time fields whenever court changes
            $( fc + '_start_time' ).val( '' );
            $( fc + '_end_time' ).val( '' );
            $( fc + '_slots' ).val( '' );
            $( fc + '_total_price' ).val( '' );

            if ( slotDuration && openTime && closeTime ) {
                courtSlotInfo = { slotDuration, pricePerSlot, openTime, closeTime };

                $( fc + '_slot_duration_text' ).text( slotDuration + ' min' );
                $( fc + '_price_per_slot_text' ).text( 'RM ' + pricePerSlot.toFixed( 2 ) );
                $( fc + '_operating_hours_text' ).text( openTime + ' – ' + closeTime );
                $( fc + '_court_info' ).slideDown( 150 );

                initTimePickers( {
                    minTime: openTime,
                    maxTime: closeTime,
                    minuteIncrement: slotDuration,
                } );
            } else {
                courtSlotInfo = {};
                $( fc + '_court_info' ).slideUp( 150 );
                initTimePickers( {} );
            }
        } );

        // ── Slot & price calculation ──────────────────────────────────────────
        function calculateSlots() {
            let startTime = $( fc + '_start_time' ).val();
            let endTime   = $( fc + '_end_time' ).val();

            if ( !startTime || !endTime || !courtSlotInfo.slotDuration ) {
                $( fc + '_slots' ).val( '' );
                $( fc + '_total_price' ).val( '' );
                return;
            }

            let start       = new Date( '2000-01-01 ' + startTime );
            let end         = new Date( '2000-01-01 ' + endTime );
            let diffMinutes = ( end - start ) / 1000 / 60;

            if ( diffMinutes > 0 ) {
                let slots = Math.floor( diffMinutes / courtSlotInfo.slotDuration );
                $( fc + '_slots' ).val( slots );
                $( fc + '_total_price' ).val( ( slots * courtSlotInfo.pricePerSlot ).toFixed( 2 ) );
            } else {
                $( fc + '_slots' ).val( '' );
                $( fc + '_total_price' ).val( '' );
            }
        }

        // ── Cancel ────────────────────────────────────────────────────────────
        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.court_booking.index' ) }}';
        } );

        // ── Submit ────────────────────────────────────────────────────────────
        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'court_id',       $( fc + '_court_id' ).val() );
            formData.append( 'user_id',        $( fc + '_user_id' ).val() );
            formData.append( 'booking_date',   $( fc + '_booking_date' ).val() );
            formData.append( 'start_time',     $( fc + '_start_time' ).val() );
            formData.append( 'end_time',       $( fc + '_end_time' ).val() );
            formData.append( 'status',         $( fc + '_status' ).val() );
            formData.append( 'payment_status', $( fc + '_payment_status' ).val() );
            formData.append( 'notes',          $( fc + '_notes' ).val() );
            formData.append( '_token',         '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court_booking.createCourtBooking' ) }}',
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
