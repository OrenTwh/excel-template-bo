<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'sport_product.products' ) }}</h3>
        </div>
        @can( 'add sport_products' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.sport_product.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<?php
$columns = [
    ['type' => 'default', 'id' => 'select_row',  'title' => ''],
    ['type' => 'default', 'id' => 'dt_no',        'title' => 'No.'],
    ['type' => 'default', 'id' => 'name',          'title' => __('sport_product.name')],
    ['type' => 'select',  'id' => 'category_id',   'title' => __('sport_product.category_label'), 'options' => $data['categories']->pluck('name', 'id')->toArray()],
    ['type' => 'select',  'id' => 'sport_id',       'title' => __('sport_product.sports'), 'options' => $data['sports']->pluck('name', 'id')->toArray()],
    ['type' => 'default', 'id' => 'variants_count', 'title' => __('sport_product.variants')],
    ['type' => 'select',  'id' => 'status',         'title' => __('sport_product.status'), 'options' => $data['status']],
    ['type' => 'default', 'id' => 'dt_action',      'title' => __('datatables.action')],
];
?>

<x-data-tables id="product_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>
window['columns'] = @json( $columns );
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var dt_table,
    dt_table_name = '#product_table',
    dt_table_config = {
        language: {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': { 'previous': '{{ __( "datatables.previous" ) }}', 'next': '{{ __( "datatables.next" ) }}' }
        },
        ajax: {
            url: '{{ route( 'admin.sport_product.allProducts' ) }}',
            data: function( d ) {
                d['_token']      = '{{ csrf_token() }}';
                d['status']      = window['status'];
                d['category_id'] = window['category_id'];
                d['sport_id']    = window['sport_id'];
            },
            dataSrc: 'products',
        },
        lengthMenu: [[10, 25], [10, 25]],
        order: [[2, 'asc']],
        columns: [
            { data: null }, { data: null },
            { data: 'name' }, { data: 'category' }, { data: 'sports' },
            { data: 'variants_count' }, { data: 'status' }, { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: 0, orderable: false, className: 'text-center',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false, width: '1%',
                render: function(data, type, row, meta) { return dt_table.page.info().start + meta.row + 1; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function(data, type, row) {
                    let img = row.image_paths && row.image_paths.length ? `<img src="${row.image_paths[0]}" style="width:36px;height:36px;object-fit:cover;border-radius:4px;margin-right:8px;">` : '';
                    return `<div class="d-flex align-items-center">${img}<span>${data ?? '-'}</span></div>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "category_id" ) }}' ),
                orderable: false,
                render: function(data, type, row) { return row.category ? row.category.name : '—'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "sport_id" ) }}' ),
                orderable: false,
                render: function(data, type, row) {
                    if (!row.sports || !row.sports.length) return '—';
                    return row.sports.map(s => `<span class="badge bg-outline-primary me-1">${s.name}</span>`).join('');
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "variants_count" ) }}' ),
                orderable: false,
                render: function(data, type, row) {
                    if ( !row.variants || !row.variants.length ) return '<span class="text-muted small">—</span>';
                    return row.variants.map( v =>
                        `<span class="badge bg-outline-secondary me-1 mb-1">${v.name} <span class="text-muted">RM ${parseFloat(v.price).toFixed(2)}</span></span>`
                    ).join('');
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function(data, type, row) {
                    return data == 10
                        ? '<span class="badge bg-success">{{ __( "datatables.activated" ) }}</span>'
                        : '<span class="badge bg-danger">{{ __( "datatables.suspended" ) }}</span>';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false, width: '1%', className: 'text-center',
                render: function(data, type, row) {
                    @canany( ['edit sport_products', 'delete sport_products'] )
                    let edit = '', status = '';
                    @can( 'edit sport_products' )
                    edit = `<li class="dt-edit" data-id="${row.encrypted_id}"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>`;
                    @endcan
                    @can( 'delete sport_products' )
                    status = row.status == 10
                        ? `<li class="dt-status" data-id="${row.encrypted_id}" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>`
                        : `<li class="dt-status" data-id="${row.encrypted_id}" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>`;
                    @endcan
                    return `<div class="dropdown"><a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a><div class="dropdown-menu"><ul class="link-list-opt">${edit}${status}</ul></div></div>`;
                    @else
                    return '-';
                    @endcanany
                },
            },
        ],
    },
    table_no = 0,
    timeout  = null;

document.addEventListener( 'DOMContentLoaded', function() {
    $( document ).on( 'click', '.dt-edit', function() {
        window.location.href = '{{ route( 'admin.sport_product.edit' ) }}?id=' + $( this ).data( 'id' );
    });

    $( document ).on( 'click', '.dt-status', function() {
        $.ajax({
            url: '{{ route( 'admin.sport_product.updateProductStatus' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': $( this ).data( 'id' ), 'status': $( this ).data( 'status' ) },
            success: function(r) {
                dt_table.draw( false );
                $( '#modal_success .caption-text' ).html( r.message );
                modalSuccess.toggle();
            },
        });
    });
});
</script>
<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
