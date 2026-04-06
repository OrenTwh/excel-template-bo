<?php
$company_create = 'company_create';
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
                    <label for="{{ $company_create }}_name" class="col-sm-5 col-form-label">{{ __( 'company.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_registration_no" class="col-sm-5 col-form-label">{{ __( 'company.registration_no' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_registration_no" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_email" class="col-sm-5 col-form-label">{{ __( 'company.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_email" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'company.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_phone_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_address" class="col-sm-5 col-form-label">{{ __( 'company.address' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $company_create }}_address" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_bank_name" class="col-sm-5 col-form-label">{{ __( 'company.bank_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_bank_name" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $company_create }}_account_no" class="col-sm-5 col-form-label">{{ __( 'company.account_no' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $company_create }}_account_no" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $company_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $company_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let cc = '#{{ $company_create }}';

        $( cc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.company.index' ) }}';
        } );

        $( cc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( cc + '_name' ).val() );
            formData.append( 'registration_no', $( cc + '_registration_no' ).val() );
            formData.append( 'email', $( cc + '_email' ).val() );
            formData.append( 'phone_number', $( cc + '_phone_number' ).val() );
            formData.append( 'address', $( cc + '_address' ).val() );
            formData.append( 'bank_name', $( cc + '_bank_name' ).val() );
            formData.append( 'account_no', $( cc + '_account_no' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.company.createCompany' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.company.index' ) }}';
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