<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.part_records' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add maintenance_records' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.maintenance_record.addPartRecord' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'maintenance_record.purchase_date' ) ] ),
        'id' => 'part_date',
        'title' => __( 'maintenance_record.purchase_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'maintenance_record.purchase_bill_reference' ),
        'id' => 'reference',
        'title' => __( 'maintenance_record.purchase_bill_reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'maintenance_record.vendor' ),
        'id' => 'vendor',
        'title' => __( 'maintenance_record.vendor' ),
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'maintenance_record.part' ),
        'id' => 'part',
        'title' => __( 'maintenance_record.part' ),
    ],
    [
        'type' => 'default',
        'id' => 'unit_price',
        'title' => __( 'maintenance_record.unit_price' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="maintenance_record_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var dt_table,
    dt_table_name = '#maintenance_record_table',
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
            url: '{{ route( 'admin.maintenance_record.allPartRecords' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'records',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 1, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'local_part_date' },
            { data: 'reference' },
            { data: 'vendor.name' },
            { data: 'part.name' },
            { data: 'unit_price' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "part_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "reference" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "vendor" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "part" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit maintenance_records', 'delete maintenance_records' ] )
                    let edit, status = '';

                    @can( 'edit maintenance_records' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete maintenance_records' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    status = '';
                    
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

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.maintenance_record.editPartRecord' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( '#part_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );
    } );

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>