<?php $vs_create = 'vs_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => __( 'template.venue_sports' ) ] ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">

        {{-- ── Venue & Sport ────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Venue &amp; Sport</h6>

        <div class="mb-3 row">
            <label for="{{ $vs_create }}_venue_id" class="col-sm-3 col-form-label">Venue <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $vs_create }}_venue_id" data-placeholder="Select a venue...">
                    <option value="">— Select Venue —</option>
                    @foreach( $data['venues'] as $venue )
                        <option value="{{ $venue->encrypted_id }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $vs_create }}_sport_id" class="col-sm-3 col-form-label">Sports <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $vs_create }}_sport_id" data-placeholder="Select sports..." multiple>
                    @foreach( $data['sports'] as $sport )
                        <option value="{{ $sport->id }}"
                            data-pricing-method="{{ $sport->pricing_method }}"
                            data-type="{{ $sport->type }}">{{ $sport->name }}</option>
                    @endforeach
                </select>
                <div class="form-text" id="{{ $vs_create }}_sport_hint" style="display:none;">
                    <em class="icon ni ni-info text-warning"></em> Sports already configured for this venue are hidden.
                </div>
                <div class="invalid-feedback d-block" id="{{ $vs_create }}_sport_id_error"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Schedule & Pricing ───────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Schedule &amp; Pricing</h6>

        {{-- Pricing Method dropdown --}}
        <div class="mb-3 row">
            <label for="{{ $vs_create }}_pricing_method" class="col-sm-3 col-form-label">Pricing Method <span class="text-danger">*</span></label>
            <div class="col-sm-5">
                <select class="form-select" id="{{ $vs_create }}_pricing_method">
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
            <label for="{{ $vs_create }}_slot_duration" class="col-sm-3 col-form-label">Slot Duration</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="number" min="15" step="15" class="form-control" id="{{ $vs_create }}_slot_duration" placeholder="e.g. 60">
                    <span class="input-group-text">min</span>
                </div>
                <div class="form-text">Each bookable time block. Use multiples of 15 (e.g. 30, 60, 90).</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row vs-field-slot" style="display:none;">
            <label for="{{ $vs_create }}_price_per_slot" class="col-sm-3 col-form-label" id="{{ $vs_create }}_price_per_slot_label">Price / Slot</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_create }}_price_per_slot" placeholder="0.00">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- per_person --}}
        <div class="mb-3 row vs-field-person" style="display:none;">
            <label for="{{ $vs_create }}_price_per_person" class="col-sm-3 col-form-label">Price / Person</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_create }}_price_per_person" placeholder="0.00">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{-- per_night --}}
        <div class="mb-3 row vs-field-night" style="display:none;">
            <label for="{{ $vs_create }}_price_per_night" class="col-sm-3 col-form-label">Price / Night</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" min="0" step="0.01" class="form-control" id="{{ $vs_create }}_price_per_night" placeholder="0.00">
                </div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Operating Hours <span class="text-danger">*</span></label>
            <div class="col-sm-3 pe-sm-1 mb-2 mb-sm-0">
                <label class="form-label small text-muted mb-1">Opens</label>
                <input type="time" class="form-control" id="{{ $vs_create }}_open_time">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3 ps-sm-1">
                <label class="form-label small text-muted mb-1">Closes</label>
                <input type="time" class="form-control" id="{{ $vs_create }}_close_time">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3 d-flex align-items-end pb-1">
                <span class="badge bg-light text-dark border" id="{{ $vs_create }}_slot_count" style="display:none !important;"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Operating Days <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                {{-- Quick-select shortcuts --}}
                <div class="btn-group btn-group-sm mb-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_create }}_days_all">All</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_create }}_days_weekdays">Weekdays</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_create }}_days_weekends">Weekends</button>
                    <button type="button" class="btn btn-outline-secondary" id="{{ $vs_create }}_days_none">Clear</button>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-1" id="{{ $vs_create }}_operating_days">
                    @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
                        <div class="form-check">
                            <input class="form-check-input operating-day-check" type="checkbox" value="{{ $day }}" id="{{ $vs_create }}_day_{{ $day }}">
                            <label class="form-check-label" for="{{ $vs_create }}_day_{{ $day }}">{{ ucfirst( $day ) }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="invalid-feedback d-block" id="{{ $vs_create }}_operating_days_error"></div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $vs_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>

    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vc = '#{{ $vs_create }}';

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

        // ── Select2 ───────────────────────────────────────────────────────────
        $( vc + '_venue_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( vc + '_sport_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true, multiple: true } );

        // ── When venue changes: hide already-assigned sports ──────────────────
        $( vc + '_venue_id' ).on( 'change', function() {
            let venueEncId = $( this ).val();

            // Restore all sport options
            $( vc + '_sport_id option' ).show().prop( 'disabled', false );

            if ( !venueEncId ) {
                $( vc + '_sport_hint' ).hide();
                $( vc + '_sport_id' ).val( [] ).trigger( 'change' );
                return;
            }

            $.ajax( {
                url: '{{ route( 'admin.venue.allVenueSports' ) }}',
                type: 'POST',
                data: { 'venue_id': venueEncId, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    if ( response.venue_sports && response.venue_sports.length ) {
                        let usedIds = response.venue_sports.map( function( vs ) {
                            return vs.sport ? vs.sport.id.toString() : null;
                        } ).filter( Boolean );

                        usedIds.forEach( function( sportId ) {
                            $( vc + '_sport_id option[value="' + sportId + '"]' ).prop( 'disabled', true );
                        } );

                        $( vc + '_sport_hint' ).show();
                    } else {
                        $( vc + '_sport_hint' ).hide();
                    }

                    // Deselect any already-assigned sports from current selection
                    let selected = $( vc + '_sport_id' ).val() || [];
                    let filtered = selected.filter( function( id ) {
                        return !$( vc + '_sport_id option[value="' + id + '"]' ).prop( 'disabled' );
                    } );
                    $( vc + '_sport_id' ).val( filtered ).trigger( 'change' );
                }
            } );
        } );

        // ── When sport selection changes: pre-fill pricing method if unambiguous
        $( vc + '_sport_id' ).on( 'change', function() {
            // Only auto-fill if the dropdown hasn't been manually set yet
            if ( $( vc + '_pricing_method' ).val() ) return;

            let selected = $( vc + '_sport_id' ).find( 'option:selected' );
            let methods  = [];
            selected.each( function() {
                let m = $( this ).attr( 'data-pricing-method' );
                if ( m && !methods.includes( m ) ) methods.push( m );
            } );

            // Pre-select only when all selected sports share the same pricing method
            if ( methods.length === 1 ) {
                $( vc + '_pricing_method' ).val( methods[0] ).trigger( 'change' );
            }
        } );

        // ── Slot count helper ─────────────────────────────────────────────────
        function updateSlotCount() {
            let openVal     = $( vc + '_open_time' ).val();
            let closeVal    = $( vc + '_close_time' ).val();
            let durationVal = parseInt( $( vc + '_slot_duration' ).val() );
            let badge       = $( vc + '_slot_count' );

            if ( openVal && closeVal && durationVal > 0 ) {
                let [oh, om] = openVal.split(':').map(Number);
                let [ch, cm] = closeVal.split(':').map(Number);
                let totalMin  = (ch * 60 + cm) - (oh * 60 + om);

                if ( totalMin > 0 ) {
                    let slots = Math.floor( totalMin / durationVal );
                    badge.text( slots + ' slot' + (slots !== 1 ? 's' : '') + '/day' ).css( 'display', 'inline-block' );
                    return;
                }
            }

            badge.hide();
        }

        $( vc + '_open_time, ' + vc + '_close_time, ' + vc + '_slot_duration' ).on( 'change input', updateSlotCount );

        // ── Day quick-select buttons ──────────────────────────────────────────
        let weekdays = ['monday','tuesday','wednesday','thursday','friday'];
        let weekends = ['saturday','sunday'];
        let allDays  = weekdays.concat( weekends );

        function setDays( days ) {
            $( '.operating-day-check' ).prop( 'checked', false );
            days.forEach( function( d ) { $( '#{{ $vs_create }}_day_' + d ).prop( 'checked', true ); } );
        }

        $( vc + '_days_all' ).click( function() { setDays( allDays ); } );
        $( vc + '_days_weekdays' ).click( function() { setDays( weekdays ); } );
        $( vc + '_days_weekends' ).click( function() { setDays( weekends ); } );
        $( vc + '_days_none' ).click( function() { setDays( [] ); } );

        // ── Submit ────────────────────────────────────────────────────────────
        $( vc + '_submit' ).click( function() {

            resetInputValidation();
            $( vc + '_sport_id_error' ).text( '' );
            $( vc + '_operating_days_error' ).text( '' );

            let sportIds = $( vc + '_sport_id' ).val() || [];
            if ( sportIds.length === 0 ) {
                $( vc + '_sport_id_error' ).text( 'At least one sport is required.' );
                return;
            }

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
            formData.append( 'venue_id',         $( vc + '_venue_id' ).val() );
            sportIds.forEach( function( id ) { formData.append( 'sport_ids[]', id ); } );
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
                url: '{{ route( 'admin.venue.addVenueSport' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function() {
                        window.location.href = '{{ route( 'admin.module_parent.venue_sport.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            if ( key === 'sport_ids' ) {
                                $( vc + '_sport_id_error' ).text( value );
                            } else if ( key === 'operating_days' ) {
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
