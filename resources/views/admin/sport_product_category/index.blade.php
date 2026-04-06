<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'sport_product.categories' ) }}</h3>
        </div>
        @can( 'add sport_products' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.sport_product_category.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
    ['type' => 'default', 'id' => 'select_row', 'title' => ''],
    ['type' => 'default', 'id' => 'dt_no',      'title' => 'No.'],
    ['type' => 'default', 'id' => 'name',        'title' => __('sport_product.name')],
    ['type' => 'default', 'id' => 'parent',      'title' => __('sport_product.parent_category')],
    ['type' => 'select',  'id' => 'status',      'title' => __('sport_product.status'), 'options' => $data['status']],
    ['type' => 'default', 'id' => 'dt_action',   'title' => __('datatables.action')],
];
?>

<x-data-tables id="category_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>
window['columns'] = @json( $columns );
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var dt_table,
    dt_table_name = '#category_table',
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
            url: '{{ route( 'admin.sport_product_category.allCategories' ) }}',
            data: function( d ) {
                d['_token'] = '{{ csrf_token() }}';
                d['status'] = window['status'];
            },
            dataSrc: 'categories',
        },
        lengthMenu: [[10, 25], [10, 25]],
        order: [[2, 'asc']],
        columns: [
            { data: null },
            { data: null },
            { data: 'name' },
            { data: 'parent' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false, width: '1%',
                render: function(data, type, row, meta) {
                    return dt_table.page.info().start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function(data, type, row) {
                    return data ?? '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "parent" ) }}' ),
                orderable: false,
                render: function(data, type, row) {
                    return row.parent ? row.parent.name : '<span class="text-muted">—</span>';
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
                    let edit = '', del = '';
                    @can( 'edit sport_products' )
                    edit = `<li class="dt-edit" data-id="${row.encrypted_id}"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>`;
                    @endcan
                    @can( 'delete sport_products' )
                    del = `<li class="dt-delete" data-id="${row.encrypted_id}"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>`;
                    @endcan
                    return `<div class="dropdown"><a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a><div class="dropdown-menu"><ul class="link-list-opt">${edit}${del}</ul></div></div>`;
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
        window.location.href = '{{ route( 'admin.sport_product_category.edit' ) }}?id=' + $( this ).data( 'id' );
    });

    $( document ).on( 'click', '.dt-delete', function() {
        let id = $( this ).data( 'id' );
        if ( confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) {
            $.ajax({
                url: '{{ route( 'admin.sport_product_category.deleteCategory' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}', 'id': id },
                success: function(r) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( r.message );
                    modalSuccess.toggle();
                },
            });
        }
    });
});
</script>
<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
