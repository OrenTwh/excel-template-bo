<?php
$user_friend_edit = 'user_friend_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.user_friends' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $user_friend_edit }}_user_id" class="col-sm-5 col-form-label">{{ __( 'user_friend.user' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_friend_edit }}_user_id" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'user_friend.user' ) ] ) }}">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user_friend.user' ) ] ) }}</option>
                            @foreach( $data['users'] as $user )
                                <option value="{{ $user->encrypted_id }}">{{ $user->fullname }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $user_friend_edit }}_friend_id" class="col-sm-5 col-form-label">{{ __( 'user_friend.friend' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_friend_edit }}_friend_id" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'user_friend.friend' ) ] ) }}">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'user_friend.friend' ) ] ) }}</option>
                            @foreach( $data['users'] as $user )
                                <option value="{{ $user->encrypted_id }}">{{ $user->fullname }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $user_friend_edit }}_status" class="col-sm-5 col-form-label">{{ __( 'datatables.status' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $user_friend_edit }}_status">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'datatables.status' ) ] ) }}</option>
                            @foreach( $data['status'] as $key => $label )
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $user_friend_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $user_friend_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $user_friend_edit }}';

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.user_friend.index' ) }}';
        } );

        let userSelect2 = $( de + '_user_id' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true,
        } );

        let friendSelect2 = $( de + '_friend_id' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true,
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'user_id', $( de + '_user_id' ).val() );
            formData.append( 'friend_id', $( de + '_friend_id' ).val() );
            formData.append( 'status', $( de + '_status' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.user_friend.updateUserFriend' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.user_friend.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( de + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getUserFriend();

        function getUserFriend() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.user_friend.oneUserFriend' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( de + '_user_id' ).val( response.user_id ).trigger( 'change' );
                    $( de + '_friend_id' ).val( response.friend_id ).trigger( 'change' );
                    $( de + '_status' ).val( response.status );
                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>
