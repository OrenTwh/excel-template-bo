<?php $currentSection = $data['section'] ?? 'events'; ?>
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">
                {{ $currentSection === 'user_activities' ? __( 'User Activities' ) : __( 'template.court_calendars' ) }}
            </h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add court calendars' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.court_calendar.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'select',
        'options' => $data['courts']->pluck('name', 'id')->toArray(),
        'id' => 'court_id',
        'title' => __( 'Court' ),
    ],
    [
        'type' => 'select',
        'options' => $data['days_of_week'],
        'id' => 'day_of_week',
        'title' => __( 'Day of Week' ),
    ],
    [
        'type' => 'default',
        'id' => 'time_slot',
        'title' => __( 'Time Slot' ),
    ],
    [
        'type' => 'default',
        'id' => 'event_info',
        'title' => $currentSection === 'user_activities' ? __( 'Activity' ) : __( 'Event' ),
    ],
    [
        'type' => 'default',
        'id' => 'organiser',
        'title' => __( 'Organiser' ),
    ],
    [
        'type'    => 'select',
        'options' => $data['availability_status'],
        'id'      => 'status',
        'title'   => __( 'Availability' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="court_calendar_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<!-- Participants Modal -->
<div class="modal fade" id="modal_participants" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_participants_title">Participants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal_participants_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.close' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var daysOfWeekMapper = @json( $data['days_of_week'] ),
    availabilityMapper = @json( $data['availability_status'] ),
    currentSection = '{{ $currentSection }}',
    dt_table,
    dt_table_name = '#court_calendar_table',
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
            url: '{{ route( 'admin.court_calendar.allCourtCalendars' ) }}',
            data: function( d ) {
                d['_token']      = '{{ csrf_token() }}';
                d['court_id']    = window['court_id'];
                d['day_of_week'] = window['day_of_week'];
                d['status']      = window['status'];
                d['type']        = currentSection === 'user_activities' ? 'activities' : 'events';
            },
            dataSrc: 'calendars',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'asc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'court' },
            { data: 'day_of_week' },
            { data: null },
            { data: null },
            { data: null },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "court_id" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return data ? data.name : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "day_of_week" ) }}' ),
                render: function( data, type, row, meta ) {
                    return daysOfWeekMapper[data];
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "time_slot" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return row.start_time && row.end_time ? row.start_time + ' - ' + row.end_time : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "event_info" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !row.is_event ) return '<span class="badge bg-outline-secondary text-secondary">Schedule</span>';
                    let count   = row.participants_count || 0;
                    let maxStr  = row.max_participants ? '/' + row.max_participants : '';
                    let label   = row.created_by ? 'Activity' : 'Event';
                    let badgeClass = row.created_by ? 'bg-info' : 'bg-primary';
                    let title   = row.event_title ? `<br><small class="text-muted">${row.event_title}</small>` : '';
                    return `<span class="badge ${badgeClass}">${label}</span>${title}<br><small class="text-muted">${count}${maxStr} joined</small>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "organiser" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( !row.created_by ) return '<span class="text-muted small">Admin</span>';
                    return `<span>${row.created_by.fullname}</span><br><small class="text-muted">${row.created_by.email}</small>`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return availabilityMapper[data];
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit court calendars', 'delete court calendars' ] )
                    let edit = '', del = '', viewParticipants = '';

                    if ( row.is_event ) {
                        viewParticipants = `<li class="dt-view-participants" data-id="${row['encrypted_id']}"><a href="#"><em class="icon ni ni-users"></em><span>View Participants</span></a></li>`;
                    }

                    @can( 'edit court calendars' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete court calendars' )
                    del = '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    ${viewParticipants}
                                    ${edit}
                                    ${del}
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
            window.location.href = '{{ route( 'admin.court_calendar.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-view-participants', function() {
            let id = $( this ).data( 'id' );
            $( '#modal_participants_title' ).text( 'Loading...' );
            $( '#modal_participants_body' ).html( '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>' );
            $( '#modal_participants' ).modal( 'show' );

            $.ajax( {
                url:  '{{ route( 'admin.court_calendar.getEventParticipants' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}', 'id': id },
                success: function( response ) {
                    let ev = response.event;
                    $( '#modal_participants_title' ).text( ev.event_title + ' — ' + ( ev.date || '' ) + ' ' + ev.start_time + '–' + ev.end_time );

                    let rows = '';
                    if ( response.participants.length === 0 ) {
                        rows = '<tr><td colspan="5" class="text-center text-muted">No participants yet.</td></tr>';
                    } else {
                        response.participants.forEach( function( p, i ) {
                            rows += `<tr>
                                <td>${i + 1}</td>
                                <td>${p.name || '-'}</td>
                                <td>${p.email || '-'}</td>
                                <td>${p.phone || '-'}</td>
                                <td><span class="badge ${p.status === 'Confirmed' ? 'bg-success' : 'bg-danger'}">${p.status}</span></td>
                            </tr>`;
                        } );
                    }

                    $( '#modal_participants_body' ).html(`
                        <p class="text-muted small mb-2">Total: <strong>${response.total}</strong>${ev.max_participants ? ' / ' + ev.max_participants : ''}</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr>
                                    <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th>
                                </tr></thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    `);
                },
                error: function() {
                    $( '#modal_participants_body' ).html( '<p class="text-danger">Failed to load participants.</p>' );
                },
            } );
        } );

        $( document ).on( 'click', '.dt-delete', function() {

            let id = $( this ).data( 'id' );

            if ( confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) {
                $.ajax( {
                    url: '{{ route( 'admin.court_calendar.deleteCourtCalendar' ) }}',
                    type: 'POST',
                    data: {
                        'id': id,
                        '_token': '{{ csrf_token() }}'
                    },
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
