<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.users' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add users' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.user.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.fullname' ) ] ),
        'id' => 'fullname',
        'title' => __( 'user.fullname' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.email' ) ] ),
        'id' => 'email',
        'title' => __( 'user.email' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.phone_number' ) ] ),
        'id' => 'phone_number',
        'title' => __( 'user.phone_number' ),
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'user.date_of_birth' ) ] ),
        'id' => 'date_of_birth',
        'title' => __( 'user.date_of_birth' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'user.nationality' ) ] ),
        'id' => 'nationality',
        'title' => __( 'user.nationality' ),
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

<x-data-tables id="user_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<!-- Test Notification Modal -->
<div class="modal fade" id="modal_test_notification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="test_notif_user_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">English</label>
                        <input type="text" class="form-control mb-2" id="test_notif_title_en" placeholder="Title (EN)" value="Xpark">
                        <textarea class="form-control" id="test_notif_content_en" rows="3" placeholder="Content (EN)">Test notification.</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Malay (BM)</label>
                        <input type="text" class="form-control mb-2" id="test_notif_title_ms" placeholder="Title (MS)" value="Xpark">
                        <textarea class="form-control" id="test_notif_content_ms" rows="3" placeholder="Content (MS)">Notifikasi ujian.</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Chinese (中文)</label>
                        <input type="text" class="form-control mb-2" id="test_notif_title_zh" placeholder="Title (ZH)" value="Xpark">
                        <textarea class="form-control" id="test_notif_content_zh" rows="3" placeholder="Content (ZH)">测试通知。</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="test_notif_submit">Send Notification</button>
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

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#user_table',
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
            url: '{{ route( 'admin.user.allUsers' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'users',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'fullname' },
            { data: 'email' },
            { data: 'phone_number' },
            { data: 'date_of_birth' },
            { data: 'nationality_info' },
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
                
                render: function (data, type, row, meta) {
                    // Calculate the row number dynamically based on the page info
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "date_of_birth" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "nationality" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data.name : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "email" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "fullname" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "last_name" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "feedback_email" ) }}' ),
                
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "phone_number" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? ( row.calling_code ? row.calling_code + " " : "+60 " ) + data : '-' ;
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

                    @canany( [ 'edit users', 'delete users' ] )
                    let edit, status, appointments, transactions, shortRentHistory, tenancyAgreement = '';

                    @can( 'edit users' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete users' )
                    status = row['status'] == 10 ?
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    let testNotification = '<li class="dt-test-notification" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-bell"></em><span>Test Notification</span></a></li>';

                    let downline = '<li class="dt-downline" data-id="' + row['encrypted_id'] + '" data-name="' + ( row['fullname'] || '-' ) + '"><a href="#"><em class="icon ni ni-users"></em><span>{{ __( 'user.show_downline' ) }}</span></a></li>';

                    let bookings = '<li class="dt-bookings"><a href="{{ route( 'admin.module_parent.court_booking.index' ) }}?user_id=' + row['encrypted_id'] + '"><em class="icon ni ni-calendar-booking"></em><span>Show Bookings</span></a></li>';

                    @if( 1 == 2 )
                    // Additional action buttons
                    appointments = '<li class="dt-appointments" data-id="' + row['encrypted_id'] + '"><a href="{{ route( "admin.user.appointments", ":id" ) }}".replace(":id", row["encrypted_id"])><em class="icon ni ni-calendar"></em><span>Appointments</span></a></li>';
                    
                    transactions = '<li class="dt-transactions" data-id="' + row['encrypted_id'] + '"><a href="{{ route( "admin.user.transactions", ":id" ) }}".replace(":id", row["encrypted_id"])><em class="icon ni ni-wallet"></em><span>Transactions</span></a></li>';
                    
                    shortRentHistory = '<li class="dt-short-rent-history" data-id="' + row['encrypted_id'] + '"><a href="{{ route( "admin.user.shortRentHistory", ":id" ) }}".replace(":id", row["encrypted_id"])><em class="icon ni ni-building"></em><span>Short Rent History</span></a></li>';
                    
                    // Show tenancy agreement only if user has any
                    tenancyAgreement = (row['has_tenancy_agreements'] && row['has_tenancy_agreements'] > 0) ? 
                        '<li class="dt-tenancy-agreement" data-id="' + row['encrypted_id'] + '"><a href="{{ route( "admin.user.tenancyAgreements", ":id" ) }}".replace(":id", row["encrypted_id"])><em class="icon ni ni-file-text"></em><span>Tenancy Agreement</span></a></li>' : 
                        '';
                    @endif

                    // `+appointments+`
                    // `+transactions+`
                    // `+shortRentHistory+`
                    // `+tenancyAgreement+`

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                    `+testNotification+`
                                    `+downline+`
                                    `+bookings+`
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( '#date_of_birth' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.user.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        let modalTestNotification = new bootstrap.Modal( document.getElementById( 'modal_test_notification' ) );

        $( document ).on( 'click', '.dt-test-notification', function() {
            $( '#test_notif_user_id' ).val( $( this ).data( 'id' ) );
            modalTestNotification.show();
        } );

        $( '#test_notif_submit' ).on( 'click', function() {

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'id',         $( '#test_notif_user_id' ).val() );
            formData.append( 'title_en',   $( '#test_notif_title_en' ).val() );
            formData.append( 'content_en', $( '#test_notif_content_en' ).val() );
            formData.append( 'title_ms',   $( '#test_notif_title_ms' ).val() );
            formData.append( 'content_ms', $( '#test_notif_content_ms' ).val() );
            formData.append( 'title_zh',   $( '#test_notif_title_zh' ).val() );
            formData.append( 'content_zh', $( '#test_notif_content_zh' ).val() );
            formData.append( '_token',     '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.user.sendTestNotification' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    modalTestNotification.hide();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    modalTestNotification.hide();
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            } );
        } );

        $( document ).on( 'click', '.dt-downline', function() {

            let id   = $( this ).data( 'id' );
            let name = $( this ).data( 'name' );

            $( '#modal_downline_title' ).text( name );
            $( '#modal_downline_body' ).html( '<div class="text-center py-3">{{ __( 'template.loading' ) }}</div>' );
            $( '#modal_downline' ).modal( 'show' );

            $.ajax( {
                url: '{{ route( 'admin.user.userDownlines' ) }}',
                type: 'POST',
                data: {
                    'id': id,
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    let downlines = response.downlines;

                    if ( downlines.length === 0 ) {
                        $( '#modal_downline_body' ).html( '<p class="text-center text-muted py-3">{{ __( 'datatables.zeroRecords' ) }}</p>' );
                        return;
                    }

                    let statusMap = @json( $data['status'] );

                    let rows = '';
                    $.each( downlines, function( i, d ) {
                        rows += `<tr>
                            <td>${ i + 1 }</td>
                            <td>${ d.fullname || '-' }</td>
                            <td>${ d.email || '-' }</td>
                            <td>${ ( d.calling_code || '+60' ) + ' ' + ( d.phone_number || '-' ) }</td>
                            <td>${ statusMap[ d.status ] || '-' }</td>
                            <td>${ d.created_at || '-' }</td>
                        </tr>`;
                    } );

                    $( '#modal_downline_body' ).html(
                        `<div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __( 'datatables.no' ) }}</th>
                                        <th>{{ __( 'user.fullname' ) }}</th>
                                        <th>{{ __( 'user.email' ) }}</th>
                                        <th>{{ __( 'user.phone_number' ) }}</th>
                                        <th>{{ __( 'datatables.status' ) }}</th>
                                        <th>{{ __( 'datatables.created_date' ) }}</th>
                                    </tr>
                                </thead>
                                <tbody>${ rows }</tbody>
                            </table>
                        </div>`
                    );
                },
                error: function() {
                    $( '#modal_downline_body' ).html( '<p class="text-center text-danger py-3">Something went wrong.</p>' );
                }
            } );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.user.updateUserStatus' ) }}',
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

<div class="modal fade" id="modal_downline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'user.downline_of' ) }} <span id="modal_downline_title"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_downline_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.close' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>