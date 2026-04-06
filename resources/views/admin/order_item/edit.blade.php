<?php
$order_edit = 'order_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row gx-5">
            <div class="col-md-6 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_reference" class="col-sm-4 col-form-label">{{ __( 'booking.reference' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_reference" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_owner" class="col-sm-4 col-form-label">{{ __( 'order.owner_name' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_owner" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.owner_name' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_farm" class="col-sm-4 col-form-label">{{ __( 'order.farm' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_farm" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.farm' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_buyer" class="col-sm-4 col-form-label">{{ __( 'order.buyer' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_buyer" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.buyer' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_order_date" class="col-sm-4 col-form-label">{{ __( 'order.order_date' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_order_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div id="order_items_section">
                    <div class="text-center mt-4" id="order_item_add">
                    </div>
                </div>

                <div class="text-center mt-2 mb-2" id="order_details_add">
                    <em class="icon ni ni-plus-round address-icon order-details-add"></em>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_subtotal" class="col-sm-4 col-form-label">{{ __( 'order.subtotal' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_subtotal" >
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_total" class="col-sm-4 col-form-label">{{ __( 'order.total' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_total">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                {{-- <div class="mb-3 row">
                    <div class="col-sm-6">
                    </div>
                    <p class="col-sm-4 col-form-label" >Subtotal: <span id="{{ $order_edit }}_subotal"></span> </p>
                </div>

                <div class="mb-3 row">
                    <div class="col-sm-6">
                    </div>
                    <p class="col-sm-4 col-form-label" >Total: <span id="{{ $order_edit }}_total"></span> </p>
                </div> --}}
            </div>
        </div>
        <div class="text-end">
            <button id="{{ $order_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $order_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let oe = '#{{ $order_edit }}',
            oeIndex = 0;

        $( document ).on( 'click', '.order-details-remove', function() {
            
            let id = $( this ).data( 'id' );

            $( '#order_details_' + id ).remove();

            oeIndex-=1;
        } );

        $( document ).on( 'click', '.order-details-add', function() {

            $( renderOrderItems( true ) ).insertBefore( '#order_item_add' );

            oeIndex+=1;
        } );

        function renderOrderItems( removeEnabled ) {

            let html = 
            `
            <div class="order-details" id="order_details_` + oeIndex + `" data-id="` + oeIndex + `">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mt-2 mb-2">{{ __('order.order_details') }} ` + (oeIndex + 1) + `</h5>
            `;

            removeButton = removeEnabled ? html +=
            `
                    <div class="mb-1">
                        <em class="icon ni ni-trash address-icon order-details-remove" data-id="` + oeIndex + `"></em>
                    </div>
            `
            :
            html +=
            ``
            ;

            html += 
            `
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit}}_grade" class="col-sm-4 col-form-label">{{ __( 'order.grade' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit}}_grade" >
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit}}_weight" class="col-sm-4 col-form-label">{{ __( 'order.weight' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit}}_weight" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit}}_rate" class="col-sm-4 col-form-label">{{ __( 'order.rate' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit}}_rate" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            `;

            return html;
        }

        let orderDate = $( oe + '_order_date' ).flatpickr();

        $( oe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
        } );

        $( oe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'reference', $( oe + '_reference' ).val() );
            formData.append( 'owner', $( oe + '_owner' ).val() );
            formData.append( 'farm', $( oe + '_farm' ).val() );
            formData.append( 'buyer', $( oe + '_buyer' ).val() );
            formData.append( 'order_date', $( oe + '_order_date' ).val() );
            formData.append( 'grade', $( oe + '_grade' ).val() );
            formData.append( 'weight', $( oe + '_weight' ).val() );
            formData.append( 'subtotal', $( oe + '_subtotal' ).val() );
            formData.append( 'total', $( oe + '_total' ).val() );
            formData.append( 'rate', $( oe + '_rate' ).val() );
            let orderItems = [];
            $( '.order-details' ).each( function( i, v ) {
                orderItems.push( {
                    'grade': $( v ).find( oe + '_grade' ).val(),
                    'weight': $( v ).find( oe + '_weight' ).val(),
                    'rate': $( v ).find( oe + '_rate' ).val(),
                } );
            } );
            formData.append( 'order_items', JSON.stringify( orderItems ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrder' ) }}',
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
                            $( oe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

        let ownerSelect2 = $( oe + '_owner' ).select2( {
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

        let farmSelect2 = $( oe + '_farm' ).select2( {
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

        let buyerSelect2 = $( oe + '_buyer' ).select2( {
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

        function calculateCustomerTotalAmount() {

            let customerQuantity = $( oe + '_customer_quantity' ).val(),
                customerRate = $( oe + '_customer_rate' ).val();

            let customerTotalAmount = parseFloat( customerQuantity ) * parseFloat( customerRate );

            $( oe + '_customer_total_amount' ).val( customerTotalAmount.toFixedDown() );

            calculateDriverTotalAmount();
        }

        function calculateDriverTotalAmount() {

            let driverQuantity = $( oe + '_driver_quantity' ).val(),
                driverRate = $( oe + '_driver_rate' ).val(),
                driverPercentage = $( oe + '_driver_percentage' ).val();

            let driverTotalAmount = parseFloat( driverQuantity ) * parseFloat( driverRate );

            $( oe + '_driver_total_amount' ).val( driverTotalAmount.toFixedDown() );
            $( oe + '_driver_final_amount' ).val( ( driverTotalAmount * parseFloat( driverPercentage ) / 100 ).toFixedDown() );
        }

        getOrder();

        function getOrder() {

            Dropzone.autoDiscover = false;

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.order.oneOrder' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( oe + '_reference' ).val( response.reference );
                    orderDate.setDate( response.order_date );

                    if ( response.owner ) {
                        let option1 = new Option( response.owner.name, response.owner.id, true, true );
                        ownerSelect2.append( option1 );
                        ownerSelect2.trigger( 'change' );
                    }

                    if ( response.farm ) {
                        let option1 = new Option( response.farm.title, response.farm.id, true, true );
                        farmSelect2.append( option1 );
                        farmSelect2.trigger( 'change' );
                    }

                    if ( response.buyer ) {
                        let option1 = new Option( response.buyer.name, response.buyer.id, true, true );
                        buyerSelect2.append( option1 );
                        buyerSelect2.trigger( 'change' );
                    }

                    $( oe + '_subtotal' ).val( response.subtotal );
                    $( oe + '_total' ).val( response.total );

                    $.each( response.order_items, function( i, v ) {
                        
                        $( renderOrderItems( i == 0 ? false : true ) ).insertBefore( '#order_item_add' );

                        oeIndex+=1;
                        
                        $( '#order_details_' + i ).find( oe + '_grade' ).val( v.grade );
                        $( '#order_details_' + i ).find( oe + '_weight' ).val( v.weight );
                        $( '#order_details_' + i ).find( oe + '_rate' ).val( v.rate );
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
        
    } );
</script>