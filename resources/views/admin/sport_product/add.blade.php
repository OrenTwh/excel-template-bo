@php
$categoriesJson = $data['categories']->map(function($cat) {
    return [
        'id'       => $cat->id,
        'name'     => $cat->name,
        'children' => $cat->children->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
    ];
});
$sportsJson = $data['sports']->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);
@endphp

<div class="nk-block">
    <div class="row g-gs">

        {{-- Left Column: Product Info --}}
        <div class="col-lg-8">

            {{-- Basic Info --}}
            <div class="card card-bordered mb-3">
                <div class="card-inner">
                    <h6 class="card-title">{{ __( 'sport_product.product' ) }}</h6>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.name' ) }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inp_name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.description' ) }}</label>
                        <textarea class="form-control" id="inp_description" rows="5"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __( 'sport_product.category_label' ) }}</label>
                                <select class="form-control" id="inp_category_id">
                                    <option value="">— {{ __( 'sport_product.category_label' ) }} —</option>
                                    @foreach ( $data['categories'] as $cat )
                                    <optgroup label="{{ $cat->name }}">
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @foreach ( $cat->children as $child )
                                        <option value="{{ $child->id }}">↳ {{ $child->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __( 'sport_product.sequence' ) }}</label>
                                <input type="number" class="form-control" id="inp_sequence" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ __( 'sport_product.status' ) }}</label>
                                <select class="form-control" id="inp_status">
                                    <option value="10">{{ __( 'datatables.activated' ) }}</option>
                                    <option value="20">{{ __( 'datatables.suspended' ) }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.sports' ) }}</label>
                        <select class="form-control" id="inp_sport_ids" multiple>
                            @foreach ( $data['sports'] as $sport )
                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Variants --}}
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">{{ __( 'sport_product.variants' ) }}</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_variant">
                            <em class="icon ni ni-plus"></em> {{ __( 'sport_product.add_variant' ) }}
                        </button>
                    </div>

                    <div id="variants_container">
                        {{-- Variant rows injected by JS --}}
                    </div>

                    <div id="no_variants_msg" class="text-muted small text-center py-3">
                        {{ __( 'sport_product.add_variant' ) }} to get started.
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Images --}}
        <div class="col-lg-4">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="card-title">{{ __( 'sport_product.images' ) }}</h6>
                    <div id="product-dropzone" class="dropzone" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_files_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">You can upload multiple images.</small>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-3">
        <button class="btn btn-primary" id="btn_submit">{{ __( 'template.save' ) }}</button>
        <a href="{{ route( 'admin.module_parent.sport_product.index' ) }}" class="btn btn-secondary">{{ __( 'template.cancel' ) }}</a>
    </div>
</div>

{{-- Variant Row Template --}}
<template id="variant_tpl">
    <div class="variant-row card card-bordered mb-2" data-index="__INDEX__">
        <div class="card-inner p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-outline-primary">{{ __( 'sport_product.variant' ) }} #<span class="variant-num">1</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant">
                    <em class="icon ni ni-trash"></em> {{ __( 'template.delete' ) }}
                </button>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.name' ) }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm var-name" placeholder="e.g. Size M / Red">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.sku' ) }}</label>
                    <input type="text" class="form-control form-control-sm var-sku" placeholder="SKU-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.status' ) }}</label>
                    <select class="form-select form-select-sm var-status">
                        <option value="10">{{ __( 'datatables.activated' ) }}</option>
                        <option value="20">{{ __( 'datatables.suspended' ) }}</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.price' ) }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">RM</span>
                        <input type="number" step="0.01" min="0" class="form-control var-price" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.compare_price' ) }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">RM</span>
                        <input type="number" step="0.01" min="0" class="form-control var-compare-price" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-sm">{{ __( 'sport_product.initial_stock' ) }}</label>
                    <input type="number" min="0" class="form-control form-control-sm var-stock" placeholder="0" value="0">
                </div>
            </div>

            {{-- Specs --}}
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label form-label-sm mb-0">{{ __( 'sport_product.specs' ) }}</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-add-spec">
                        <em class="icon ni ni-plus"></em> {{ __( 'sport_product.add_spec' ) }}
                    </button>
                </div>
                <div class="specs-container"></div>
            </div>

            {{-- Variant Image --}}
            <div class="mt-3 pt-2 border-top">
                <label class="form-label form-label-sm">{{ __( 'sport_product.variant_image' ) }}</label>
                <div class="var-dropzone dropzone" style="min-height:0;">
                    <div class="dz-message needsclick py-2 text-center">
                        <em class="icon ni ni-img fs-3 text-muted d-block mb-1"></em>
                        <small class="text-muted">{{ __( 'template.drop_files_or_click_to_upload' ) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Spec Row Template --}}
<template id="spec_tpl">
    <div class="spec-row row g-2 mb-2 align-items-center">
        <div class="col">
            <input type="text" class="form-control form-control-sm spec-key" placeholder="{{ __( 'sport_product.spec_key' ) }}">
        </div>
        <div class="col">
            <input type="text" class="form-control form-control-sm spec-value" placeholder="{{ __( 'sport_product.spec_value' ) }}">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-spec"><em class="icon ni ni-cross"></em></button>
        </div>
    </div>
</template>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    $( '#inp_category_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
    $( '#inp_sport_ids' ).select2( { theme: 'bootstrap-5', width: '100%' } );

    var uploadedImages   = [];
    var variantIndex     = 0;
    var variantDropzones = {};

    // ── Dropzone ──────────────────────────────────────────────────────────────
    Dropzone.autoDiscover = false;
    var dz = new Dropzone( '#product-dropzone', {
        url: '{{ route( 'admin.file.upload' ) }}',
        maxFilesize: 3,
        acceptedFiles: 'image/jpg,image/jpeg,image/png',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function( file, response ) {
            if ( response.status == 200 ) {
                file.serverPath = response.data.file;
                uploadedImages.push( response.data.file );
            }
        },
        removedfile: function( file ) {
            if ( file.serverPath ) uploadedImages = uploadedImages.filter( p => p !== file.serverPath );
            file.previewElement.remove();
        },
    });

    // ── Add Variant ───────────────────────────────────────────────────────────
    $( '#btn_add_variant' ).on( 'click', function() {
        addVariantRow();
    });

    function addVariantRow() {
        let tpl  = document.getElementById( 'variant_tpl' ).content.cloneNode( true );
        let row  = tpl.querySelector( '.variant-row' );
        let idx  = variantIndex++;
        row.dataset.index = idx;
        row.querySelector( '.variant-num' ).textContent = $( '#variants_container .variant-row' ).length + 1;

        document.getElementById( 'variants_container' ).appendChild( row );

        // Init per-variant Dropzone after the row is in the DOM
        let dzEl = document.querySelector( `#variants_container .variant-row[data-index="${idx}"] .var-dropzone` );
        variantDropzones[idx] = new Dropzone( dzEl, {
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            maxFilesize: 3,
            acceptedFiles: 'image/jpg,image/jpeg,image/png,image/webp',
            addRemoveLinks: true,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            dictMaxFilesExceeded: 'Only 1 image per variant.',
            success: function( file, r ) {
                if ( r.status == 200 ) {
                    file.serverPath = r.data.file;
                    $( dzEl ).closest( '.variant-row' ).data( 'var-image', r.data.file );
                }
            },
            removedfile: function( file ) {
                if ( file.serverPath ) $( dzEl ).closest( '.variant-row' ).data( 'var-image', '' );
                if ( file.previewElement ) file.previewElement.remove();
            },
        });

        updateNoVariantsMsg();
    }

    // ── Remove Variant ────────────────────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-remove-variant', function() {
        let row = $( this ).closest( '.variant-row' );
        let idx = parseInt( row.data( 'index' ) );
        if ( variantDropzones[idx] ) {
            variantDropzones[idx].destroy();
            delete variantDropzones[idx];
        }
        row.remove();
        renumberVariants();
        updateNoVariantsMsg();
    });

    function renumberVariants() {
        $( '#variants_container .variant-row' ).each( function(i) {
            $( this ).find( '.variant-num' ).text( i + 1 );
        });
    }

    function updateNoVariantsMsg() {
        let hasVariants = $( '#variants_container .variant-row' ).length > 0;
        $( '#no_variants_msg' ).toggle( !hasVariants );
    }

    // ── Add Spec ──────────────────────────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-add-spec', function() {
        let specContainer = $( this ).closest( '.variant-row' ).find( '.specs-container' );
        let tpl = document.getElementById( 'spec_tpl' ).content.cloneNode( true );
        specContainer.append( tpl );
    });

    $( '#variants_container' ).on( 'click', '.btn-remove-spec', function() {
        $( this ).closest( '.spec-row' ).remove();
    });

    // ── Submit ────────────────────────────────────────────────────────────────
    $( '#btn_submit' ).on( 'click', function() {
        let btn = $( this ).prop( 'disabled', true ).text( '...' );

        let fd = new FormData();
        fd.append( '_token',      '{{ csrf_token() }}' );
        fd.append( 'name',        $( '#inp_name' ).val() );
        fd.append( 'description', $( '#inp_description' ).val() );
        fd.append( 'category_id', $( '#inp_category_id' ).val() );
        fd.append( 'sequence',    $( '#inp_sequence' ).val() );
        fd.append( 'status',      $( '#inp_status' ).val() );

        $( '#inp_sport_ids' ).val().forEach( id => fd.append( 'sport_ids[]', id ) );
        uploadedImages.forEach( p => fd.append( 'images[]', p ) );

        // Collect variants
        $( '#variants_container .variant-row' ).each( function(i) {
            let row = $( this );
            fd.append( `variants[${i}][name]`,          row.find( '.var-name' ).val() );
            fd.append( `variants[${i}][sku]`,           row.find( '.var-sku' ).val() );
            fd.append( `variants[${i}][price]`,         row.find( '.var-price' ).val() );
            fd.append( `variants[${i}][compare_price]`, row.find( '.var-compare-price' ).val() );
            fd.append( `variants[${i}][status]`,        row.find( '.var-status' ).val() );
            fd.append( `variants[${i}][initial_stock]`, row.find( '.var-stock' ).val() || 0 );

            // Specs
            let specs = {};
            row.find( '.spec-row' ).each( function() {
                let k = $( this ).find( '.spec-key' ).val().trim();
                let v = $( this ).find( '.spec-value' ).val().trim();
                if ( k ) specs[k] = v;
            });
            fd.append( `variants[${i}][specs]`, JSON.stringify( specs ) );

            let varImage = row.data( 'var-image' ) || '';
            if ( varImage ) fd.append( `variants[${i}][image]`, varImage );
        });

        $.ajax({
            url: '{{ route( 'admin.sport_product.createProduct' ) }}',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function(r) {
                window.location.href = '{{ route( 'admin.module_parent.sport_product.index' ) }}';
            },
            error: function(xhr) {
                btn.prop( 'disabled', false ).text( '{{ __( "template.save" ) }}' );
                let msg = xhr.responseJSON?.message ?? Object.values( xhr.responseJSON?.errors ?? {} ).flat().join( '\n' );
                alert( msg );
            },
        });
    });
});
</script>
