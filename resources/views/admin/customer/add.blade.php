<?php
$customer_create = 'customer_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_name" class="col-sm-5 col-form-label">{{ __( 'customer.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_pic_name" class="col-sm-5 col-form-label">{{ __( 'customer.pic_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_pic_name" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_email" class="col-sm-5 col-form-label">{{ __( 'customer.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="email" class="form-control" id="{{ $customer_create }}_email" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'customer.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_phone_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_phone_number_2" class="col-sm-5 col-form-label">{{ __( 'customer.phone_number_2' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_phone_number_2" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'customer.address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_address_1" class="col-sm-5 col-form-label">{{ __( 'customer.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $customer_create }}_address_1" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_address_2" class="col-sm-5 col-form-label">{{ __( 'customer.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $customer_create }}_address_2" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_postcode" class="col-sm-5 col-form-label">{{ __( 'customer.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_state" class="col-sm-5 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $customer_create }}_state" >
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
                    <label for="{{ $customer_create }}_city" class="col-sm-5 col-form-label">{{ __( 'customer.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $customer_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'customer.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $customer_create }}_remarks" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $customer_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $customer_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let cc = '#{{ $customer_create }}';

        $( cc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.customer.index' ) }}';
        } );

        $( cc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( cc + '_name' ).val() );
            formData.append( 'pic_name', $( cc + '_pic_name' ).val() );
            formData.append( 'phone_number', $( cc + '_phone_number' ).val() );
            formData.append( 'phone_number_2', $( cc + '_phone_number_2' ).val() );
            formData.append( 'email', $( cc + '_email' ).val() );
            formData.append( 'address_1', $( cc + '_address_1' ).val() );
            formData.append( 'address_2', $( cc + '_address_2' ).val() );
            formData.append( 'postcode', $( cc + '_postcode' ).val() );
            formData.append( 'state', $( cc + '_state' ).val() );
            formData.append( 'city', $( cc + '_city' ).val() );
            formData.append( 'remarks', $( cc + '_remarks' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.customer.createCustomer' ) }}',
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
                            $( cc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
    } );
</script>