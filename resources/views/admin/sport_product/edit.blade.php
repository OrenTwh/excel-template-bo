<div class="nk-block">
    <div class="row g-gs">

        {{-- Left: Product Info --}}
        <div class="col-lg-8">

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

                    <button class="btn btn-primary" id="btn_update_product">{{ __( 'template.update' ) }}</button>
                </div>
            </div>

            {{-- Images --}}
            <div class="card card-bordered mb-3">
                <div class="card-inner">
                    <h6 class="card-title">{{ __( 'sport_product.images' ) }}</h6>
                    <div id="existing_images" class="d-flex flex-wrap gap-2 mb-3"></div>
                    <div id="product-dropzone" class="dropzone" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_files_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Variants + Stock --}}
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">{{ __( 'sport_product.variants' ) }}</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_variant">
                            <em class="icon ni ni-plus"></em> {{ __( 'sport_product.add_variant' ) }}
                        </button>
                    </div>
                    <div id="variants_container"></div>
                </div>
            </div>

        </div>

        {{-- Right: Stock Summary --}}
        <div class="col-lg-4">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="card-title">{{ __( 'sport_product.stock' ) }}</h6>
                    <div id="stock_summary">
                        <p class="text-muted small">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-3">
        <a href="{{ route( 'admin.module_parent.sport_product.index' ) }}" class="btn btn-secondary">{{ __( 'template.back' ) }}</a>
    </div>
</div>

{{-- Stock Adjust Modal --}}
<div class="modal fade" id="modal_stock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'sport_product.adjust_stock' ) }} — <span id="modal_stock_variant_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_stock_variant_id">
                <div class="mb-3">
                    <label class="form-label">{{ __( 'sport_product.quantity' ) }} <small class="text-muted">(negative to deduct)</small></label>
                    <input type="number" class="form-control" id="modal_stock_qty" placeholder="e.g. 10 or -5">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __( 'sport_product.type_restock' ) }}</label>
                    <select class="form-control" id="modal_stock_type">
                        <option value="restock">{{ __( 'sport_product.type_restock' ) }}</option>
                        <option value="adjustment">{{ __( 'sport_product.type_adjustment' ) }}</option>
                        <option value="return">{{ __( 'sport_product.type_return' ) }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __( 'template.notes' ) }}</label>
                    <textarea class="form-control" id="modal_stock_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_adjust_stock">{{ __( 'sport_product.adjust_stock' ) }}</button>
            </div>
        </div>
    </div>
</div>

{{-- New Variant Modal --}}
<div class="modal fade" id="modal_add_variant" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'sport_product.add_variant' ) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __( 'sport_product.name' ) }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nv_name" placeholder="e.g. Size M / Red">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __( 'sport_product.sku' ) }}</label>
                        <input type="text" class="form-control" id="nv_sku">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __( 'sport_product.status' ) }}</label>
                        <select class="form-control" id="nv_status">
                            <option value="10">{{ __( 'datatables.activated' ) }}</option>
                            <option value="20">{{ __( 'datatables.suspended' ) }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __( 'sport_product.price' ) }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="nv_price">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __( 'sport_product.compare_price' ) }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="nv_compare_price">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __( 'sport_product.initial_stock' ) }}</label>
                        <input type="number" min="0" class="form-control" id="nv_stock" value="0">
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>{{ __( 'sport_product.specs' ) }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_nv_add_spec"><em class="icon ni ni-plus"></em> {{ __( 'sport_product.add_spec' ) }}</button>
                </div>
                <div id="nv_specs_container"></div>

                <hr>
                <div>
                    <label class="form-label">{{ __( 'sport_product.variant_image' ) }}</label>
                    <div id="nv_dropzone" class="dropzone" style="min-height:0;">
                        <div class="dz-message needsclick py-2 text-center">
                            <em class="icon ni ni-img fs-3 text-muted d-block mb-1"></em>
                            <small class="text-muted">{{ __( 'template.drop_files_or_click_to_upload' ) }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_save_variant">{{ __( 'template.save' ) }}</button>
            </div>
        </div>
    </div>
</div>

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

    let productId = new URLSearchParams( window.location.search ).get( 'id' );
    let uploadedImages = [];

    $( '#inp_category_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );
    $( '#inp_sport_ids' ).select2( { theme: 'bootstrap-5', width: '100%' } );

    // ── Dropzone ──────────────────────────────────────────────────────────────
    Dropzone.autoDiscover = false;
    var dz = new Dropzone( '#product-dropzone', {
        url: '{{ route( 'admin.file.upload' ) }}',
        maxFilesize: 3,
        acceptedFiles: 'image/jpg,image/jpeg,image/png',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function( file, r ) {
            if ( r.status == 200 ) {
                file.serverPath = r.data.file;
                uploadedImages.push( r.data.file );
                saveNewImages();
            }
        },
        removedfile: function( file ) {
            if ( file.serverPath ) uploadedImages = uploadedImages.filter( p => p !== file.serverPath );
            file.previewElement.remove();
        },
    });

    // ── Modal Variant Dropzone ─────────────────────────────────────────────────
    var nvDz = new Dropzone( '#nv_dropzone', {
        url: '{{ route( 'admin.file.upload' ) }}',
        maxFiles: 1,
        maxFilesize: 3,
        acceptedFiles: 'image/jpg,image/jpeg,image/png,image/webp',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        dictMaxFilesExceeded: 'Only 1 image per variant.',
        success: function( file, r ) {
            if ( r.status == 200 ) file.serverPath = r.data.file;
        },
        removedfile: function( file ) {
            if ( file.previewElement ) file.previewElement.remove();
        },
    });

    function saveNewImages() {
        uploadedImages.forEach( (p, i) => {
            let fd = new FormData();
            fd.append( '_token', '{{ csrf_token() }}' );
            fd.append( 'id', productId );
            fd.append( 'images[]', p );
            $.ajax({ url: '{{ route( 'admin.sport_product.updateProduct' ) }}', type: 'POST', data: fd, contentType: false, processData: false });
        });
    }

    // ── Load Product ──────────────────────────────────────────────────────────
    function loadProduct() {
        $.ajax({
            url: '{{ route( 'admin.sport_product.oneProduct' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': productId },
            success: function(r) {
                $( '#inp_name' ).val( r.name );
                $( '#inp_description' ).val( r.description );
                $( '#inp_category_id' ).val( r.category_id ).trigger( 'change' );
                $( '#inp_sequence' ).val( r.sequence );
                $( '#inp_status' ).val( r.status );

                // Sports
                let sportIds = r.sports.map( s => s.id.toString() );
                $( '#inp_sport_ids' ).val( sportIds ).trigger( 'change' );

                // Existing images
                let imgHtml = '';
                ( r.image_paths || [] ).forEach( (url, idx) => {
                    imgHtml += `<div class="position-relative" style="width:80px;">
                        <img src="${url}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                        <button type="button" class="btn btn-xs btn-danger position-absolute top-0 end-0 btn-remove-img" data-index="${idx}" style="padding:1px 5px;">&times;</button>
                    </div>`;
                });
                $( '#existing_images' ).html( imgHtml );

                // Variants
                $( '#variants_container' ).empty();
                ( r.variants || [] ).forEach( v => renderVariantCard( v ) );

                // Stock summary
                renderStockSummary( r.variants || [] );
            },
        });
    }

    loadProduct();

    // ── Remove Existing Image ─────────────────────────────────────────────────
    $( '#existing_images' ).on( 'click', '.btn-remove-img', function() {
        let idx = $( this ).data( 'index' );
        $.ajax({
            url: '{{ route( 'admin.sport_product.removeProductImage' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': productId, 'index': idx },
            success: function() { loadProduct(); },
        });
    });

    // ── Render Variant Card ───────────────────────────────────────────────────
    function renderVariantCard( v ) {
        let stockQty   = v.stock?.quantity ?? 0;
        let stockAvail = Math.max( 0, stockQty - ( v.stock?.reserved_quantity ?? 0 ) );
        let stockBadge = stockAvail > 0
            ? `<span class="badge bg-success">${stockAvail} {{ __( 'sport_product.available' ) }}</span>`
            : `<span class="badge bg-danger">{{ __( 'sport_product.out_of_stock' ) }}</span>`;

        let statusOpts = `
            <option value="10" ${v.status == 10 ? 'selected' : ''}>{{ __( 'datatables.activated' ) }}</option>
            <option value="20" ${v.status == 20 ? 'selected' : ''}>{{ __( 'datatables.suspended' ) }}</option>`;

        let specsHtml = '';
        if ( v.specs && Object.keys( v.specs ).length ) {
            Object.entries( v.specs ).forEach( ([k, val]) => {
                specsHtml += `<span class="badge bg-outline-secondary me-1 mb-1">${k}: ${val}</span>`;
            });
            specsHtml = `<div class="mb-2">${specsHtml}</div>`;
        }

        let existingImgHtml = v.image_path
            ? `<div class="d-flex align-items-center gap-2 mb-2">
                   <div class="position-relative" style="width:64px;flex-shrink:0;">
                       <img src="${v.image_path}" style="width:64px;height:64px;object-fit:cover;border-radius:6px;display:block;">
                       <button type="button" class="btn btn-danger btn-xs btn-remove-var-img position-absolute top-0 end-0" data-id="${v.encrypted_id}" style="padding:1px 4px;line-height:1;">&times;</button>
                   </div>
                   <small class="text-muted">{{ __( 'sport_product.variant_image' ) }}</small>
               </div>`
            : '';

        let card = $(`
            <div class="card card-bordered mb-3 variant-card" data-id="${v.encrypted_id}">
                <div class="card-inner p-3">

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.name' ) }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm ev-name" value="${v.name ?? ''}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.sku' ) }}</label>
                            <input type="text" class="form-control form-control-sm ev-sku" value="${v.sku ?? ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.status' ) }}</label>
                            <select class="form-select form-select-sm ev-status">${statusOpts}</select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.price' ) }} <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">RM</span>
                                <input type="number" step="0.01" min="0" class="form-control ev-price" value="${parseFloat( v.price || 0 ).toFixed(2)}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.compare_price' ) }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">RM</span>
                                <input type="number" step="0.01" min="0" class="form-control ev-compare-price" value="${v.compare_price ? parseFloat( v.compare_price ).toFixed(2) : ''}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __( 'sport_product.stock' ) }}</label>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                ${stockBadge}
                                <small class="text-muted">(${stockQty} {{ __( 'sport_product.quantity' ) }})</small>
                            </div>
                        </div>
                    </div>

                    ${specsHtml}

                    <div class="mt-3 pt-2 border-top mb-3">
                        <label class="form-label form-label-sm mb-2">{{ __( 'sport_product.variant_image' ) }}</label>
                        ${existingImgHtml}
                        <div class="var-dz dropzone" style="min-height:0;">
                            <div class="dz-message needsclick py-2 text-center">
                                <em class="icon ni ni-img fs-3 text-muted d-block mb-1"></em>
                                <small class="text-muted">${v.image_path ? 'Replace image' : '{{ __("template.drop_files_or_click_to_upload") }}'}</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-warning btn-adjust-stock" data-id="${v.encrypted_id}" data-name="${v.name}">
                                <em class="icon ni ni-coins"></em> {{ __( 'sport_product.adjust_stock' ) }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-variant" data-id="${v.encrypted_id}">
                                <em class="icon ni ni-trash"></em> {{ __( 'template.delete' ) }}
                            </button>
                        </div>
                        <button class="btn btn-sm btn-primary btn-update-variant" data-id="${v.encrypted_id}">
                            {{ __( 'template.save_changes' ) }}
                        </button>
                    </div>

                </div>
            </div>
        `);
        $( '#variants_container' ).append( card );
        initVariantCardDropzone( card );
    }

    function initVariantCardDropzone( card ) {
        let dzEl = card.find( '.var-dz' )[0];
        if ( !dzEl ) return;
        new Dropzone( dzEl, {
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
                    card.data( 'new-image', r.data.file );
                }
            },
            removedfile: function( file ) {
                if ( file.serverPath ) card.data( 'new-image', '' );
                if ( file.previewElement ) file.previewElement.remove();
            },
        });
    }

    function renderStockSummary( variants ) {
        if ( !variants.length ) {
            $( '#stock_summary' ).html( '<p class="text-muted small">No variants yet.</p>' );
            return;
        }
        let html = '<div class="list-group list-group-flush">';
        variants.forEach( v => {
            let qty   = v.stock?.quantity ?? 0;
            let avail = Math.max( 0, qty - ( v.stock?.reserved_quantity ?? 0 ) );
            let color = avail > 0 ? 'text-success' : 'text-danger';
            html += `<div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                <span class="small">${v.name}</span>
                <span class="small fw-bold ${color}">${avail} / ${qty}</span>
            </div>`;
        });
        html += '</div><small class="text-muted d-block mt-1">Available / Total</small>';
        $( '#stock_summary' ).html( html );
    }

    // ── Update Product ────────────────────────────────────────────────────────
    $( '#btn_update_product' ).on( 'click', function() {
        let btn = $( this ).prop( 'disabled', true ).text( '...' );

        let fd = new FormData();
        fd.append( '_token',      '{{ csrf_token() }}' );
        fd.append( 'id',          productId );
        fd.append( 'name',        $( '#inp_name' ).val() );
        fd.append( 'description', $( '#inp_description' ).val() );
        fd.append( 'category_id', $( '#inp_category_id' ).val() );
        fd.append( 'sequence',    $( '#inp_sequence' ).val() );
        fd.append( 'status',      $( '#inp_status' ).val() );
        $( '#inp_sport_ids' ).val().forEach( id => fd.append( 'sport_ids[]', id ) );

        $.ajax({
            url: '{{ route( 'admin.sport_product.updateProduct' ) }}',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function(r) {
                btn.prop( 'disabled', false ).text( '{{ __( "template.update" ) }}' );
                $( '#modal_success .caption-text' ).html( r.message );
                modalSuccess.toggle();
            },
            error: function(xhr) {
                btn.prop( 'disabled', false ).text( '{{ __( "template.update" ) }}' );
                alert( xhr.responseJSON?.message ?? 'Error' );
            },
        });
    });

    // ── Update Variant ────────────────────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-update-variant', function() {
        let btn  = $( this ).prop( 'disabled', true ).text( '...' );
        let card = $( this ).closest( '.variant-card' );
        let id   = $( this ).data( 'id' );

        let fd = new FormData();
        fd.append( '_token',        '{{ csrf_token() }}' );
        fd.append( 'id',            id );
        fd.append( 'name',          card.find( '.ev-name' ).val() );
        fd.append( 'sku',           card.find( '.ev-sku' ).val() );
        fd.append( 'price',         card.find( '.ev-price' ).val() );
        fd.append( 'compare_price', card.find( '.ev-compare-price' ).val() );
        fd.append( 'status',        card.find( '.ev-status' ).val() );

        let newImage = card.data( 'new-image' ) || '';
        if ( newImage ) fd.append( 'image', newImage );

        $.ajax({
            url: '{{ route( 'admin.sport_product.updateVariant' ) }}',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function() {
                btn.prop( 'disabled', false ).text( '{{ __( "template.save_changes" ) }}' );
                loadProduct();
            },
            error: function( xhr ) {
                btn.prop( 'disabled', false ).text( '{{ __( "template.save_changes" ) }}' );
                alert( xhr.responseJSON?.message ?? 'Error' );
            },
        });
    });

    // ── Remove Existing Variant Image ─────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-remove-var-img', function() {
        let id = $( this ).data( 'id' );
        if ( !confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) return;
        $.ajax({
            url: '{{ route( 'admin.sport_product.removeVariantImage' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': id },
            success: function() { loadProduct(); },
            error: function(xhr) { alert( xhr.responseJSON?.message ?? 'Error' ); },
        });
    });

    // ── Delete Variant ────────────────────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-delete-variant', function() {
        let id = $( this ).data( 'id' );
        if ( !confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) return;
        $.ajax({
            url: '{{ route( 'admin.sport_product.deleteVariant' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': id },
            success: function() { loadProduct(); },
        });
    });

    // ── Adjust Stock Modal ────────────────────────────────────────────────────
    $( '#variants_container' ).on( 'click', '.btn-adjust-stock', function() {
        $( '#modal_stock_variant_id' ).val( $( this ).data( 'id' ) );
        $( '#modal_stock_variant_name' ).text( $( this ).data( 'name' ) );
        $( '#modal_stock_qty, #modal_stock_notes' ).val( '' );
        $( '#modal_stock_type' ).val( 'restock' );
        $( '#modal_stock' ).modal( 'show' );
    });

    $( '#btn_adjust_stock' ).on( 'click', function() {
        $.ajax({
            url: '{{ route( 'admin.sport_product.adjustStock' ) }}',
            type: 'POST',
            data: {
                '_token':         '{{ csrf_token() }}',
                'variant_id':     $( '#modal_stock_variant_id' ).val(),
                'quantity_change': $( '#modal_stock_qty' ).val(),
                'type':           $( '#modal_stock_type' ).val(),
                'notes':          $( '#modal_stock_notes' ).val(),
            },
            success: function(r) {
                $( '#modal_stock' ).modal( 'hide' );
                loadProduct();
            },
            error: function(xhr) { alert( xhr.responseJSON?.message ?? 'Error' ); },
        });
    });

    // ── Add Variant Modal ─────────────────────────────────────────────────────
    $( '#btn_add_variant' ).on( 'click', function() {
        $( '#nv_name, #nv_sku, #nv_price, #nv_compare_price' ).val( '' );
        $( '#nv_stock' ).val( 0 );
        $( '#nv_status' ).val( 10 );
        $( '#nv_specs_container' ).empty();
        nvDz.removeAllFiles( true );
        $( '#modal_add_variant' ).modal( 'show' );
    });

    $( '#btn_nv_add_spec' ).on( 'click', function() {
        let tpl = document.getElementById( 'spec_tpl' ).content.cloneNode( true );
        $( '#nv_specs_container' ).append( tpl );
    });

    $( '#nv_specs_container' ).on( 'click', '.btn-remove-spec', function() {
        $( this ).closest( '.spec-row' ).remove();
    });

    $( '#btn_save_variant' ).on( 'click', function() {
        let specs = {};
        $( '#nv_specs_container .spec-row' ).each( function() {
            let k = $( this ).find( '.spec-key' ).val().trim();
            let v = $( this ).find( '.spec-value' ).val().trim();
            if ( k ) specs[k] = v;
        });

        let fd = new FormData();
        fd.append( '_token',        '{{ csrf_token() }}' );
        fd.append( 'product_id',    productId );
        fd.append( 'name',          $( '#nv_name' ).val() );
        fd.append( 'sku',           $( '#nv_sku' ).val() );
        fd.append( 'price',         $( '#nv_price' ).val() );
        fd.append( 'compare_price', $( '#nv_compare_price' ).val() );
        fd.append( 'initial_stock', $( '#nv_stock' ).val() );
        fd.append( 'status',        $( '#nv_status' ).val() );
        fd.append( 'specs',         JSON.stringify( specs ) );

        let nvImgFile = nvDz.files.find( f => f.serverPath );
        if ( nvImgFile ) fd.append( 'image', nvImgFile.serverPath );

        $.ajax({
            url: '{{ route( 'admin.sport_product.createVariant' ) }}',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function(r) {
                $( '#modal_add_variant' ).modal( 'hide' );
                loadProduct();
            },
            error: function(xhr) { alert( xhr.responseJSON?.message ?? 'Error' ); },
        });
    });
});
</script>
