<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ $header['title'] }}</h3>
        </div>
        @can( 'add court_bookings' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.court_booking.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<?php
$statusFilterJson = json_encode( $data['status_filter'] ?? [] );

$columns = [
    [ 'type' => 'default', 'id' => 'select_row',    'title' => '' ],
    [ 'type' => 'default', 'id' => 'dt_no',         'title' => 'No.' ],
    [
        'type'        => 'input',
        'placeholder' => __( 'Search booking no.' ),
        'id'          => 'booking_no',
        'title'       => __( 'Booking No.' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'Search court' ),
        'id'          => 'court',
        'title'       => __( 'Court' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'Search user' ),
        'id'          => 'user_name',
        'title'       => __( 'template.users' ),
    ],
    [ 'type' => 'default', 'id' => 'booking_date', 'title' => __( 'Booking Date' ) ],
    [ 'type' => 'default', 'id' => 'time_slot',    'title' => __( 'Time Slot' ) ],
    [ 'type' => 'default', 'id' => 'total_amount', 'title' => __( 'Amount' ) ],
    [
        'type'    => 'select',
        'options' => $data['status_labels'],
        'id'      => 'status',
        'title'   => __( 'Status' ),
    ],
    [
        'type'    => 'select',
        'options' => $data['payment_status'],
        'id'      => 'payment_status',
        'title'   => __( 'Payment' ),
    ],
    [ 'type' => 'default', 'id' => 'dt_action', 'title' => __( 'datatables.action' ) ],
];
?>

<x-data-tables id="court_booking_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />
<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper   = @json( $data['status_labels'] ),
    paymentMapper  = @json( $data['payment_status'] ),
    statusFilter   = {{ $statusFilterJson }},
    filterUserId   = new URLSearchParams(window.location.search).get('user_id') || '',
    dt_table,
    dt_table_name  = '#court_booking_table',
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
            url: '{{ route( 'admin.court_booking.allCourtBookings' ) }}',
            type: 'POST',
            data: function( d ) {
                d['_token']        = '{{ csrf_token() }}';
                d['status_filter'] = statusFilter;
                if ( filterUserId ) d['user_id'] = filterUserId;
            },
            dataSrc: 'court_bookings',
        },
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        order: [[ 5, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'group_no' },
            { data: 'court_bookings' },
            { data: 'user' },
            { data: 'court_bookings' },
            { data: 'court_bookings' },
            { data: 'total_amount' },
            { data: 'status' },
            { data: 'payment_status' },
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
                width: '1%',
                render: function( data, type, row, meta ) {
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "booking_no" ) }}' ),
                render: function( data, type, row ) {
                    return data ? `<code>${data}</code>` : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "court" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !data || !data.length ) return '-';
                    let first = data[0];
                    let court = first.court;
                    if ( !court ) return `<span class="text-muted">${data.length} court(s)</span>`;
                    let venue = court.venue_sport?.venue?.name ?? '';
                    let sport = court.venue_sport?.sport?.name ?? '';
                    let sub   = venue && sport ? `<small class="text-muted d-block">${venue} · ${sport}</small>` : '';
                    let extra = data.length > 1 ? ` <small class="badge bg-dim bg-primary">+${data.length - 1} more</small>` : '';
                    return court.name + extra + sub;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user_name" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    return data ? data.email : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "booking_date" ) }}' ),
                render: function( data, type, row ) {
                    if ( !data || !data.length ) return '-';
                    let first = data[0];
                    if ( data.length === 1 ) return first.booking_date ?? '-';
                    let dates = [...new Set( data.map( i => i.booking_date ).filter( Boolean ) )];
                    return dates.length === 1 ? dates[0] : `<small>${dates.join('<br>')}</small>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "time_slot" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !data || !data.length ) return '-';
                    let first = data[0];
                    if ( !first.start_time || !first.end_time ) return '-';
                    let slot = `<span class="text-nowrap">${first.start_time.substring(0,5)} – ${first.end_time.substring(0,5)}</span>`;
                    return data.length > 1 ? slot + ` <small class="text-muted">(+${data.length - 1})</small>` : slot;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_amount" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    return data ? 'RM ' + parseFloat( data ).toFixed( 2 ) : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row ) {
                    let colors = { 1: 'warning', 10: 'primary', 11: 'success', 20: 'secondary', 21: 'danger' };
                    let color  = colors[ data ] ?? 'light';
                    let label  = statusMapper[ data ] ?? data;
                    return `<span class="badge bg-${color}">${label}</span>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "payment_status" ) }}' ),
                render: function( data, type, row ) {
                    let colors = { pending: 'warning', paid: 'success', failed: 'danger', refunded: 'info' };
                    let color  = colors[ data ] ?? 'light';
                    let label  = paymentMapper[ data ] ?? data;
                    return `<span class="badge bg-outline-${color}">${label}</span>`;
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row ) {
                    @canany( [ 'edit court_bookings', 'delete court_bookings' ] )

                    let edit = '', del = '';

                    @can( 'edit court_bookings' )
                    edit = `<li class="dt-edit" data-id="${row.encrypted_id}"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>`;
                    @endcan

                    @can( 'delete court_bookings' )
                    del = `<li class="dt-delete" data-id="${row.encrypted_id}"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>`;
                    @endcan

                    return `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">${edit}${del}</ul>
                            </div>
                        </div>`;
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

    // Show filter banner if coming from user page
    if ( filterUserId ) {
        $( '.nk-block-head' ).after(
            '<div class="alert alert-warning alert-dismissible d-flex align-items-center gap-2 mb-3" id="user_filter_banner">' +
            '<em class="icon ni ni-filter-fill"></em>' +
            '<span>Showing bookings filtered by selected user. <a href="{{ route( 'admin.module_parent.court_booking.index' ) }}" class="alert-link ms-1">Clear filter</a></span>' +
            '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
    }

    $( document ).on( 'click', '.dt-edit', function() {
        window.location.href = '{{ route( 'admin.court_booking.edit' ) }}?id=' + $( this ).data( 'id' );
    } );

    $( document ).on( 'click', '.dt-delete', function() {
        let id = $( this ).data( 'id' );
        if ( confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) {
            $.ajax( {
                url: '{{ route( 'admin.court_booking.deleteCourtBooking' ) }}',
                type: 'POST',
                data: { 'id': id, '_token': '{{ csrf_token() }}' },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
            } );
        }
    } );

} );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
