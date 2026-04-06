<?php
$nationality_edit = 'nationality_edit';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="mb-3">
                <strong>{{ __( 'template.general_info' ) }}</strong>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $nationality_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'nationality.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $nationality_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $nationality_edit }}_symbol" class="col-sm-5 col-form-label">{{ __( 'nationality.symbol' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $nationality_edit }}_symbol">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $nationality_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $nationality_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        getNationality();

        let ue = '#{{ $nationality_edit }}';

        $( ue + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.nationality.index' ) }}';
        } );

        $( ue + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( ue + '_name' ).val() );
            formData.append( 'symbol', $( ue + '_symbol' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.nationality.updateNationality' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.nationality.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ue + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );

        function getNationality() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.nationality.oneNationality' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    $( ue + '_name' ).val( response.name );
                    $( ue + '_symbol' ).val( response.symbol );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>