<?php $court_edit = 'court_edit'; ?>

{{-- Pass venue_sports as JSON for cascading selects --}}
@php
    $venueSportsJson = $data['venue_sports']->map( fn( $vs ) => [
        'id'               => $vs->id,
        'venue_id'         => $vs->venue->id ?? null,
        'venue_name'       => $vs->venue->name ?? '-',
        'sport_id'         => $vs->sport->id ?? null,
        'sport_name'       => $vs->sport->name ?? '-',
        'open_time'        => $vs->open_time ? substr( $vs->open_time, 0, 5 ) : null,
        'close_time'       => $vs->close_time ? substr( $vs->close_time, 0, 5 ) : null,
        'pricing_method'   => $vs->pricing_method,
        'sport_type'       => $vs->sport->type ?? null,
        'slot_duration'    => $vs->slot_duration,
        'price_per_slot'   => $vs->price_per_slot,
        'price_per_person' => $vs->price_per_person,
        'price_per_night'  => $vs->price_per_night,
    ] )->values();
@endphp

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => 'Court' ] ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Venue Sport (cascading) ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Venue &amp; Sport</h6>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Venue <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $court_edit }}_venue_select">
                    <option value="">— Select Venue —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Sport <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $court_edit }}_sport_select">
                    <option value="">— Select Sport —</option>
                </select>
                <input type="hidden" id="{{ $court_edit }}_venue_sport_id">
                <div class="invalid-feedback" id="{{ $court_edit }}_venue_sport_id_error"></div>
            </div>
        </div>

        {{-- Context card --}}
        <div id="{{ $court_edit }}_context_card" class="alert alert-light border mt-2" style="display:none;">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Operating Hours</div>
                    <strong id="{{ $court_edit }}_ctx_hours">—</strong>
                </div>
                <div class="col-sm-4" id="{{ $court_edit }}_ctx_slot_col">
                    <div class="small text-muted mb-1">Slot Duration</div>
                    <strong id="{{ $court_edit }}_ctx_slot">—</strong>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1" id="{{ $court_edit }}_ctx_price_label">Base Price</div>
                    <strong id="{{ $court_edit }}_ctx_price">—</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Court Details ────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Court Details</h6>

        <div class="mb-3 row">
            <label for="{{ $court_edit }}_name" id="{{ $court_edit }}_name_label" class="col-sm-3 col-form-label">Name <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $court_edit }}_name">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row d-none">
            <label for="{{ $court_edit }}_slug" class="col-sm-3 col-form-label">Slug</label>
            <div class="col-sm-9">
                <input type="text" class="form-control bg-light" id="{{ $court_edit }}_slug" readonly>
                <div class="form-text">Auto-generated from name.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $court_edit }}_description" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
                <textarea class="form-control" style="min-height:80px;" id="{{ $court_edit }}_description"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $court_edit }}_capacity" id="{{ $court_edit }}_capacity_label" class="col-sm-3 col-form-label">Capacity <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="number" class="form-control" id="{{ $court_edit }}_capacity" min="0">
                    <span class="input-group-text">participants</span>
                </div>
                <div class="form-text" id="{{ $court_edit }}_capacity_hint"></div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $court_edit }}_price_per_hour" id="{{ $court_edit }}_price_label" class="col-sm-3 col-form-label">Base Price <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" step="0.01" class="form-control" id="{{ $court_edit }}_price_per_hour" min="0">
                </div>
                <div class="form-text" id="{{ $court_edit }}_price_hint"></div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

    </div>
</div>

{{-- ── Pricing Tiers ────────────────────────────────────────────────────────── --}}
<div class="card mb-4" id="{{ $court_edit }}_pricing_card">
    <div class="card-inner">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h6 class="overline-title text-primary-alt mb-0">{{ __( 'court.pricings' ) }}</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="{{ $court_edit }}_add_tier">
                <em class="icon ni ni-plus"></em> {{ __( 'court.pricing_add_tier' ) }}
            </button>
        </div>
        <p class="text-muted small mb-3">{{ __( 'court.pricing_hint' ) }}</p>
        <div id="{{ $court_edit }}_pricing_rows"></div>
        <p class="text-muted small fst-italic mb-0" id="{{ $court_edit }}_no_tiers_msg">{{ __( 'court.pricing_no_tiers' ) }}</p>
    </div>
</div>

{{-- ── Media ────────────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Media</h6>

        {{-- Current cover image --}}
        <div class="mb-3 row" id="{{ $court_edit }}_current_image_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Current Cover</label>
            <div class="col-sm-9">
                <img id="{{ $court_edit }}_image_preview" src="" class="rounded border mb-2 d-block" style="max-height:150px; max-width:300px;">
                <button type="button" class="btn btn-sm btn-danger" id="{{ $court_edit }}_remove_image">
                    <em class="icon ni ni-trash"></em> Remove
                </button>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Replace Cover</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="{{ $court_edit }}_image" accept="image/jpg,image/jpeg,image/png">
                <div class="form-text">{{ __( 'template.leave_blank' ) }}</div>
                <div class="invalid-feedback"></div>
                <div class="mt-2" id="{{ $court_edit }}_new_image_preview_wrap" style="display:none;">
                    <img id="{{ $court_edit }}_new_image_preview" src="" class="rounded border" style="max-height:120px; max-width:280px;">
                    <div class="form-text text-success">New image selected — will replace current on save.</div>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Gallery</label>
            <div class="col-sm-9">
                <div class="dropzone mb-2" id="{{ $court_edit }}_gallery" style="min-height:0px;">
                    <div class="dz-message needsclick">
                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_files_or_click_to_upload' ) }}</h3>
                    </div>
                </div>
                <div class="form-text">Additional court photos. JPG or PNG. Click × on a photo to remove it.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.court.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $court_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe             = '#{{ $court_edit }}';
        let galleryFileIDs = [];
        let currentVs      = null;

        // ── Unit label helpers ─────────────────────────────────────────────────
        const unitNames = {
            court:         { unit: 'Court',          capacity: 'Capacity',        placeholder: 'e.g. Court A, Court B1' },
            activity:      { unit: 'Bay / Station',  capacity: 'Max Participants', placeholder: 'e.g. Bay 1, Archery Lane 2' },
            water:         { unit: 'Station / Boat', capacity: 'Max Passengers',  placeholder: 'e.g. Boat 1, Kayak Station 2' },
            accommodation: { unit: 'Camp / Cabin',   capacity: 'Max Guests',      placeholder: 'e.g. Camp A, Cabin 3' },
        };

        function getUnitMeta( sportType ) {
            return unitNames[ sportType ] || { unit: 'Unit', capacity: 'Capacity', placeholder: 'e.g. Unit 1' };
        }

        function updateUnitLabels( vs ) {
            let meta = getUnitMeta( vs ? vs.sport_type : null );
            $( fe + '_name_label' ).html( meta.unit + ' Name <span class="text-danger">*</span>' );
            $( fe + '_capacity_label' ).html( meta.capacity + ' <span class="text-danger">*</span>' );
            refreshCapacityHint( parseInt( $( fe + '_capacity' ).val() ) || 0, vs );
        }

        function refreshCapacityHint( cap, vs ) {
            let hint = $( fe + '_capacity_hint' );
            let type = vs ? vs.sport_type : null;
            let pm   = vs ? ( vs.pricing_method || '' ) : '';

            if ( type === 'accommodation' || pm === 'per_night' ) {
                hint.html( cap > 0
                    ? '<span class="text-info"><em class="icon ni ni-users"></em> This camp / cabin sleeps up to <strong>' + cap + '</strong> guests.</span>'
                    : '<span class="text-muted">Enter the maximum number of guests this unit can sleep.</span>' );
            } else if ( type === 'activity' || type === 'water' || pm === 'per_person' ) {
                if ( cap <= 1 ) {
                    hint.html( '<span class="text-info"><em class="icon ni ni-lock-alt"></em> <strong>Single-booking</strong> — one customer at a time (e.g. solo archery lane, solo kayak).</span>' );
                } else {
                    hint.html( '<span class="text-success"><em class="icon ni ni-users"></em> <strong>Shared</strong> — up to <strong>' + cap + '</strong> participants can use this station simultaneously.</span>' );
                }
            } else {
                if ( cap <= 1 ) {
                    hint.html( '<span class="text-info"><em class="icon ni ni-lock-alt"></em> <strong>Exclusive</strong> — one group books this court per slot (e.g. badminton court, golf bay).</span>' );
                } else {
                    hint.html( '<span class="text-success"><em class="icon ni ni-users"></em> <strong>Shared</strong> — up to <strong>' + cap + '</strong> participants can book the same slot simultaneously.</span>' );
                }
            }
        }

        // ── Build venue_sports lookup ──────────────────────────────────────────
        const venueSports = {!! json_encode( $venueSportsJson ) !!};

        let venueMap = {};
        venueSports.forEach( function( vs ) {
            if ( !vs.venue_id ) return;
            if ( !venueMap[ vs.venue_id ] ) {
                venueMap[ vs.venue_id ] = { name: vs.venue_name, sports: [] };
            }
            venueMap[ vs.venue_id ].sports.push( vs );
        } );

        // Populate Venue select
        let venueSelect = $( fe + '_venue_select' );
        Object.keys( venueMap ).forEach( function( venueId ) {
            venueSelect.append( $( '<option>' ).val( venueId ).text( venueMap[ venueId ].name ) );
        } );

        venueSelect.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( fe + '_sport_select' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );

        // ── Venue → Sport cascade ─────────────────────────────────────────────
        venueSelect.on( 'change', function() {
            let venueId     = $( this ).val();
            let sportSelect = $( fe + '_sport_select' );

            sportSelect.empty().append( '<option value="">— Select Sport —</option>' );
            $( fe + '_venue_sport_id' ).val( '' );
            $( fe + '_context_card' ).hide();

            if ( !venueId || !venueMap[ venueId ] ) return;

            venueMap[ venueId ].sports.forEach( function( vs ) {
                sportSelect.append( $( '<option>' ).val( vs.id ).text( vs.sport_name ) );
            } );

            sportSelect.trigger( 'change' );
        } );

        // ── Sport → fill venue_sport_id + context card ────────────────────────
        $( fe + '_sport_select' ).on( 'change', function() {
            let vsId = $( this ).val();
            $( fe + '_venue_sport_id' ).val( vsId );
            $( fe + '_context_card' ).hide();
            $( fe + '_price_hint' ).text( '' );

            if ( !vsId ) return;

            let vs = venueSports.find( function( v ) { return v.id == vsId; } );
            if ( !vs ) return;

            let pm = vs.pricing_method || '';

            // ── Context card ──────────────────────────────────────────────────
            $( fe + '_ctx_hours' ).text( ( vs.open_time || '—' ) + ' – ' + ( vs.close_time || '—' ) );

            if ( pm === 'per_slot' || pm === 'per_hour' || pm === '' ) {
                $( fe + '_ctx_slot_col' ).show();
                $( fe + '_ctx_slot' ).text( vs.slot_duration != null ? vs.slot_duration + ' min/slot' : '—' );
            } else {
                $( fe + '_ctx_slot_col' ).hide();
            }

            let ctxPriceLabel, ctxPriceVal, priceHint;
            if ( pm === 'per_person' ) {
                ctxPriceLabel = 'Base Price / Person';
                ctxPriceVal   = vs.price_per_person != null ? 'RM ' + parseFloat( vs.price_per_person ).toFixed(2) : '—';
                priceHint     = vs.price_per_person != null ? 'VenueSport base: RM ' + parseFloat( vs.price_per_person ).toFixed(2) + ' / person' : '';
            } else if ( pm === 'per_night' ) {
                ctxPriceLabel = 'Base Price / Night';
                ctxPriceVal   = vs.price_per_night != null ? 'RM ' + parseFloat( vs.price_per_night ).toFixed(2) : '—';
                priceHint     = vs.price_per_night != null ? 'VenueSport base: RM ' + parseFloat( vs.price_per_night ).toFixed(2) + ' / night' : '';
            } else if ( pm === 'per_hour' ) {
                ctxPriceLabel = 'Base Price / Hour';
                ctxPriceVal   = vs.price_per_slot != null ? 'RM ' + parseFloat( vs.price_per_slot ).toFixed(2) : '—';
                priceHint     = vs.price_per_slot != null ? 'VenueSport base: RM ' + parseFloat( vs.price_per_slot ).toFixed(2) + ' / hr' : '';
            } else {
                ctxPriceLabel = 'Base Price / Slot';
                ctxPriceVal   = vs.price_per_slot != null ? 'RM ' + parseFloat( vs.price_per_slot ).toFixed(2) : '—';
                if ( vs.price_per_slot != null && vs.slot_duration ) {
                    let pph = ( vs.price_per_slot / vs.slot_duration * 60 ).toFixed(2);
                    priceHint = 'VenueSport base: RM ' + parseFloat( vs.price_per_slot ).toFixed(2) + ' / slot (≈ RM ' + pph + ' / hr)';
                } else {
                    priceHint = '';
                }
            }
            $( fe + '_ctx_price_label' ).text( ctxPriceLabel );
            $( fe + '_ctx_price' ).text( ctxPriceVal );
            $( fe + '_context_card' ).show();

            // ── Court price field label ───────────────────────────────────────
            let courtPriceLabel = { 'per_slot': 'Price / Slot', 'per_hour': 'Price / Hour', 'per_person': 'Price / Person', 'per_night': 'Price / Night' }[ pm ] || 'Base Price';
            $( fe + '_price_label' ).html( courtPriceLabel + ' <span class="text-danger">*</span>' );
            $( fe + '_price_hint' ).text( priceHint );

            currentVs = vs;
            updateUnitLabels( vs );
        } );

        // Helper: set venue+sport cascade from a known venue_sport_id
        function setCascadeFromVsId( vsId ) {
            let vs = venueSports.find( function( v ) { return v.id == vsId; } );
            if ( !vs || !vs.venue_id ) return;

            // Set venue (silently)
            venueSelect.val( vs.venue_id ).trigger( 'change.select2' );

            // Populate sports for this venue
            let sportSelect = $( fe + '_sport_select' );
            sportSelect.empty().append( '<option value="">— Select Sport —</option>' );
            venueMap[ vs.venue_id ].sports.forEach( function( v ) {
                sportSelect.append( $( '<option>' ).val( v.id ).text( v.sport_name ) );
            } );
            sportSelect.trigger( 'change.select2' );

            // Set sport value and trigger context card
            sportSelect.val( vsId ).trigger( 'change' );
        }

        // ── Pricing tiers ─────────────────────────────────────────────────────
        function addTierRow( data ) {
            let html = `
                <div class="row g-2 mb-2 align-items-end pricing-tier-row">
                    <div class="col-sm-3">
                        <label class="form-label small mb-1">{{ __( 'court.pricing_label' ) }}</label>
                        <input type="text" class="form-control form-control-sm tier-label" placeholder="e.g. Peak, Off-Peak" value="${ data?.label ?? '' }">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1">{{ __( 'court.pricing_time_from' ) }}</label>
                        <input type="text" class="form-control form-control-sm tier-time-from" placeholder="— All day —" value="${ data?.time_from ?? '' }">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1">{{ __( 'court.pricing_time_to' ) }}</label>
                        <input type="text" class="form-control form-control-sm tier-time-to" placeholder="— All day —" value="${ data?.time_to ?? '' }">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1" title="{{ __( 'court.pricing_min_courts_hint' ) }}">{{ __( 'court.pricing_min_courts' ) }} <em class="icon ni ni-info text-muted" style="font-size:.75rem;"></em></label>
                        <input type="number" class="form-control form-control-sm tier-min-courts" value="${ data?.min_courts ?? 1 }" min="1">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1">{{ __( 'court.pricing_price' ) }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">RM</span>
                            <input type="number" step="0.01" class="form-control tier-price" value="${ data?.price ?? '' }" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-sm-1 text-end">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-tier-row" title="Remove">
                            <em class="icon ni ni-trash"></em>
                        </button>
                    </div>
                </div>`;

            let $row = $( html );
            $( fe + '_pricing_rows' ).append( $row );

            let fromPicker = $row.find( '.tier-time-from' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, allowInput: true } );
            let toPicker   = $row.find( '.tier-time-to' ).flatpickr( { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, allowInput: true } );
            if ( data?.time_from ) fromPicker.setDate( data.time_from );
            if ( data?.time_to )   toPicker.setDate( data.time_to );

            $( fe + '_no_tiers_msg' ).hide();
        }

        $( fe + '_add_tier' ).click( function() { addTierRow( null ); } );

        $( document ).on( 'click', '.remove-tier-row', function() {
            $( this ).closest( '.pricing-tier-row' ).remove();
            if ( $( fe + '_pricing_rows .pricing-tier-row' ).length === 0 ) {
                $( fe + '_no_tiers_msg' ).show();
            }
        } );

        function collectTiers() {
            let tiers = [];
            $( fe + '_pricing_rows .pricing-tier-row' ).each( function() {
                let price = $( this ).find( '.tier-price' ).val();
                if ( !price ) return;
                tiers.push( {
                    label:      $( this ).find( '.tier-label' ).val() || null,
                    time_from:  $( this ).find( '.tier-time-from' ).val() || null,
                    time_to:    $( this ).find( '.tier-time-to' ).val() || null,
                    min_courts: parseInt( $( this ).find( '.tier-min-courts' ).val() ) || 1,
                    price:      price,
                } );
            } );
            return tiers;
        }

        // ── Capacity live hint ────────────────────────────────────────────────
        $( fe + '_capacity' ).on( 'input', function() {
            refreshCapacityHint( parseInt( $( this ).val() ) || 0, currentVs );
        } );

        // ── New image preview ─────────────────────────────────────────────────
        $( fe + '_image' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( fe + '_new_image_preview' ).attr( 'src', e.target.result );
                    $( fe + '_new_image_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( fe + '_new_image_preview_wrap' ).hide();
            }
        } );

        // ── Remove cover image ────────────────────────────────────────────────
        $( fe + '_remove_image' ).click( function() {
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court.removeCourtImage' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( fe + '_current_image_row' ).hide();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            } );
        } );

        // ── Submit ────────────────────────────────────────────────────────────
        $( fe + '_submit' ).click( function() {

            resetInputValidation();
            $( fe + '_venue_sport_id_error' ).text( '' );

            let vsId = $( fe + '_venue_sport_id' ).val();
            if ( !vsId ) {
                $( fe + '_venue_sport_id_error' ).text( 'Please select a venue and sport.' );
                return;
            }

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id',             '{{ request( 'id' ) }}' );
            formData.append( 'venue_sport_id', vsId );
            formData.append( 'name',           $( fe + '_name' ).val() );
            formData.append( 'slug',           $( fe + '_slug' ).val() );
            formData.append( 'description',    $( fe + '_description' ).val() );
            formData.append( 'price_per_hour', $( fe + '_price_per_hour' ).val() );
            formData.append( 'capacity',       $( fe + '_capacity' ).val() );
            formData.append( 'pricing',        JSON.stringify( collectTiers() ) );

            let imageFile = $( fe + '_image' )[0].files[0];
            if ( imageFile ) formData.append( 'image', imageFile );

            formData.append( 'gallery', galleryFileIDs.join( ',' ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court.updateCourt' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function() {
                        window.location.href = '{{ route( 'admin.module_parent.court.index' ) }}';
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

        // ── Load court data ───────────────────────────────────────────────────
        Dropzone.autoDiscover = false;
        getCourt();

        function getCourt() {
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.court.oneCourt' ) }}',
                type: 'POST',
                data: { 'id': '{{ request( 'id' ) }}', '_token': '{{ csrf_token() }}' },
                success: function( response ) {

                    // Set cascading selects from loaded venue_sport_id
                    if ( response.venue_sport_id ) {
                        setCascadeFromVsId( response.venue_sport_id );
                    }

                    $( fe + '_name' ).val( response.name );
                    $( fe + '_slug' ).val( response.slug );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_price_per_hour' ).val( response.price_per_hour );
                    $( fe + '_capacity' ).val( response.capacity );
                    refreshCapacityHint( parseInt( response.capacity ) || 0, currentVs );

                    if ( response.image_path ) {
                        $( fe + '_image_preview' ).attr( 'src', response.image_path );
                        $( fe + '_current_image_row' ).show();
                    }

                    // Pricing tiers
                    if ( response.pricings && response.pricings.length ) {
                        response.pricings.forEach( function( tier ) { addTierRow( tier ); } );
                    }

                    // Gallery Dropzone
                    const galleryDropzone = new Dropzone( fe + '_gallery', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp',
                        addRemoveLinks: true,
                        params: { '_token': '{{ csrf_token() }}' },
                        init: function() {
                            let that = this;
                            if ( response.galleries && response.galleries.length ) {
                                response.galleries.forEach( function( gallery ) {
                                    let mockFile = { name: 'Gallery', size: 1024, accepted: true, galleryId: gallery.id };
                                    that.files.push( mockFile );
                                    that.displayExistingFile( mockFile, gallery.image_url );
                                } );
                            }
                        },
                        removedfile: function( file ) {
                            if ( file.galleryId ) {
                                removeGalleryImage( file.galleryId );
                            } else if ( file.id ) {
                                let idx = galleryFileIDs.indexOf( file.id.toString() );
                                if ( idx !== -1 ) galleryFileIDs.splice( idx, 1 );
                            }
                            if ( file.previewElement ) file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            if ( response.status == 200 ) {
                                file.id = response.data.id;
                                galleryFileIDs.push( response.data.id.toString() );
                            }
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        function removeGalleryImage( galleryId ) {
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'gallery_id', galleryId );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.court.removeCourtGalleryImage' ) }}',
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
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            } );
        }

    } );
</script>
