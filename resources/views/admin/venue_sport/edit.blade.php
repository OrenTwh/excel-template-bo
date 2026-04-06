<?php $vs_edit = 'vs_edit'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => __( 'template.venue_sports' ) ] ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Context banner (filled on load) ────────────────────────────────────── --}}
<div class="alert alert-light border mb-4 d-flex align-items-center" id="{{ $vs_edit }}_context_banner">
    <em class="icon ni ni-layers-fill fs-3 text-primary"></em>
    <div>
        <div class="small text-muted mb-1">Venue Sport Configuration</div>
        <div class="d-flex align-items-center gap-2">
            <strong id="{{ $vs_edit }}_banner_venue">—</strong>
            <span class="text-muted">›</span>
            <strong id="{{ $vs_edit }}_banner_sport">—</strong>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">

        {{-- ── Read-only context ──────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Linked Records</h6>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Venue</label>
            <div class="col-sm-9">
                <input type="text" class="form-control bg-light" id="{{ $vs_edit }}_venue_name" readonly>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Sport</label>
            <div class="col-sm-9">
                <input type="text" class="form-control bg-light" id="{{ $vs_edit }}_sport_name" readonly>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Schedule & Pricing ───────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Schedule &amp; Pricing</h6>

        {{-- Pricing Method dropdown --}}
        <div class="mb-3 row">
            <label for="{{ $vs_edit }}_pricing_method" class="col-sm-3 col-form-label">Pricing Method <span class="text-danger">*</span></label>
            <div class="col-sm-5">
                <select class="form-select" id="{{ $vs_edit }}_pricing_method">
                    <option value="">— Select Pricing Method —</option>
                    <option value="per_slot">Per Slot (fixed time block)</option>
                    <option value="per_hour">Per Hour</option>
                    <option value="per_person">Per Person (headcount × rate)</option>
                    <option value="per_night">Per Night</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- per_slot / per_hour --}}
        <div class="mb-3 row vs-field-slot" style="display:none;">
            <label for="{{ $vs_edit }}_slot_duration" class="col-sm-3 col-form-label">Slot Duration</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="number" min="15" step="15" class="form-control" id="{{ $vs_edit }}_slot_duration">
                    <span class="input-group-text">min</span>
                </div>
                <div class="form-text">Each bookable time block. Use multiples of 15.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row vs-field-slot" style="display:none;">
            <label for="{{ $vs_edit }}_price_per_slot" class="col-sm-3 col-form-label" id="{{ $vs_edit }}_price_per_slot_label">Price / Slot</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_edit }}_price_per_slot">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- per_person --}}
        <div class="mb-3 row vs-field-person" style="display:none;">
            <label for="{{ $vs_edit }}_price_per_person" class="col-sm-3 col-form-label">Price / Person</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_edit }}_price_per_person">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- per_night --}}
        <div class="mb-3 row vs-field-night" style="display:none;">
            <label for="{{ $vs_edit }}_price_per_night" class="col-sm-3 col-form-label">Price / Night</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_edit }}_price_per_night">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Operating Hours <span class="text-danger">*</span></label>
            <div class="col-sm-3 pe-sm-1 mb-2 mb-sm-0">
                <label class="form-label small text-muted mb-1">Opens</label>
                <input type="time" class="form-control" id="{{ $vs_edit }}_open_time">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3 ps-sm-1">
                <label class="form-label small text-muted mb-1">Closes</label>
                <input type="time" class="form-control" id="{{ $vs_edit }}_close_time">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3 d-flex align-items-end pb-1">
                <span class="badge bg-light text-dark border" id="{{ $vs_edit }}_slot_count" style="display:none !important;"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Operating Days <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <div class="btn-group btn-group-sm mb-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_edit }}_days_all">All</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_edit }}_days_weekdays">Weekdays</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_edit }}_days_weekends">Weekends</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_edit }}_days_none">Clear</button>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-1" id="{{ $vs_edit }}_operating_days">
                    @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
                        <div class="form-check">
                            <input class="form-check-input operating-day-check" type="checkbox" value="{{ $day }}" id="{{ $vs_edit }}_day_{{ $day }}">
                            <label class="form-check-label" for="{{ $vs_edit }}_day_{{ $day }}">{{ ucfirst( $day ) }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="invalid-feedback d-block" id="{{ $vs_edit }}_operating_days_error"></div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $vs_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>

    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vc = '#{{ $vs_edit }}';

        // ── Pricing method toggling ───────────────────────────────────────────
        function applyPricingMethod( method ) {
            $( '.vs-field-slot, .vs-field-person, .vs-field-night' ).hide();
            if ( method === 'per_slot' || method === 'per_hour' ) {
                $( '.vs-field-slot' ).show();
                $( vc + '_price_per_slot_label' ).text( method === 'per_hour' ? 'Price / Hour' : 'Price / Slot' );
            } else if ( method === 'per_person' ) {
                $( '.vs-field-person' ).show();
            } else if ( method === 'per_night' ) {
                $( '.vs-field-night' ).show();
            }
        }

        $( vc + '_pricing_method' ).on( 'change', function() {
            applyPricingMethod( $( this ).val() );
        } );

        // ── Day quick-select buttons ──────────────────────────────────────────
        let weekdays = ['monday','tuesday','wednesday','thursday','friday'];
        let weekends = ['saturday','sunday'];
        let allDays  = weekdays.concat( weekends );

        function setDays( days ) {
            $( '.operating-day-check' ).prop( 'checked', false );
            days.forEach( function( d ) { $( '#{{ $vs_edit }}_day_' + d ).prop( 'checked', true ); } );
        }

        $( vc + '_days_all' ).click( function() { setDays( allDays ); } );
        $( vc + '_days_weekdays' ).click( function() { setDays( weekdays ); } );
        $( vc + '_days_weekends' ).click( function() { setDays( weekends ); } );
        $( vc + '_days_none' ).click( function() { setDays( [] ); } );

        // ── Slot count display ────────────────────────────────────────────────
        function updateSlotCount() {
            let openVal     = $( vc + '_open_time' ).val();
            let closeVal    = $( vc + '_close_time' ).val();
            let durationVal = parseInt( $( vc + '_slot_duration' ).val() );
            let badge       = $( vc + '_slot_count' );

            if ( openVal && closeVal && durationVal > 0 ) {
                let [oh, om] = openVal.split(':').map( Number );
                let [ch, cm] = closeVal.split(':').map( Number );
                let totalMin  = (ch * 60 + cm) - (oh * 60 + om);
                if ( totalMin > 0 ) {
                    let slots = Math.floor( totalMin / durationVal );
                    badge.text( slots + ' slot' + ( slots !== 1 ? 's' : '' ) + '/day' ).css( 'display', 'inline-block' );
                    return;
                }
            }
            badge.hide();
        }

        $( vc + '_open_time, ' + vc + '_close_time, ' + vc + '_slot_duration' ).on( 'change input', updateSlotCount );

        // ── Load data ─────────────────────────────────────────────────────────
        getVenueSport();

        function getVenueSport() {
            $.ajax( {
                url: '{{ route( 'admin.venue_sport.oneVenueSport' ) }}',
                type: 'POST',
                data: { 'id': '{{ request()->id }}', '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    let venueName = response.venue ? response.venue.name : '';
                    let sportName = response.sport ? response.sport.name : '';

                    $( vc + '_venue_name' ).val( venueName );
                    $( vc + '_sport_name' ).val( sportName );
                    let pm = response.pricing_method || ( response.sport ? response.sport.pricing_method : '' ) || '';
                    $( vc + '_pricing_method' ).val( pm );
                    applyPricingMethod( pm );

                    $( vc + '_slot_duration' ).val( response.slot_duration );
                    $( vc + '_price_per_slot' ).val( response.price_per_slot );
                    $( vc + '_price_per_person' ).val( response.price_per_person );
                    $( vc + '_price_per_night' ).val( response.price_per_night );
                    $( vc + '_open_time' ).val( response.open_time ? response.open_time.substring( 0, 5 ) : '' );
                    $( vc + '_close_time' ).val( response.close_time ? response.close_time.substring( 0, 5 ) : '' );

                    // Context banner
                    $( '#{{ $vs_edit }}_banner_venue' ).text( venueName );
                    $( '#{{ $vs_edit }}_banner_sport' ).text( sportName );

                    $( '.operating-day-check' ).prop( 'checked', false );
                    if ( response.operating_days && response.operating_days.length ) {
                        response.operating_days.forEach( function( day ) {
                            $( '#{{ $vs_edit }}_day_' + day ).prop( 'checked', true );
                        } );
                    }

                    updateSlotCount();
                },
                error: function() {
                    window.location.href = '{{ route( 'admin.module_parent.venue_sport.index' ) }}';
                }
            } );
        }

        // ── Submit ────────────────────────────────────────────────────────────
        $( vc + '_submit' ).click( function() {

            resetInputValidation();
            $( vc + '_operating_days_error' ).text( '' );

            let operatingDays = [];
            $( '.operating-day-check:checked' ).each( function() {
                operatingDays.push( $( this ).val() );
            } );

            if ( operatingDays.length === 0 ) {
                $( vc + '_operating_days_error' ).text( '{{ __( 'The operating days field is required.' ) }}' );
                return;
            }

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id',               '{{ request()->id }}' );
            formData.append( 'pricing_method',   $( vc + '_pricing_method' ).val() );
            formData.append( 'slot_duration',    $( vc + '_slot_duration' ).val() );
            formData.append( 'price_per_slot',   $( vc + '_price_per_slot' ).val() );
            formData.append( 'price_per_person', $( vc + '_price_per_person' ).val() );
            formData.append( 'price_per_night',  $( vc + '_price_per_night' ).val() );
            formData.append( 'open_time',      $( vc + '_open_time' ).val() );
            formData.append( 'close_time',     $( vc + '_close_time' ).val() );
            operatingDays.forEach( function( day ) {
                formData.append( 'operating_days[]', day );
            } );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.venue.updateVenueSport' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            if ( key === 'operating_days' ) {
                                $( vc + '_operating_days_error' ).text( value );
                            } else {
                                $( vc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }
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
