<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'Popular Arenas' ) }}</h3>
        </div>
        @can( 'add courts' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_popular_arena">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<?php
$columns = [
    [ 'type' => 'default', 'id' => 'dt_drag',    'title' => '' ],
    [ 'type' => 'default', 'id' => 'select_row', 'title' => '' ],
    [ 'type' => 'default', 'id' => 'dt_no',      'title' => 'No.' ],
    [ 'type' => 'default', 'id' => 'venue',      'title' => __( 'Venue' ) ],
    [ 'type' => 'default', 'id' => 'sequence',   'title' => __( 'Sequence' ) ],
    [ 'type' => 'select',  'id' => 'status',     'title' => __( 'datatables.status' ), 'options' => $data['status'] ],
    [ 'type' => 'default', 'id' => 'dt_action',  'title' => __( 'datatables.action' ) ],
];
?>

<x-data-tables id="popular_arena_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<!-- Add Modal -->
<div class="modal fade" id="modal_add_popular_arena" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'Add Popular Arena' ) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __( 'Venue' ) }} <span class="text-danger">*</span></label>
                    <select id="pa_venue_id" class="form-select">
                        <option value="">-- {{ __( 'Select Venue' ) }} --</option>
                        @foreach ( $data['venues'] as $venue )
                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="pa_error" class="text-danger small d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="pa_submit">{{ __( 'template.save' ) }}</button>
            </div>
        </div>
    </div>
</div>

<style>
.drag-handle { cursor: grab; color: #8094ae; padding: 0 6px; }
.drag-handle:active { cursor: grabbing; }
.sortable-ghost { opacity: 0.4; background: #e5e9f0; }
</style>

<script>
window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name  = '#popular_arena_table',
    dt_table_config = {
        language: {
            'lengthMenu':   '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords':  '{{ __( "datatables.zeroRecords" ) }}',
            'info':         '{{ __( "datatables.info" ) }}',
            'infoEmpty':    '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': { 'previous': '{{ __( "datatables.previous" ) }}', 'next': '{{ __( "datatables.next" ) }}' }
        },
        ajax: {
            url: '{{ route( 'admin.popular_arena.allPopularArenas' ) }}',
            data: function( d ) {
                d['_token'] = '{{ csrf_token() }}';
                d['status'] = window['status'];
            },
            dataSrc: 'popular_arenas',
        },
        lengthMenu: [[10, 25], [10, 25]],
        order: [],
        columns: [
            { data: null },
            { data: null },
            { data: null },
            { data: 'venue' },
            { data: 'sequence' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_drag" ) }}' ),
                orderable: false, width: '1%', className: 'text-center',
                render: function() {
                    return '<span class="drag-handle"><em class="icon ni ni-move" style="font-size:1.2rem;"></em></span>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "select_row" ) }}' ),
                orderable: false, className: 'text-center',
                render: function( data, type, row ) {
                    return `<input type="checkbox" class="select-row" data-id="${ row.encrypted_id }">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false, width: '1%',
                render: function( data, type, row, meta ) {
                    return dt_table.page.info().start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "venue" ) }}' ),
                orderable: false,
                render: function( data ) { return data ? data.name : '-'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "sequence" ) }}' ),
                visible: false,
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data ) { return statusMapper[ data ] ?? data; },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false, width: '1%', className: 'text-center',
                render: function( data, type, row ) {
                    @canany( [ 'edit courts', 'delete courts' ] )
                    let statusToggle = '', del = '';
                    @can( 'edit courts' )
                    statusToggle = row.status == 10
                        ? `<li class="dt-status" data-id="${ row.encrypted_id }"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>`
                        : `<li class="dt-status" data-id="${ row.encrypted_id }"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>`;
                    @endcan
                    @can( 'delete courts' )
                    del = `<li class="dt-delete" data-id="${ row.encrypted_id }"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>`;
                    @endcan
                    return `<div class="dropdown"><a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a><div class="dropdown-menu"><ul class="link-list-opt">${ statusToggle }${ del }</ul></div></div>`;
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

    $( '#pa_submit' ).on( 'click', function() {
        $( '#pa_error' ).addClass( 'd-none' ).text( '' );
        $.ajax( {
            url:  '{{ route( 'admin.popular_arena.createPopularArena' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'venue_id': $( '#pa_venue_id' ).val() },
            success: function( response ) {
                $( '#modal_add_popular_arena' ).modal( 'hide' );
                $( '#pa_venue_id' ).val( '' );
                dt_table.draw( false );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();
            },
            error: function( xhr ) {
                let errors = xhr.responseJSON?.errors;
                $( '#pa_error' ).removeClass( 'd-none' ).html( errors ? Object.values( errors ).flat().join( '<br>' ) : xhr.responseJSON?.message );
            },
        } );
    } );

    $( document ).on( 'click', '.dt-status', function() {
        $.ajax( {
            url:  '{{ route( 'admin.popular_arena.updatePopularArenaStatus' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': $( this ).data( 'id' ) },
            success: function( response ) {
                dt_table.draw( false );
                $( '#modal_success .caption-text' ).html( response.message );
                modalSuccess.toggle();
            },
        } );
    } );

    $( document ).on( 'click', '.dt-delete', function() {
        if ( confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) {
            $.ajax( {
                url:  '{{ route( 'admin.popular_arena.deletePopularArena' ) }}',
                type: 'POST',
                data: { '_token': '{{ csrf_token() }}', 'id': $( this ).data( 'id' ) },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
            } );
        }
    } );

    $( '#popular_arena_table' ).on( 'draw.dt', function() {
        let tbody = document.querySelector( '#popular_arena_table tbody' );
        if ( !tbody ) return;
        Sortable.create( tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                let ids = [];
                $( '#popular_arena_table tbody tr' ).each( function() {
                    let id = $( this ).find( '.select-row' ).data( 'id' );
                    if ( id ) ids.push( id );
                } );
                if ( !ids.length ) return;
                $.ajax( {
                    url:  '{{ route( 'admin.popular_arena.reorder' ) }}',
                    type: 'POST',
                    data: { '_token': '{{ csrf_token() }}', 'ids': ids },
                    success: function( r ) {
                        dt_table.draw( false );
                        $( '#modal_success .caption-text' ).html( r.message );
                        modalSuccess.toggle();
                    },
                    error: function( err ) {
                        $( '#modal_danger .caption-text' ).html( err.responseJSON?.message ?? 'Reorder failed.' );
                        modalDanger.toggle();
                    }
                } );
            },
        } );
    } );

} );
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
