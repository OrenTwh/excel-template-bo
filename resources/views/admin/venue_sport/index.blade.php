<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.venue_sports' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add courts' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal_import_venue_sport">
                                <em class="icon ni ni-upload"></em> <span>Import</span>
                            </button>
                        </li>
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.venue_sport.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type'  => 'default',
        'id'    => 'slot_duration',
        'title' => __( 'Slot Duration (min)' ),
    ],
    [
        'type'  => 'default',
        'id'    => 'price_per_slot',
        'title' => __( 'Price / Slot' ),
    ],
    [
        'type'  => 'default',
        'id'    => 'open_time',
        'title' => __( 'Open' ),
    ],
    [
        'type'  => 'default',
        'id'    => 'close_time',
        'title' => __( 'Close' ),
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

<x-data-tables id="venue_sport_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#venue_sport_table',
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
            url: '{{ route( 'admin.venue_sport.allVenueSportsGlobal' ) }}',
            data: function( d ) {
                d['_token']       = '{{ csrf_token() }}';
                d['created_date'] = window['created_date'];
                d['venue']        = window['venue'];
                d['sport']        = window['sport'];
                d['status']       = window['status'];
            },
            dataSrc: 'venue_sports',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'venue' },
            { data: 'sport' },
            { data: 'slot_duration' },
            { data: 'price_per_slot' },
            { data: 'open_time' },
            { data: 'close_time' },
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
                render: function( data, type, row ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "venue" ) }}' ),
                render: function( data, type, row ) {
                    return row.venue ? row.venue.name : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "sport" ) }}' ),
                render: function( data, type, row ) {
                    return row.sport ? row.sport.name : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "slot_duration" ) }}' ),
                render: function( data, type, row ) {
                    return data ? data + ' min' : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "price_per_slot" ) }}' ),
                render: function( data, type, row ) {
                    return data ? 'RM ' + parseFloat( data ).toFixed( 2 ) : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "open_time" ) }}' ),
                render: function( data, type, row ) {
                    return data ? data.substring( 0, 5 ) : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "close_time" ) }}' ),
                render: function( data, type, row ) {
                    return data ? data.substring( 0, 5 ) : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row ) {
                    return statusMapper[data];
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                className: 'text-center',
                render: function( data, type, row ) {

                    @canany( [ 'edit courts', 'delete courts' ] )
                    let edit = '', status = '';

                    @can( 'edit courts' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete courts' )
                    status = row['status'] == 10 ?
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' :
                        '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan

                    let html = `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
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

        const venueParam = new URLSearchParams( window.location.search ).get( 'venue' );
        if ( venueParam ) {
            window['venue'] = venueParam;
            $( '#venue' ).val( venueParam );
        }

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.venue_sport.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {
            $.ajax( {
                url: '{{ route( 'admin.venue.updateVenueSportStatus' ) }}',
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
         // ── Import ────────────────────────────────────────────────────────────────
    $( '#btn_import_venue_sport' ).on( 'click', function() {
        let file = $( '#import_vs_file' )[0].files[0];
        $( '#import_vs_error' ).text( '' );

        if ( !file ) {
            $( '#import_vs_error' ).text( 'Please select an Excel file.' );
            return;
        }

        let formData = new FormData();
        formData.append( 'file',   file );
        formData.append( '_token', '{{ csrf_token() }}' );

        $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );
        bootstrap.Modal.getInstance( document.getElementById( 'modal_import_venue_sport' ) ).hide();

        $.ajax( {
            url: '{{ route( 'admin.venue_sport.import' ) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function( response ) {
                $( 'body' ).loading( 'stop' );
                $( '#import_vs_file' ).val( '' );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();
                dt_table.draw( false );
            },
            error: function( error ) {
                $( 'body' ).loading( 'stop' );
                if ( error.status === 422 ) {
                    let errors = error.responseJSON.errors;
                    $( '#import_vs_error' ).text( errors.file ? errors.file[0] : ( error.responseJSON.message || 'Import failed.' ) );
                    bootstrap.Modal.getOrCreateInstance( document.getElementById( 'modal_import_venue_sport' ) ).show();
                } else {
                    $( '#modal_danger .caption-text' ).html( error.responseJSON?.message || 'Something went wrong.' );
                    modalDanger.toggle();
                }
            }
        } );
    } );

} );
</script>

{{-- ── Import Modal ─────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal_import_venue_sport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Venue Sports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Upload an <strong>.xlsx</strong> file. Expected format:
                </p>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm text-center small">
                        <thead class="table-light">
                            <tr>
                                <th>Activities</th>
                                <th>Venue A</th>
                                <th>Venue B</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-start">Golf</td><td>✅</td><td></td></tr>
                            <tr><td class="text-start">Badminton</td><td>✅</td><td>✅</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-3">
                    A tick creates a VenueSport record (schedule &amp; pricing can be edited after).
                    Sports not yet in the system will be created automatically.
                    Venue names must match exactly.
                </p>
                <div class="mb-3">
                    <label class="form-label">Excel File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="import_vs_file" accept=".xlsx,.xls">
                </div>
                <div class="text-danger small" id="import_vs_error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_import_venue_sport">
                    <em class="icon ni ni-upload"></em> Import
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
