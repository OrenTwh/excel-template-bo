<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.venues' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add courts' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.venue.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'id'   => 'select_row',
        'title' => '',
    ],
    [
        'type'  => 'default',
        'id'    => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type'        => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id'          => 'created_date',
        'title'       => __( 'datatables.created_date' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'Name' ) ] ),
        'id'          => 'name',
        'title'       => __( 'Name' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'City' ) ] ),
        'id'          => 'city',
        'title'       => __( 'City' ),
    ],
    [
        'type'    => 'select',
        'options' => $data['status'],
        'id'      => 'status',
        'title'   => __( 'datatables.status' ),
    ],
    [
        'type'  => 'default',
        'id'    => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="venue_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#venue_table',
    dt_table_config = {
        language: {
            'lengthMenu':   '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords':  '{{ __( "datatables.zeroRecords" ) }}',
            'info':         '{{ __( "datatables.info" ) }}',
            'infoEmpty':    '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'previous': '{{ __( "datatables.previous" ) }}',
                'next':     '{{ __( "datatables.next" ) }}',
            }
        },
        ajax: {
            url: '{{ route( 'admin.venue.allVenues' ) }}',
            data: { '_token': '{{ csrf_token() }}' },
            dataSrc: 'venues',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'name' },
            { data: 'city' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                className: 'text-center',
                render: function( data, type, row ) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "city" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
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
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit courts', 'delete courts' ] )
                    let edit, status = '';

                    @can( 'edit courts' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete courts' )
                    status = row['status'] == 10 ?
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    let venueName   = encodeURIComponent( row['name'] );
                    let showSports  = `<li><a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}?venue=${venueName}"><em class="icon ni ni-grid-alt"></em><span>{{ __( 'Show Sports' ) }}</span></a></li>`;
                    let showCourts  = `<li><a href="{{ route( 'admin.module_parent.court.index' ) }}?venue=${venueName}"><em class="icon ni ni-building"></em><span>{{ __( 'Show Courts' ) }}</span></a></li>`;

                    let html = `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+showSports+`
                                    `+showCourts+`
                                    `+status+`
                                </ul>
                            </div>
                        </div>`;
                    return html;
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data( 'id' )] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.venue.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {
            $.ajax( {
                url: '{{ route( 'admin.venue.updateVenueStatus' ) }}',
                type: 'POST',
                data: {
                    'id':      $( this ).data( 'id' ),
                    'status':  $( this ).data( 'status' ),
                    '_token':  '{{ csrf_token() }}'
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
