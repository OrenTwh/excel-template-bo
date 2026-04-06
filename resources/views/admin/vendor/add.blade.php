<?php
$vendor_create = 'vendor_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3">
                    <label>{{ __( 'datatables.photo' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vendor_create }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_name" class="col-sm-5 col-form-label">{{ __( 'vendor.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_email" class="col-sm-5 col-form-label">{{ __( 'vendor.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'vendor.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <!-- <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_website" class="col-sm-5 col-form-label">{{ __( 'vendor.website' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_website">
                        <div class="invalid-feedback"></div>
                    </div>
                </div> -->
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_type" class="col-sm-5 col-form-label">{{ __( 'vendor.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_create }}_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vendor.type' ) ] ) }}</option>
                            <option value="1">{{ __( 'vendor.parts' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'vendor.address_detail' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_address_1" class="col-sm-5 col-form-label">{{ __( 'vendor.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_create }}_address_1"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_address_2" class="col-sm-5 col-form-label">{{ __( 'vendor.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_create }}_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_city" class="col-sm-5 col-form-label">{{ __( 'vendor.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_postcode" class="col-sm-5 col-form-label">{{ __( 'vendor.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_create }}_postcode">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_state" class="col-sm-5 col-form-label">{{ __( 'vendor.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_create }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vendor.state' ) ] ) }}</option>
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Malacca">Malacca</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Penang">Penang</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Labuan">Labuan</option>
                            <option value="Putrajaya">Putrajaya</option>
                            <option value="Perak">Perak</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if ( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_country" class="col-sm-5 col-form-label">{{ __( 'vendor.country' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_create }}_country">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vendor.country' ) ] ) }}</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Thailand">Thailand</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <hr>
                <div class="mb-3 row">
                    <label for="{{ $vendor_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'vendor.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_create }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vendor_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vendor_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vc = '#{{ $vendor_create }}',
            fileID = '';

        $( vc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vendor.index' ) }}';
        } );

        $( vc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'photo', fileID );
            formData.append( 'name', $( vc + '_name' ).val() );
            formData.append( 'email', $( vc + '_email' ).val() );
            formData.append( 'phone_number', $( vc + '_phone_number' ).val() );
            formData.append( 'address_1', $( vc + '_address_1' ).val() );
            formData.append( 'address_2', $( vc + '_address_2' ).val() );
            formData.append( 'city', $( vc + '_city' ).val() );
            formData.append( 'postcode', $( vc + '_postcode' ).val() );
            formData.append( 'state', $( vc + '_state' ).val() );
            // formData.append( 'website', $( vc + '_website' ).val() );
            formData.append( 'remarks', $( vc + '_remarks' ).val() );
            formData.append( 'type', $( vc + '_type' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vendor.createVendor' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.vendor.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( vc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( vc + '_photo', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {
                fileID = null;
                file.previewElement.remove();
            },
            success: function( file, response ) {
                console.log( file );
                console.log( response );
                if ( response.status == 200 )  {
                    fileID = response.data.id;
                }
            }
        } );
    } );
</script>