<?php $cb = 'court_bulk'; ?>

{{-- Pass venue_sports as JSON for cascading selects --}}
@php
    $venueSportsJson = $data['venue_sports']->map( fn( $vs ) => [
        'id'             => $vs->id,
        'venue_id'       => $vs->venue->id ?? null,
        'venue_name'     => $vs->venue->name ?? '-',
        'sport_id'       => $vs->sport->id ?? null,
        'sport_name'     => $vs->sport->name ?? '-',
        'open_time'      => $vs->open_time ? substr( $vs->open_time, 0, 5 ) : null,
        'close_time'     => $vs->close_time ? substr( $vs->close_time, 0, 5 ) : null,
        'slot_duration'  => $vs->slot_duration,
        'price_per_slot' => $vs->price_per_slot,
    ] )->values();
@endphp

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Bulk Add Courts</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Step 1: Venue & Sport ─────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Step 1 — Select Venue &amp; Sport</h6>
        <p class="text-muted small mb-3">Choose the venue first, then the sport configured for that venue.</p>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Venue <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cb }}_venue_select">
                    <option value="">— Select Venue —</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row" id="{{ $cb }}_sport_row" style="display:none;">
            <label class="col-sm-3 col-form-label">Sport <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $cb }}_sport_select">
                    <option value="">— Select Sport —</option>
                </select>
                <input type="hidden" id="{{ $cb }}_venue_sport_id">
                <div class="invalid-feedback d-block" id="{{ $cb }}_venue_sport_id_error"></div>
            </div>
        </div>

        <div id="{{ $cb }}_context_card" class="alert alert-light border mt-2" style="display:none;">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Operating Hours</div>
                    <strong id="{{ $cb }}_ctx_hours">—</strong>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Slot Duration</div>
                    <strong id="{{ $cb }}_ctx_slot">—</strong>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Base Price / Slot</div>
                    <strong id="{{ $cb }}_ctx_price">—</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Step 2: Shared Details ────────────────────────────────────────────────── --}}
<div class="card mb-4" id="{{ $cb }}_shared_card" style="display:none;">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Step 2 — Shared Details</h6>
        <p class="text-muted small mb-3">These settings apply to every court in the batch.</p>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Capacity <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="number" class="form-control" id="{{ $cb }}_capacity" min="1" placeholder="e.g. 4">
                    <span class="input-group-text">players</span>
                </div>
                <div class="invalid-feedback d-block" id="{{ $cb }}_capacity_error"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Price / Hour <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" step="0.01" class="form-control" id="{{ $cb }}_price_per_hour" min="0" placeholder="0.00">
                </div>
                <div class="form-text" id="{{ $cb }}_price_hint"></div>
                <div class="invalid-feedback d-block" id="{{ $cb }}_price_per_hour_error"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
                <textarea class="form-control" style="min-height:80px;" id="{{ $cb }}_description" placeholder="Optional description applied to all courts..."></textarea>
            </div>
        </div>
    </div>
</div>

{{-- ── Step 3: Court List ────────────────────────────────────────────────────── --}}
<div class="card mb-4" id="{{ $cb }}_courts_card" style="display:none;">
    <div class="card-inner">
        <h6 class="overline-title text-primary-alt mb-3">Step 3 — Courts to Create</h6>

        {{-- Generator --}}
        <div class="p-3 bg-lighter rounded border mb-4">
            <p class="text-muted small mb-2">Quick-generate numbered courts:</p>
            <div class="row g-2 mb-2">
                <div class="col-sm-4">
                    <label class="form-label small mb-1">Prefix</label>
                    <input type="text" class="form-control form-control-sm" id="{{ $cb }}_gen_prefix" placeholder="e.g. Bay">
                </div>
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Start #</label>
                    <input type="number" class="form-control form-control-sm" id="{{ $cb }}_gen_start" min="1" value="1">
                </div>
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Count</label>
                    <input type="number" class="form-control form-control-sm" id="{{ $cb }}_gen_count" min="1" value="5">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="{{ $cb }}_gen_append">
                    <em class="icon ni ni-plus"></em> Append
                </button>
                <button type="button" class="btn btn-outline-secondary" id="{{ $cb }}_gen_replace">
                    Replace All
                </button>
            </div>
        </div>

        {{-- Rows table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="{{ $cb }}_table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Court Name <span class="text-danger">*</span></th>
                        <th>Slug <span class="text-danger">*</span></th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="{{ $cb }}_tbody">
                    <tr id="{{ $cb }}_empty_row">
                        <td colspan="4" class="text-center text-muted py-3">No courts yet — use the generator above or add a row manually.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $cb }}_add_row">
                <em class="icon ni ni-plus"></em> Add Row
            </button>
            <span class="text-muted small" id="{{ $cb }}_row_count"></span>
        </div>

        <div class="invalid-feedback d-block mt-2" id="{{ $cb }}_courts_error"></div>

        <div class="text-end mt-4">
            <a href="{{ route( 'admin.module_parent.court.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $cb }}_submit" type="button" class="btn btn-primary">
                <em class="icon ni ni-layers"></em> Create All Courts
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let cb      = '#{{ $cb }}';
        let rowIndex = 0;

        // ── Venue sport lookup ────────────────────────────────────────────────
        const venueSports = {!! json_encode( $venueSportsJson ) !!};

        let venueMap = {};
        venueSports.forEach( function( vs ) {
            if ( !vs.venue_id ) return;
            if ( !venueMap[ vs.venue_id ] ) venueMap[ vs.venue_id ] = { name: vs.venue_name, sports: [] };
            venueMap[ vs.venue_id ].sports.push( vs );
        } );

        let venueSelect = $( cb + '_venue_select' );
        Object.keys( venueMap ).forEach( function( vid ) {
            venueSelect.append( $( '<option>' ).val( vid ).text( venueMap[ vid ].name ) );
        } );

        venueSelect.select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
        $( cb + '_sport_select' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );

        // ── Venue → Sport cascade ─────────────────────────────────────────────
        venueSelect.on( 'change', function() {
            let venueId     = $( this ).val();
            let sportSelect = $( cb + '_sport_select' );

            sportSelect.empty().append( '<option value="">— Select Sport —</option>' );
            $( cb + '_venue_sport_id' ).val( '' );
            $( cb + '_sport_row' ).hide();
            $( cb + '_context_card, ' + cb + '_shared_card, ' + cb + '_courts_card' ).hide();

            if ( !venueId || !venueMap[ venueId ] ) return;

            venueMap[ venueId ].sports.forEach( function( vs ) {
                sportSelect.append( $( '<option>' ).val( vs.id ).text( vs.sport_name ) );
            } );

            $( cb + '_sport_row' ).show();
            sportSelect.trigger( 'change' );
        } );

        // ── Sport → context card ──────────────────────────────────────────────
        $( cb + '_sport_select' ).on( 'change', function() {
            let vsId = $( this ).val();
            $( cb + '_venue_sport_id' ).val( vsId );
            $( cb + '_context_card, ' + cb + '_shared_card, ' + cb + '_courts_card' ).hide();
            $( cb + '_price_hint' ).text( '' );

            if ( !vsId ) return;

            let vs = venueSports.find( function( v ) { return v.id == vsId; } );
            if ( !vs ) return;

            $( cb + '_ctx_hours' ).text( ( vs.open_time || '—' ) + ' – ' + ( vs.close_time || '—' ) );
            $( cb + '_ctx_slot' ).text( vs.slot_duration + ' min/slot' );
            $( cb + '_ctx_price' ).text( 'RM ' + parseFloat( vs.price_per_slot ).toFixed(2) );
            $( cb + '_context_card' ).show();

            if ( vs.price_per_slot ) {
                let slotDuration = vs.slot_duration || 60;
                let pricePerHour = ( vs.price_per_slot / slotDuration * 60 ).toFixed(2);
                $( cb + '_price_hint' ).text( 'VenueSport base: RM ' + parseFloat( vs.price_per_slot ).toFixed(2) + ' / slot (≈ RM ' + pricePerHour + ' / hr)' );
            }

            $( cb + '_shared_card, ' + cb + '_courts_card' ).show();
        } );

        // ── Slug helper ───────────────────────────────────────────────────────
        function toSlug( str ) {
            return str.toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' );
        }

        // ── Row management ────────────────────────────────────────────────────
        function updateRowNumbers() {
            $( cb + '_tbody tr.court-row' ).each( function( i ) {
                $( this ).find( '.row-num' ).text( i + 1 );
            } );
            let count = $( cb + '_tbody tr.court-row' ).length;
            $( cb + '_row_count' ).text( count ? count + ' court' + ( count !== 1 ? 's' : '' ) : '' );
            $( cb + '_empty_row' ).toggle( count === 0 );
        }

        function addRow( name ) {
            let idx  = rowIndex++;
            let slug = name ? toSlug( name ) : '';

            $( cb + '_empty_row' ).hide();
            $( cb + '_tbody' ).append( `
                <tr class="court-row" data-idx="${idx}">
                    <td class="row-num text-muted"></td>
                    <td>
                        <input type="text" class="form-control form-control-sm court-name" placeholder="Court name" value="${ $('<div>').text(name||'').html() }">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm court-slug bg-light" placeholder="auto" value="${ $('<div>').text(slug).html() }" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-dim btn-danger remove-row"><em class="icon ni ni-trash"></em></button>
                    </td>
                </tr>
            ` );

            // Name → auto-slug
            let $row  = $( cb + '_tbody tr[data-idx="' + idx + '"]' );
            let $name = $row.find( '.court-name' );
            let $slug = $row.find( '.court-slug' );

            $name.on( 'input', function() {
                $slug.val( toSlug( $( this ).val() ) );
            } );

            // Allow manual slug edit on click
            $slug.on( 'click', function() {
                $( this ).removeClass( 'bg-light' ).removeAttr( 'readonly' ).focus();
            } );

            updateRowNumbers();
        }

        // Add Row button
        $( cb + '_add_row' ).on( 'click', function() { addRow( '' ); } );

        // Remove row
        $( document ).on( 'click', cb + '_tbody .remove-row', function() {
            $( this ).closest( 'tr' ).remove();
            updateRowNumbers();
        } );

        // ── Generator ─────────────────────────────────────────────────────────
        function generate( replace ) {
            let prefix = $( cb + '_gen_prefix' ).val().trim();
            let start  = parseInt( $( cb + '_gen_start' ).val() ) || 1;
            let count  = parseInt( $( cb + '_gen_count' ).val() ) || 1;

            if ( !prefix ) { $( cb + '_gen_prefix' ).addClass( 'is-invalid' ).focus(); return; }
            $( cb + '_gen_prefix' ).removeClass( 'is-invalid' );

            if ( replace ) {
                $( cb + '_tbody tr.court-row' ).remove();
                rowIndex = 0;
            }

            for ( let i = 0; i < count; i++ ) {
                addRow( prefix + ' ' + ( start + i ) );
            }
        }

        $( cb + '_gen_append' ).on( 'click', function() { generate( false ); } );
        $( cb + '_gen_replace' ).on( 'click', function() { generate( true ); } );

        // ── Submit ────────────────────────────────────────────────────────────
        $( cb + '_submit' ).on( 'click', function() {

            $( cb + '_venue_sport_id_error, ' + cb + '_capacity_error, ' + cb + '_price_per_hour_error, ' + cb + '_courts_error' ).text( '' );

            let vsId = $( cb + '_venue_sport_id' ).val();
            if ( !vsId ) {
                $( cb + '_venue_sport_id_error' ).text( 'Please select a venue and sport.' );
                return;
            }

            let capacity = $( cb + '_capacity' ).val();
            if ( !capacity || parseInt( capacity ) < 1 ) {
                $( cb + '_capacity_error' ).text( 'Capacity is required (min 1).' );
                return;
            }

            let pricePerHour = $( cb + '_price_per_hour' ).val();
            if ( pricePerHour === '' || parseFloat( pricePerHour ) < 0 ) {
                $( cb + '_price_per_hour_error' ).text( 'Price per hour is required.' );
                return;
            }

            let courts = [];
            let hasError = false;
            $( cb + '_tbody tr.court-row' ).each( function() {
                let name = $( this ).find( '.court-name' ).val().trim();
                let slug = $( this ).find( '.court-slug' ).val().trim();
                if ( !name ) {
                    $( this ).find( '.court-name' ).addClass( 'is-invalid' );
                    hasError = true;
                } else {
                    $( this ).find( '.court-name' ).removeClass( 'is-invalid' );
                }
                courts.push( { name: name, slug: slug || toSlug( name ) } );
            } );

            if ( courts.length === 0 ) {
                $( cb + '_courts_error' ).text( 'Add at least one court.' );
                return;
            }
            if ( hasError ) return;

            // Check duplicate slugs within the batch
            let slugs = courts.map( function( c ) { return c.slug; } );
            let dupes = slugs.filter( function( s, i ) { return slugs.indexOf( s ) !== i; } );
            if ( dupes.length ) {
                $( cb + '_courts_error' ).text( 'Duplicate slugs in batch: ' + [ ...new Set( dupes ) ].join( ', ' ) );
                return;
            }

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.court.bulkCreateCourts' ) }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify( {
                    _token:         '{{ csrf_token() }}',
                    venue_sport_id: vsId,
                    capacity:       capacity,
                    price_per_hour: pricePerHour,
                    description:    $( cb + '_description' ).val(),
                    courts:         courts,
                } ),
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
                        if ( errors.venue_sport_id ) $( cb + '_venue_sport_id_error' ).text( errors.venue_sport_id[0] );
                        if ( errors.capacity )       $( cb + '_capacity_error' ).text( errors.capacity[0] );
                        if ( errors.price_per_hour ) $( cb + '_price_per_hour_error' ).text( errors.price_per_hour[0] );
                        if ( errors.courts )         $( cb + '_courts_error' ).text( errors.courts[0] );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

    } );
</script>
