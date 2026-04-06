<?php
$buyer_edit = 'buyer_edit';
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
                    <label for="{{ $buyer_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'buyer.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $buyer_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'buyer.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $buyer_edit }}_phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'buyer.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $buyer_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $buyer_edit }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $buyer_edit }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $buyer_edit }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $buyer_edit }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'customer.state' ) ] ) }}</option>
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
                <div class="mb-3 row">
                    <label for="{{ $buyer_edit }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $buyer_edit }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
            </div>
                <div class="text-end">
                    <button id="{{ $buyer_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $buyer_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let be = '#{{ $buyer_edit }}';
        
        $( be + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.buyer.index' ) }}';
        } );

        $( be + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( be + '_name' ).val() );
            formData.append( 'phone_number', $( be + '_phone_number' ).val() );
            formData.append( 'email', $( be + '_email' ).val() );
            formData.append( 'address_1', $( be + '_address_1' ).val() );
            formData.append( 'address_2', $( be + '_address_2' ).val() );
            formData.append( 'city', $( be + '_city' ).val() );
            formData.append( 'state', $( be + '_state' ).val() );
            formData.append( 'postcode', $( be + '_postcode' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.buyer.updateBuyer' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.buyer.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( be + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getBuyer();

        function getBuyer() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.buyer.oneBuyer' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( be + '_name' ).val( response.name );
                    $( be + '_phone_number' ).val( response.phone_number );
                    $( be + '_email' ).val( response.email );
                    $( be + '_address_1' ).val( response.address_1 );
                    $( be + '_address_2' ).val( response.address_2 );
                    $( be + '_postcode' ).val( response.postcode );
                    $( be + '_state' ).val( response.state );
                    $( be + '_city' ).val( response.city );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>