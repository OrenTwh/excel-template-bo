<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.orders' ) }}</h3>
        </div><!-- .nk-block-head-content -->
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
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'order.order_date' ) ] ),
        'id' => 'order_date',
        'title' => __( 'order.order_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'order.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.grade' ) ] ),
        'id' => 'grade',
        'title' => __( 'order.grade' ),
    ],
    [
        'type' => 'default',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.weight' ) ] ),
        'id' => 'weight',
        'title' => __( 'order.weight' ),
    ],
    [
        'type' => 'default',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.rate' ) ] ),
        'id' => 'rate',
        'title' => __( 'order.rate' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
];
?>

<x-data-tables id="order_item_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#order_item_table',
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
            url: '{{ route( 'admin.order.allOrderItems' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'orders',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'order' },
            { data: 'order' },
            { data: 'grade' },
            { data: 'weight' },
            { data: 'rate' },
            { data: 'status' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "order_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    console.log(data)
                    return data ? data.order_date : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "reference" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data.reference : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return statusMapper[data];
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;
    exportPath = '{{ route( 'admin.order.export' ) }}';

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#order_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.order.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrderStatus' ) }}',
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
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>