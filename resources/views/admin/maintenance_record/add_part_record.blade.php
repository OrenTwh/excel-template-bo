<?php
$part_record_create = 'part_record_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.part_records' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_part_date" class="col-sm-5 col-form-label">{{ __( 'datatables.purchase_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $part_record_create }}_part_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_reference" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.purchase_bill_reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $part_record_create }}_reference">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_vendor" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vendor' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_create }}_vendor" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vendor' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_part" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.part' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_create }}_part" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.part' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_unit_price" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.unit_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $part_record_create }}_unit_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $part_record_create }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $part_record_create }}_vehicle" data-placeholder="{{ __( 'template.optional' ) }} - {{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'maintenance_record.documents' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $part_record_create }}_documents" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $part_record_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $part_record_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let prc = '#{{ $part_record_create }}',
            fileID = [];

        $( prc + '_part_date' ).flatpickr();

        $( prc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.maintenance_record.partRecords' ) }}';
        } );

        $( prc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'part_date', $( prc + '_part_date' ).val() );
            formData.append( 'reference', $( prc + '_reference' ).val() );
            formData.append( 'vendor', null === $( prc + '_vendor' ).val() ? '' : $( prc + '_vendor' ).val() );
            formData.append( 'part', null === $( prc + '_part' ).val() ? '' : $( prc + '_part' ).val() );
            formData.append( 'unit_price', $( prc + '_unit_price' ).val() );
            formData.append( 'vehicle', null === $( prc + '_vehicle' ).val() ? '' : $( prc + '_vehicle' ).val() );
            formData.append( 'documents', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.createPartRecord' ) }}',
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
                            $( prc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( prc + '_vendor' ).select2( {
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

        $( prc + '_part' ).select2( {
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

        $( prc + '_vehicle' ).select2( {
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

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( prc + '_documents', {
            url: '{{ route( 'admin.file.upload' ) }}',
            acceptedFiles: 'image/jpg,image/jpeg,image/png,application/pdf',
            addRemoveLinks: true,
            init: function() {
                this.on( 'addedfile', function( file ) {

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
            },
            removedfile: function( file ) {
                let previewID = $( file.previewElement ).data( 'id' );

                let index = fileID.indexOf( previewID );

                if ( index !== -1 ) {
                    fileID.splice( index, 1 );
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
    } );
</script>