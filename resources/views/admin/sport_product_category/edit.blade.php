<div class="nk-block">
    <div class="row g-gs">
        <div class="col-md-7">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-head">
                        <h5 class="card-title">{{ __( 'sport_product.category' ) }}</h5>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.name' ) }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inp_name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.parent_category' ) }}</label>
                        <select class="form-control" id="inp_parent_id">
                            <option value="">— {{ __( 'sport_product.parent_category' ) }} —</option>
                            @foreach ( $data['parents'] as $parent )
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.sequence' ) }}</label>
                        <input type="number" class="form-control" id="inp_sequence" value="0" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __( 'sport_product.status' ) }}</label>
                        <select class="form-control" id="inp_status">
                            <option value="10">{{ __( 'datatables.activated' ) }}</option>
                            <option value="20">{{ __( 'datatables.suspended' ) }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="card-head">
                        <h5 class="card-title">{{ __( 'sport_product.images' ) }}</h5>
                    </div>
                    <div id="current_image" class="mb-3" style="display:none;">
                        <img id="img_preview" src="" style="max-height:160px;border-radius:8px;" class="img-fluid">
                    </div>
                    <div id="category-dropzone" class="dropzone" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary" id="btn_submit">{{ __( 'template.update' ) }}</button>
        <a href="{{ route( 'admin.module_parent.sport_product_category.index' ) }}" class="btn btn-secondary">{{ __( 'template.cancel' ) }}</a>
    </div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {

    let categoryId = new URLSearchParams( window.location.search ).get( 'id' );
    let uploadedImage = null;

    $( '#inp_parent_id' ).select2( { theme: 'bootstrap-5', width: '100%', allowClear: true } );

    Dropzone.autoDiscover = false;
    var dz = new Dropzone( '#category-dropzone', {
        url: '{{ route( 'admin.file.upload' ) }}',
        maxFiles: 1,
        maxFilesize: 2,
        acceptedFiles: 'image/jpg,image/jpeg,image/png',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function( file, response ) { if ( response.status == 200 ) uploadedImage = response.data.file; },
        removedfile: function( file ) { uploadedImage = null; file.previewElement.remove(); },
    });

    // Load category
    $.ajax({
        url: '{{ route( 'admin.sport_product_category.oneCategory' ) }}',
        type: 'POST',
        data: { '_token': '{{ csrf_token() }}', 'id': categoryId },
        success: function( r ) {
            $( '#inp_name' ).val( r.name );
            $( '#inp_parent_id' ).val( r.parent_id || '' ).trigger( 'change' );
            $( '#inp_sequence' ).val( r.sequence );
            $( '#inp_status' ).val( r.status );
            if ( r.image_path ) {
                $( '#img_preview' ).attr( 'src', r.image_path );
                $( '#current_image' ).show();
            }
        },
    });

    $( '#btn_submit' ).on( 'click', function() {
        let btn = $( this ).prop( 'disabled', true ).text( '...' );

        let fd = new FormData();
        fd.append( '_token',    '{{ csrf_token() }}' );
        fd.append( 'id',        categoryId );
        fd.append( 'name',      $( '#inp_name' ).val() );
        fd.append( 'parent_id', $( '#inp_parent_id' ).val() );
        fd.append( 'sequence',  $( '#inp_sequence' ).val() );
        fd.append( 'status',    $( '#inp_status' ).val() );
        if ( uploadedImage ) fd.append( 'image', uploadedImage );

        $.ajax({
            url: '{{ route( 'admin.sport_product_category.updateCategory' ) }}',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function( r ) {
                window.location.href = '{{ route( 'admin.module_parent.sport_product_category.index' ) }}';
            },
            error: function( xhr ) {
                btn.prop( 'disabled', false ).text( '{{ __( "template.update" ) }}' );
                let msg = xhr.responseJSON?.message ?? Object.values( xhr.responseJSON?.errors ?? {} ).flat().join( '\n' );
                alert( msg );
            },
        });
    });
});
</script>
