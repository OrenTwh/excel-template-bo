<?php
$owner_edit = 'owner_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.owners' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $owner_edit }}_username" class="col-sm-5 col-form-label">{{ __( 'administrator.username' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $owner_edit }}_username">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $owner_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'administrator.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $owner_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $owner_edit }}_fullname" class="col-sm-5 col-form-label">{{ __( 'administrator.fullname' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $owner_edit }}_fullname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $owner_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'administrator.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <button class="flex-shrink-0 inline-flex items-center input-group-text" type="button">
                                +60
                            </button>
                            <input type="text" class="form-control" id="{{ $owner_edit }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>
                <div class="mb-3 row">
                    <label for="{{ $owner_edit }}_password" class="col-sm-5 col-form-label">{{ __( 'administrator.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $owner_edit }}_password" autocomplete="new-password" placeholder="{{ __( 'template.leave_blank' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $owner_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $owner_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let oe = '#{{ $owner_edit }}';
        
        $( oe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.administrator.index' ) }}';
        } );

        $( oe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'username', $( oe + '_username' ).val() );
            formData.append( 'email', $( oe + '_email' ).val() );
            formData.append( 'fullname', $( oe + '_fullname' ).val() );
            formData.append( 'phone_number', $( oe + '_phone_number' ).val() );
            formData.append( 'password', $( oe + '_password' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.owner.updateOwner' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.owner.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( oe + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getOwner();

        function getOwner() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.owner.oneOwner' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( oe + '_username' ).val( response.name );
                    $( oe + '_email' ).val( response.email );
                    $( oe + '_fullname' ).val( response.fullname );
                    $( oe + '_phone_number' ).val( response.phone_number );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>