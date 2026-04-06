<?php
$order_create = 'order_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$orderIncrement = $data['order_increment'];
$grade = $data['grade'];
?>

<div class="card">
    <div class="card-inner">
        <div class="row gx-5">
            <div class="col-md-6 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_reference" class="col-sm-4 col-form-label">{{ __( 'booking.reference' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_reference" value="BNS-{{ date( 'Ymd' ) . ( $orderIncrement ) }}" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_owner" class="col-sm-4 col-form-label">{{ __( 'order.owner_name' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_owner" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.owner_name' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_farm" class="col-sm-4 col-form-label">{{ __( 'order.farm' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_farm" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.farm' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_buyer" class="col-sm-4 col-form-label">{{ __( 'order.buyer' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_buyer" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.buyer' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_order_date" class="col-sm-4 col-form-label">{{ __( 'order.order_date' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_order_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 order-details">
                <h5 class="card-title mb-4">{{ __( 'order.order_details' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_grade" class="col-sm-4 col-form-label">{{ __( 'order.grade' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_grade" >
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_weight" class="col-sm-4 col-form-label">{{ __( 'order.weight' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_weight" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_rate" class="col-sm-4 col-form-label">{{ __( 'order.rate' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_rate" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-center mt-2 mb-2" id="order_details_add">
                    <em class="icon ni ni-plus-round address-icon order-details-add"></em>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $order_create }}_subtotal" class="col-sm-4 col-form-label">{{ __( 'order.subtotal' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_subtotal" >
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_total" class="col-sm-4 col-form-label">{{ __( 'order.total' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_total">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="text-end">
            <button id="{{ $order_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $order_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let oc = '#{{ $order_create }}',
                    odIndex = 1;

        $( document ).on( 'click', '.order-details-remove', function() {
            
            let id = $( this ).data( 'id' );

            $( '#order_details_' + id ).remove();

            odIndex-=1;
        } );

        $( oc + '_order_date' ).flatpickr({
            defaultDate: "today",
        });

        $( oc + '_order_date' ).on( 'change', function() {

            const selectedDate = $( this ).val();
    
            const cleanedDate = selectedDate.replace( /-/g, '' );

            const value = `BNS-${cleanedDate}{{ $orderIncrement }}`;

            $( oc + '_reference' ).val( value )
        } );

        $( oc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
        } );

        $( oc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'reference', $( oc + '_reference' ).val() );
            formData.append( 'owner', $( oc + '_owner' ).val() );
            formData.append( 'farm', $( oc + '_farm' ).val() );
            formData.append( 'buyer', $( oc + '_buyer' ).val() );
            formData.append( 'order_date', $( oc + '_order_date' ).val() );
            formData.append( 'grade', $( oc + '_grade' ).val() );
            formData.append( 'weight', $( oc + '_weight' ).val() );
            formData.append( 'subtotal', $( oc + '_subtotal' ).val() );
            formData.append( 'total', $( oc + '_total' ).val() );
            formData.append( 'rate', $( oc + '_rate' ).val() );
            let orderItems = [];
            $( '.order-details' ).each( function( i, v ) {
                orderItems.push( {
                    'grade': $( v ).find( oc + '_grade' ).val(),
                    'weight': $( v ).find( oc + '_weight' ).val(),
                    'rate': $( v ).find( oc + '_rate' ).val(),
                } );
            } );
            formData.append( 'order_items', JSON.stringify( orderItems ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.order.createOrder' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {

                            if ( key.includes( 'pickup_addresses' ) ) {

                                let stringKey = key.split( '.' );

                                $( '#order_details' + stringKey[1] ).find( oc + '_' + stringKey[2] ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );

                                return true;
                            }

                            if ( key.includes( 'dropoff_addresses' ) ) {

                                let stringKey = key.split( '.' );

                                $( '#dropoff_address_' + stringKey[1] ).find( oc + '_' + stringKey[2] ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );

                                return true;
                            }

                            $( oc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                        $( '.form-control.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                        $( '.form-select.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( oc + '_owner' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.owner.allOwners' ) }}',
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

                    data.owners.map( function( v, i ) {
                        processedResult.push( {
                            id: v.owner.id,
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

        $( oc + '_farm' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.farm.allFarms' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        designation: 1,
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.farms.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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

        $( oc + '_buyer' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.buyer.allBuyers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        designation: 1,
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.buyers.map( function( v, i ) {
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

        $( document ).on( 'click', '.order-details-add', function() {

            let html = 
            `
            <div class="order-details" id="order_details_` + odIndex + `" data-id="` + odIndex + `">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mt-2 mb-2">{{ __('order.order_details') }} ` + (odIndex + 1) + `</h5>
                    <div class="mb-1">
                        <em class="icon ni ni-trash address-icon order-details-remove" data-id="` + odIndex + `"></em>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_grade" class="col-sm-4 col-form-label">{{ __( 'order.grade' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_create }}_grade" >
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_weight" class="col-sm-4 col-form-label">{{ __( 'order.weight' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_weight" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_create }}_rate" class="col-sm-4 col-form-label">{{ __( 'order.rate' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_create }}_rate" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            `;

            $( html ).insertBefore( '#order_details_add' );

            odIndex+=1;
        } );

    } );
</script>