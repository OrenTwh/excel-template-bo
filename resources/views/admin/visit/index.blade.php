<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.visits' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add visits' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.visit.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'visit.visit_date' ) ] ),
        'id' => 'visit_date',
        'title' => __( 'visit.visit_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'visit.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'visit.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'visit.user' ) ] ),
        'id' => 'email',
        'title' => __( 'visit.user' ),
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

<x-data-tables id="visit_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#visit_table',
    dt_table_config = {
        language: {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'first': '{{ __( "datatables.first" ) }}',
                'last': '{{ __( "datatables.last" ) }}',
                'next': '{{ __( "datatables.next" ) }}',
                'previous': '{{ __( "datatables.previous" ) }}',
            },
            'search': '{{ __( "datatables.search" ) }}',
        },
        ajax: {
            url: '{{ route( "admin.visit.all" ) }}',
            type: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'visits',
        },
        ordering: true,
        searching: true,
        order: [[ 3, 'desc' ]],
        columnDefs: [
            { targets: 0, orderable: false, searchable: false, className: 'nk-tb-col-check' },
            { targets: 1, orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false },
        ],
        columns: [
            {
                data: null,
                render: function ( data, type, row ) {
                    return `<div class="custom-control custom-control-sm custom-checkbox notext">
                                <input type="checkbox" class="custom-control-input row-selected-checkbox" id="select_row_${row.id}">
                                <label class="custom-control-label" for="select_row_${row.id}"></label>
                            </div>`;
                }
            },
            {
                data: null,
                render: function ( data, type, row, meta ) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'created_at' },
            { data: 'visit_date' },
            { data: 'reference' },
            { data: 'email' },
            {
                data: 'status',
                render: function ( data, type, row ) {
                    let badge = data == 10 ? 'success' : 'danger';
                    return `<span class="badge bg-${badge}">${statusMapper[data]}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function ( data, type, row ) {

                    @canany( [ 'edit visits', 'delete visits' ] )
                    let edit, printQR, statusAction = '';

                    @can( 'edit visits' )
                    edit = '<li class="dt-edit" data-id="' + row.id + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    // Print QR button
                    printQR = '<li class="dt-print-qr" data-id="' + row.id + '"><a href="#"><em class="icon ni ni-printer"></em><span>{{ __( 'visit.print_qr' ) }}</span></a></li>';

                    @can( 'delete visits' )
                    statusAction = row.status == 10 ?
                        '<li class="dt-status" data-id="' + row.id + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                        '<li class="dt-status" data-id="' + row.id + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+printQR+`
                                    `+statusAction+`
                                </ul>
                            </div>
                        </div>
                        `;
                    return html;
                    @else
                    return '-';
                    @endcanany
                }
            },
        ],
    },

table_no = 0,
timeout = null;
document.addEventListener( 'DOMContentLoaded', function() {
    // Edit functionality
    $( document ).on( 'click', '.dt-edit', function() {
        window.location.href = '{{ route( 'admin.visit.edit', '' ) }}/' + $( this ).data( 'id' );
    });

    // Status functionality
    $( document ).on( 'click', '.dt-status', function() {
        let id = $( this ).data( 'id' );
        let status = $( this ).data( 'status' );

        $.ajax({
            url: '{{ route( "admin.visit.updateStatus" ) }}',
            type: 'POST',
            headers: {
                '_token': '{{ csrf_token() }}',
            },
            data: {
                id: id,
                status: status,
                _token: '{{ csrf_token() }}',
            },
            success: function( response ) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __( "template.success" ) }}',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                dt_table.ajax.reload();
            },
            error: function( xhr ) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __( "template.error" ) }}',
                    text: xhr.responseJSON.message || '{{ __( "template.something_went_wrong" ) }}',
                });
            }
        });
    });

    // Print QR functionality
    $( document ).on( 'click', '.dt-print-qr', function() {
        let id = $( this ).data( 'id' );
        window.open( '{{ route( 'admin.visit.printQR', '' ) }}/' + id, '_blank' );
    });
});

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
