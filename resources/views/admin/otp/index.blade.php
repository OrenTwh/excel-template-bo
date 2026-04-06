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
        'type' => 'default',
        'id' => 'expire_on',
        'title' => __( 'user.expire_on' ),
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'user.phone_number' ) ] ),
        'id' => 'phone_number',
        'title' => __( 'user.phone_number' ),
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'user.otp_code' ) ] ),
        'id' => 'otp',
        'title' => __( 'user.otp_code' ),
    ],
    [
        'type' => 'select',
        'options' => $data['type'],
        'id' => 'type',
        'title' => __( 'user.type' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'user.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];

?>

<div class="card">
    <div class="card-body">
        <x-data-tables id="otp_action_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />
    </div>
</div>

<script>

    window['columns'] = @json( $columns );
    window['ids'] = [];
        
    @foreach ( $columns as $column )
    @if ( $column['type'] != 'default' )
    window['{{ $column['id'] }}'] = '';
    @endif
    @endforeach

    var statusMapper = @json( $data['status'] ),
        dt_table,
        dt_table_name = '#otp_action_table',
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
                url: '{{ route( 'admin.otp.allOtp' ) }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                dataSrc: 'otp_actions',
            },
            lengthMenu: [
                [ 10, 25, 50, 999999 ],
                [ 10, 25, 50, '{{ __( 'datatables.all' ) }}' ]
            ],
            order: [[ 1, 'desc' ]],
            columns: [
                { data: null },
                { data: null },
                { data: 'created_at' },
                { data: 'expire_on' },
                { data: 'phone_number' },
                { data: 'otp_code' },
                { data: 'user' },
                { data: 'status' },
                { data: 'id' },
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
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                    
                    render: function( data, type, row, meta ) {
                        return data ? data : '-' ;
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "expire_on" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return data ?? '-';
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "phone_number" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        if (!data) return '-';
                        data = data.toString();

                        if (data.startsWith('60')) {
                            return '+' + data;
                        } else {
                            return '+60' + data;
                        }
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "otp" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return data ?? '-';
                    },
                },
                {
                    targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                    orderable: false,
                    render: function( data, type, row, meta ) {
                        return data ? 'user action' : 'register otp';
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
                    width: '10%',
                    className: 'text-center',
                    render: function( data, type, row, meta ) {
                        console.log(data)
                        @canany( [ 'edit otps' ] )

                        let view = '',
                            edit = '',
                            status = '';

                        @can( 'edit otps' )
                        status = '<li class="dropdown-item click-action dt-send-otp" data-id="' + data + '" data-type="' + ( row.user ? 1 : 2 ) + '">{{ __( 'user.resend_otp' ) }}</li>' ;
                        @endcan

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
                        return '<i class="text-secondary" data-lucide="more-horizontal" data-bs-toggle="dropdown"></i>';
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

        $( document ).on( 'click', '.dt-send-otp', function() {

            uid = $( this ).data( 'id' );
            type = $( this ).data( 'type' );
            $.ajax( {
                url: '{{ route( 'admin.otp.resendOtp' ) }}',
                type: 'POST',
                data: {
                    id: uid,
                    type: type,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( '#modal_success .caption-text' ).html( response.message );
                    dt_table.draw( false );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                },
            } );      
        } );

        $( document ).on( 'click', '#modal_confirmation_submit', function() {

            switch ( scope ) {
                case 'send-otp':
                    $.ajax( {
                        url: '{{ route( 'admin.otp.resendOtp' ) }}',
                        type: 'POST',
                        data: {
                            id: uid,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function( response ) {
                            modalConfirmation.hide();
                            $( '#modal_success .caption-text' ).html( response.message );
                            modalSuccess.show();
                            dt_table.draw( false );
                        },
                        error: function( error ) {
                            modalConfirmation.hide();
                            $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                            modalDanger.show();
                        },
                    } );        
            }
        } );

    } );

</script>

<script src="{{ asset( 'admin/js/dataTable.initv2.js' ) . Helper::assetVersion() }}"></script>