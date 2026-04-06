<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'Sport Product Orders' ) }}</h3>
        </div>
    </div>
</div>

<?php
$statusLabels = $data['status_labels'];
$columns = [
    ['type' => 'default', 'id' => 'select_row', 'title' => ''],
    ['type' => 'default', 'id' => 'dt_no',      'title' => 'No.'],
    ['type' => 'default', 'id' => 'created_at', 'title' => __( 'Date' )],
    ['type' => 'default', 'id' => 'user',       'title' => __( 'Customer' )],
    ['type' => 'default', 'id' => 'items_count','title' => __( 'Items' )],
    ['type' => 'default', 'id' => 'total',      'title' => __( 'Total' )],
    ['type' => 'select',  'id' => 'status',     'title' => __( 'Status' ), 'options' => $statusLabels],
    ['type' => 'default', 'id' => 'dt_action',  'title' => __( 'datatables.action' )],
];
?>

<x-data-tables id="order_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>
window['columns'] = @json( $columns );
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var dt_table,
    dt_table_name = '#order_table',
    dt_table_config = {
        language: {
            'lengthMenu':   '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords':  '{{ __( "datatables.zeroRecords" ) }}',
            'info':         '{{ __( "datatables.info" ) }}',
            'infoEmpty':    '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': { 'previous': '{{ __( "datatables.previous" ) }}', 'next': '{{ __( "datatables.next" ) }}' }
        },
        ajax: {
            url: '{{ route( 'admin.sport_product_order.allOrders' ) }}',
            data: function( d ) {
                d['_token'] = '{{ csrf_token() }}';
                d['status'] = window['status'];
            },
            dataSrc: 'orders',
        },
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        order: [[2, 'desc']],
        columns: [
            { data: null }, { data: null },
            { data: 'created_at' }, { data: 'user' },
            { data: 'items_count' }, { data: 'total' },
            { data: 'status' }, { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: 0, orderable: false, className: 'text-center',
                render: function( data, type, row ) {
                    return `<input type="checkbox" class="select-row" data-id="${ row.encrypted_id }">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false, width: '1%',
                render: function( data, type, row, meta ) { return dt_table.page.info().start + meta.row + 1; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_at" ) }}' ),
                render: function( data ) {
                    if ( !data ) return '—';
                    return new Date( data ).toLocaleDateString( 'en-MY', { year: 'numeric', month: 'short', day: 'numeric' } );
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !row.user ) return '<span class="text-muted">—</span>';
                    return `${ row.user.fullname }<small class="text-muted d-block">${ row.user.email }</small>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "items_count" ) }}' ),
                orderable: false, className: 'text-center',
                render: function( data ) { return data ?? '—'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total" ) }}' ),
                render: function( data ) { return data != null ? 'RM ' + parseFloat( data ).toFixed( 2 ) : '—'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data ) {
                    let map = {
                        10: ['warning',   '{{ __( 'Pending' ) }}'],
                        20: ['primary',   '{{ __( 'Confirmed' ) }}'],
                        30: ['success',   '{{ __( 'Completed' ) }}'],
                        40: ['secondary', '{{ __( 'Cancelled' ) }}'],
                    };
                    let [color, label] = map[ data ] ?? ['light', data];
                    return `<span class="badge bg-${ color }">${ label }</span>`;
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false, width: '1%', className: 'text-center',
                render: function( data, type, row ) {
                    @can( 'view sport_products' )
                    return `<div class="dropdown"><a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a><div class="dropdown-menu"><ul class="link-list-opt"><li class="dt-view" data-id="${ row.encrypted_id }"><a href="#"><em class="icon ni ni-eye"></em><span>{{ __( 'template.view' ) }}</span></a></li></ul></div></div>`;
                    @else
                    return '-';
                    @endcan
                },
            },
        ],
    },
    table_no = 0,
    timeout  = null;

document.addEventListener( 'DOMContentLoaded', function() {
    $( document ).on( 'click', '.dt-view', function() {
        window.location.href = '{{ route( 'admin.sport_product_order.view' ) }}?id=' + $( this ).data( 'id' );
    } );
} );
</script>
<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
