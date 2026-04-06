<?php
$vendor_edit = 'vendor_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ) }}</h3>
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
                    <div class="dropzone mb-3" id="{{ $vendor_edit }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'vendor.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'vendor.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'vendor.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <!-- <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_website" class="col-sm-5 col-form-label">{{ __( 'vendor.website' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_website">
                        <div class="invalid-feedback"></div>
                    </div>
                </div> -->
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_type" class="col-sm-5 col-form-label">{{ __( 'vendor.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_edit }}_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vendor.type' ) ] ) }}</option>
                            <option value="1">{{ __( 'vendor.parts' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'vendor.address_detail' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_address_1" class="col-sm-5 col-form-label">{{ __( 'vendor.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_edit }}_address_1"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_address_2" class="col-sm-5 col-form-label">{{ __( 'vendor.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_edit }}_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_city" class="col-sm-5 col-form-label">{{ __( 'vendor.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_postcode" class="col-sm-5 col-form-label">{{ __( 'vendor.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vendor_edit }}_postcode">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vendor_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'vendor.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_edit }}_state" >
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
                    <label for="{{ $vendor_edit }}_country" class="col-sm-5 col-form-label">{{ __( 'vendor.country' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vendor_edit }}_country">
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
                    <label for="{{ $vendor_edit }}_remarks" class="col-sm-5 col-form-label">{{ __( 'vendor.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $vendor_edit }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vendor_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vendor_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getVendor();

        let ve = '#{{ $vendor_edit }}',
            fileID = '';

        $( ve + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vendor.index' ) }}';
        } );

        $( ve + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            if ( fileID ) {
                formData.append( 'photo', fileID );
            }
            formData.append( 'name', $( ve + '_name' ).val() );
            formData.append( 'email', $( ve + '_email' ).val() );
            formData.append( 'phone_number', $( ve + '_phone_number' ).val() );
            formData.append( 'address_1', $( ve + '_address_1' ).val() );
            formData.append( 'address_2', $( ve + '_address_2' ).val() );
            formData.append( 'city', $( ve + '_city' ).val() );
            formData.append( 'postcode', $( ve + '_postcode' ).val() );
            formData.append( 'state', $( ve + '_state' ).val() );
            // formData.append( 'website', $( ve + '_website' ).val() );
            formData.append( 'remarks', $( ve + '_remarks' ).val() );
            formData.append( 'type', $( ve + '_type' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vendor.updateVendor' ) }}',
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
                            $( ve + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        function getVendor() {
            
            Dropzone.autoDiscover = false;

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.vendor.oneVendor' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( ve + '_name' ).val( response.name );
                    $( ve + '_email' ).val( response.email );
                    $( ve + '_phone_number' ).val( response.phone_number );
                    // $( ve + '_website' ).val( response.website );
                    $( ve + '_type' ).val( response.type );

                    if ( response.address_object ) {
                        $( ve + '_address_1' ).val( response.address_object.address_1 );
                        $( ve + '_address_2' ).val( response.address_object.address_2 );
                        $( ve + '_city' ).val( response.address_object.city );
                        $( ve + '_postcode' ).val( response.address_object.postcode );
                        $( ve + '_state' ).val( response.address_object.state );
                    }

                    $( ve + '_remarks' ).val( response.remarks );

                    fileID = response.photo;

                    let imagePath = response.path;

                    const dropzone = new Dropzone( ve + '_photo', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                fileID = response.data.id;
                            }
                        }
                    } );

                    $( 'body' ).loading( 'stop' );
                }
            } );
        }
    } );
</script>