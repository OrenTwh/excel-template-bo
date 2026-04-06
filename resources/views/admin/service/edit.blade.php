<?php
$service_edit = 'service_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.services' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $service_edit }}_service_name" class="col-sm-5 col-form-label">{{ __( 'service.service_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_edit }}_service_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_edit }}_description" class="col-sm-5 col-form-label">{{ __( 'service.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $service_edit }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_edit }}_service_interval" class="col-sm-5 col-form-label">{{ __( 'service.service_interval' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $service_edit }}_service_interval">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_edit }}_reminder_activation" class="col-sm-5 col-form-label">{{ __( 'service.reminder_activation' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $service_edit }}_reminder_activation">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_edit }}_reminder_frequency" class="col-sm-5 col-form-label">{{ __( 'service.reminder_frequency' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_edit }}_reminder_frequency">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'service.reminder_frequency' ) ] ) }}</option>
                            <option value="1">{{ __( 'service.daily' ) }}</option>
                            <option value="2">{{ __( 'service.weekly' ) }}</option>
                            <option value="3">{{ __( 'service.monthly' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $service_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $service_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let se = '#{{ $service_edit }}';

        $( se + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.service.index' ) }}';
        } );

        $( se + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'service_name', $( se + '_service_name' ).val() );
            formData.append( 'description', $( se + '_description' ).val() );
            formData.append( 'service_interval', $( se + '_service_interval' ).val() );
            formData.append( 'reminder_activation', $( se + '_reminder_activation' ).val() );
            formData.append( 'reminder_frequency', $( se + '_reminder_frequency' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.service.updateService' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.service.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( se + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getService();

        function getService() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.service.oneService' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( se + '_service_name' ).val( response.name );
                    $( se + '_description' ).val( response.description );
                    $( se + '_service_interval' ).val( response.service_interval );
                    $( se + '_reminder_activation' ).val( response.reminder_activation );
                    $( se + '_reminder_frequency' ).val( response.reminder_frequency );

                    $( 'body' ).loading( 'stop' );
                }
            } );
        }
    } );
</script>