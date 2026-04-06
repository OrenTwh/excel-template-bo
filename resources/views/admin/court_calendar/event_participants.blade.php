<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.event_joining_histories' ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> {{ __( 'template.court_calendars' ) }}
            </a>
        </div>
    </div>
</div>

<?php
$columns = [
    [ 'type' => 'default', 'id' => 'select_row',  'title' => '' ],
    [ 'type' => 'default', 'id' => 'dt_no',        'title' => 'No.' ],
    [
        'type'        => 'input',
        'placeholder' => __( 'court_calendar.search_event' ),
        'id'          => 'event_title',
        'title'       => __( 'court_calendar.event' ),
    ],
    [
        'type'        => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'court_calendar.date_and_time' ) ] ),
        'id'          => 'event_date',
        'title'       => __( 'court_calendar.date_and_time' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'court_calendar.user' ) ] ),
        'id'          => 'user',
        'title'       => __( 'court_calendar.user' ),
    ],
    [ 'type' => 'default', 'id' => 'joined_at',    'title' => __( 'court_calendar.joined_at' ) ],
    [
        'type'    => 'select',
        'options' => $data['status'],
        'id'      => 'status',
        'title'   => __( 'datatables.status' ),
    ],
];
?>

<x-data-tables id="event_participants_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name   = '#event_participants_table',
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
            url: '{{ route( 'admin.court_calendar.allEventParticipants' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'participants',
        },
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        order: [[ 5, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'court_calendar' },
            { data: 'court_calendar' },
            { data: 'user' },
            { data: 'joined_at' },
            { data: 'status' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "event_title" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !data ) return '-';
                    return `<strong>${data.event_title || '-'}</strong>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "event_date" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !data ) return '-';
                    let date = data.date ? data.date.substring( 0, 10 ) : '-';
                    let time = ( data.start_time || '-' ) + ' – ' + ( data.end_time || '-' );
                    return `<span>${date}</span><br><small class="text-muted">${time}</small>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    if ( !data ) return '-';
                    let phone = data.calling_code ? ( data.calling_code + ' ' + data.phone_number ) : ( data.phone_number || '' );
                    return `<strong>${data.name || '-'}</strong><br><small class="text-muted">${data.email || ''}</small>${phone ? '<br><small class="text-muted">' + phone + '</small>' : ''}`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "joined_at" ) }}' ),
                render: function( data, type, row ) {
                    return data ? data.substring( 0, 16 ).replace( 'T', ' ' ) : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row ) {
                    if ( data == 10 ) return '<span class="badge bg-success">{{ __( "court_calendar.confirmed" ) }}</span>';
                    if ( data == 20 ) {
                        let reason = row.cancellation_reason ? `<br><small class="text-muted">${row.cancellation_reason}</small>` : '';
                        return `<span class="badge bg-danger">{{ __( "court_calendar.cancelled" ) }}</span>${reason}`;
                    }
                    return data;
                },
            },
        ],
    },
    table_no = 0,
    timeout  = null;

document.addEventListener( 'DOMContentLoaded', function() {

    $( '#event_date' ).flatpickr( {
        mode: 'range',
        disableMobile: true,
        onClose: function( selected, dateStr, instance ) {
            window[$( instance.element ).data( 'id' )] = $( instance.element ).val();
            dt_table.draw();
        }
    } );

} );

</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
