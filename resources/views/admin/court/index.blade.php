<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'court.title_plural' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add courts' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.court.bulkAdd' ) }}" class="btn btn-outline-primary btn-sm">
                                <em class="icon ni ni-layers"></em> <span>Bulk Add</span>
                            </a>
                        </li>
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.court.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'default',
        'id' => 'image',
        'title' => __( 'Image' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'Name' ) ] ),
        'id' => 'name',
        'title' => __( 'Name' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'Venue' ) ] ),
        'id'          => 'venue',
        'title'       => __( 'Venue' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'Sport' ) ] ),
        'id'          => 'sport',
        'title'       => __( 'Sport' ),
    ],
    [
        'type' => 'default',
        'id' => 'capacity',
        'title' => __( 'Capacity' ),
    ],
    [
        'type' => 'default',
        'id' => 'price_per_hour',
        'title' => __( 'court.base_price' ),
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

<x-data-tables id="court_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#court_table',
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
            url: '{{ route( 'admin.court.allCourts' ) }}',
            data: function( d ) {
                d['_token'] = '{{ csrf_token() }}';
                d['name']   = window['name'];
                d['venue']  = window['venue'];
                d['sport']  = window['sport'];
                d['status'] = window['status'];
            },
            dataSrc: 'courts',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 1, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'image_path' },
            { data: 'name' },
            { data: 'venue_sport' },
            { data: 'venue_sport' },
            { data: 'capacity' },
            { data: 'price_per_hour' },
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
                        return '<img src="' + data + '" width="50px" height="50px" style="object-fit: contain;" />';
                    } else {
                        return '<img src="' + '{{ asset( 'admin/images/placeholder.png' ) }}' + '" width="50px" />'
                    }
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "venue" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data && data.venue ? data.venue.name : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "sport" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data && data.sport ? data.sport.name : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "capacity" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "price_per_hour" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !data ) return '-';
                    let price = 'RM ' + parseFloat( data ).toFixed( 2 );
                    let badge = row.pricings_count > 0
                        ? ` <small class="badge bg-dim bg-primary">${ row.pricings_count } tier${ row.pricings_count > 1 ? 's' : '' }</small>`
                        : '';
                    return price + badge;
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

                    @canany( [ 'edit courts', 'delete courts' ] )
                    let edit, status = '';

                    @can( 'edit courts' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'add courts' )
                    let duplicate = '<li class="dt-duplicate" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-copy"></em><span>{{ __( 'template.duplicate' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete courts' )
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
                                    `+(typeof duplicate !== 'undefined' ? duplicate : '')+`
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

        const venueParam = new URLSearchParams( window.location.search ).get( 'venue' );
        if ( venueParam ) {
            window['venue'] = venueParam;
            $( '#venue' ).val( venueParam );
        }

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.court.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-duplicate', function() {
            if ( !confirm( '{{ __( 'template.confirm_duplicate' ) }}' ) ) return;
            let id = $( this ).data( 'id' );
            $.ajax( {
                url: '{{ route( 'admin.court.duplicateCourt' ) }}',
                type: 'POST',
                data: { 'id': id, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( xhr ) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                    alert( msg );
                },
            } );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.court.updateCourtStatus' ) }}',
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
