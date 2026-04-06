<?php
$part_record_edit = 'part_record_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.part_records' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_part_date" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.purchase_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $part_record_edit }}_part_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_reference" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.purchase_bill_reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $part_record_edit }}_reference">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_vendor" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vendor' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_edit }}_vendor" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vendor' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_part" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.part' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_edit }}_part" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.part' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_unit_price" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.unit_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $part_record_edit }}_unit_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_edit }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_edit }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'maintenance_record.documents' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $part_record_edit }}_documents" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $part_record_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $part_record_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getPartRecord();

        let pre = '#{{ $part_record_edit }}',
            fileID = [],
            toBeDeleteFileID = [];

        let partDate = $( pre + '_part_date' ).flatpickr();

        $( pre + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.maintenance_record.partRecords' ) }}';
        } );

        $( pre + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'part_date', $( pre + '_part_date' ).val() );
            formData.append( 'reference', $( pre + '_reference' ).val() );
            formData.append( 'vendor', null === $( pre + '_vendor' ).val() ? '' : $( pre + '_vendor' ).val() );
            formData.append( 'part', null === $( pre + '_part' ).val() ? '' : $( pre + '_part' ).val() );
            formData.append( 'unit_price', $( pre + '_unit_price' ).val() );
            formData.append( 'vehicle', null === $( pre + '_vehicle' ).val() ? '' : $( pre + '_vehicle' ).val() );
            formData.append( 'documents', fileID );
            formData.append( 'to_be_delete_documents', toBeDeleteFileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.updatePartRecord' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.maintenance_record.partRecords' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( pre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        let vendorSelect2 = $( pre + '_vendor' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.vendor.allVendors' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.vendors.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );

        let partSelect2 = $( pre + '_part' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.part.allParts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.parts.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );

        let vehicleSelect2 = $( pre + '_vehicle' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.vehicle.allVehicles' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.vehicles.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.license_plate,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );

        function getPartRecord() {

            Dropzone.autoDiscover = false;

            $( 'body' ).loading( { 
                message: '{{ __( 'template.loading' ) }}',
            } );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.onePartRecord' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );

                    if ( response.vendor ) {
                        let option1 = new Option( response.vendor.name, response.vendor.id, true, true );
                        vendorSelect2.append( option1 );
                        vendorSelect2.trigger( 'change' );
                    }

                    if ( response.part ) {
                        let option2 = new Option( response.part.name, response.part.id, true, true );
                        partSelect2.append( option2 );
                        partSelect2.trigger( 'change' );
                    }

                    if ( response.vehicle ) {
                        let option2 = new Option( response.vehicle.license_plate, response.vehicle.id, true, true );
                        vehicleSelect2.append( option2 );
                        vehicleSelect2.trigger( 'change' );
                    }

                    partDate.setDate( response.local_part_date );
                    $( pre + '_reference' ).val( response.reference );
                    $( pre + '_unit_price' ).val( response.unit_price );

                    const dropzone = new Dropzone( pre + '_documents', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        acceptedFiles: 'image/jpg,image/jpeg,image/png,application/pdf',
                        addRemoveLinks: true,
                        init: function() {
                            this.on( 'addedfile', function( file ) {

                                if ( file.hasOwnProperty( 'existing' ) ) {
                                    if ( file.existing ) {
                                        file.previewElement.setAttribute( 'data-id', file.id );
                                        $( file.previewElement ).addClass( 'existing' );
                                    }
                                }

                                if ( file.type ) {
                                    if ( !file.type.match(/image.*/) ) {
                                        this.emit( 'thumbnail', file, '{{ asset( 'admin/images/file_pdf.png' ) }}' );
                                    }
                                } else {
                                    if ( file.fileType == 'pdf' ) {
                                        this.emit( 'thumbnail', file, '{{ asset( 'admin/images/file_pdf.png' ) }}' );
                                    }
                                }

                                $( file.previewElement ).bind( 'click', function() {

                                    if ( file.xhr ) {
                                        window.open( '{{ asset( 'storage') }}/' + JSON.parse( file.xhr.response ).data.file );
                                    }

                                    window.open( file.assetUrl );
                                } );
                            } );

                            let myDropzone = this;

                            response.documents.map( function( v, i ) {
                                let mockFile = { name: v.name, size: 1024, accepted: true, assetUrl: v.path, id: v.id, existing: true }
                                    preview = v.type == 1 ? '{{ asset( 'admin/images/file_pdf.png' ) }}' : v.path;

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, preview );
                            } );
                        },
                        removedfile: function( file ) {

                            let existing = $( file.previewElement ).hasClass( 'existing' );
                            let previewID = $( file.previewElement ).data( 'id' );

                            if ( existing ) {
                                toBeDeleteFileID.push( previewID );
                            } else {
                                let index = fileID.indexOf( previewID );
                                if ( index !== -1 ) {
                                    fileID.splice( index, 1 );
                                }
                            }
                            
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                file.previewElement.setAttribute( 'data-id', response.data.id );
                                fileID.push( response.data.id );
                            }
                        }
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( pre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        }
    } );
</script>