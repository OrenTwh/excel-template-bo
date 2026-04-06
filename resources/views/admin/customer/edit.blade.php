<?php
$customer_edit = 'customer_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'customer.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_pic_name" class="col-sm-5 col-form-label">{{ __( 'customer.pic_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_pic_name" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'customer.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="email" class="form-control" id="{{ $customer_edit }}_email" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'customer.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_phone_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_phone_number_2" class="col-sm-5 col-form-label">{{ __( 'customer.phone_number_2' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_phone_number_2" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'customer.address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $customer_edit }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $customer_edit }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $customer_edit }}_state" >
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
                    <label for="{{ $customer_edit }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_edit }}_remarks" class="col-sm-5 col-form-label">{{ __( 'customer.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_edit }}_remarks" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $customer_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $customer_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let ce = '#{{ $customer_edit }}';

        $( ce + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.customer.index' ) }}';
        } );

        $( ce + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( ce + '_name' ).val() );
            formData.append( 'pic_name', $( ce + '_pic_name' ).val() );
            formData.append( 'phone_number', $( ce + '_phone_number' ).val() );
            formData.append( 'phone_number_2', $( ce + '_phone_number_2' ).val() );
            formData.append( 'email', $( ce + '_email' ).val() );
            formData.append( 'address_1', $( ce + '_address_1' ).val() );
            formData.append( 'address_2', $( ce + '_address_2' ).val() );
            formData.append( 'postcode', $( ce + '_postcode' ).val() );
            formData.append( 'state', $( ce + '_state' ).val() );
            formData.append( 'city', $( ce + '_city' ).val() );
            formData.append( 'remarks', $( ce + '_remarks' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.customer.updateCustomer' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.customer.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ce + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getCompany();

        function getCompany() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.customer.oneCustomer' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( ce + '_name' ).val( response.name );
                    $( ce + '_pic_name' ).val( response.pic_name );
                    $( ce + '_phone_number' ).val( response.phone_number );
                    $( ce + '_phone_number_2' ).val( response.phone_number_2 );
                    $( ce + '_email' ).val( response.email );
                    $( ce + '_address_1' ).val( response.display_address.a1 );
                    $( ce + '_address_2' ).val( response.display_address.a2 );
                    $( ce + '_postcode' ).val( response.display_address.p );
                    $( ce + '_state' ).val( response.display_address.s );
                    $( ce + '_city' ).val( response.display_address.c );
                    $( ce + '_remarks' ).val( response.remarks );

                    $( 'body'   ).loading( 'stop' );
                },
            } );
        }
    } );
</script>