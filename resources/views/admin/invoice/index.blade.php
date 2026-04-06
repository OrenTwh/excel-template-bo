<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.invoices' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add invoice' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.invoice.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan

    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'select_row',
        'title' => '',
    ],
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.sales_order' ) ] ),
        'id' => 'sales_order',
        'title' => __( 'invoice.sales_order' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'invoice.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.salesman' ) ] ),
        'id' => 'salesman',
        'title' => __( 'invoice.salesman' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.customer' ) ] ),
        'id' => 'customer',
        'title' => __( 'invoice.customer' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.supplier' ) ] ),
        'id' => 'supplier',
        'title' => __( 'invoice.supplier' ),
    ],
    [
        'type' => 'default',
        'id' => 'final_amount',
        'title' => __( 'invoice.final_amount' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="invoice_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#invoice_table',
    dt_table_config = {
        language: {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'previous': '{{ __( "datatables.previous" ) }}',
                'next': '{{ __( "datatables.next" ) }}',
            }
        },
        ajax: {
            url: '{{ route( 'admin.invoice.allInvoices' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'invoices',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'sales_order' },
            { data: 'reference' },
            { data: 'salesman' },
            { data: 'customer' },
            { data: 'supplier' },
            { data: 'final_amount' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [

            {
                // Add checkboxes to the first column
                targets: 0,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                width: '1%',
                render: function (data, type, row, meta) {
                    // Calculate the row number dynamically based on the page info
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "image" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( data ) {

                        return '<img src="' + ( data ? data : '{{ asset( 'admin/images/placeholder.png' ) }}' ) + '" width="75px" />';

                    } else {

                        return '<img src="' + '{{ asset( 'admin/images/placeholder.png' ) }}' + '" width="75px" />'
                        
                    }
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "product" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    if (Array.isArray(data)) {
                        return data
                            .map(item => item.product?.title || '-') 
                            .join('<br>'); 
                    }
                    return '-'; // Return '-' if no valid data
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "supplier" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data.title : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "sales_order" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data.reference : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "salesman" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data.name : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "customer" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data.email : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "reference" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "quantity" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    if (Array.isArray(data)) {
                        return data
                            .map(item => item?.amount || '-') 
                            .join('<br>'); 
                    }
                    return '-'; // Return '-' if no valid data
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return statusMapper[data];
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit invoice', 'delete invoice' ] )
                    let edit, status = '';

                    @can( 'edit invoice' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    if( row['status'] != 14 ){
                        edit += '<li class="dt-convert" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'datatables.to_delivery_order' ) }}</span></a></li>';
                    }
                    @endcan

                    @can( 'delete invoice' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                </ul>
                            </div>
                        </div>
                        `;
                        return html;
                    @else
                    return '-';
                    @endcanany
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.invoice.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.invoice.updateInvoiceStatus' ) }}',
                type: 'POST',
                data: {
                    'id': $( this ).data( 'id' ),
                    'status': $( this ).data( 'status' ),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
            } );
        } );

        $( document ).on( 'click', '.dt-convert', function() {

            $.ajax( {
                url: '{{ route( 'admin.invoice.convertDeliveryOrder' ) }}',
                type: 'POST',
                data: {
                    'id': $( this ).data( 'id' ),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( dc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>