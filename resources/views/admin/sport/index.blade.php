<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'Sports' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add sports' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.sport.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
    ['type' => 'default', 'id' => 'dt_drag',      'title' => ''],
    ['type' => 'default', 'id' => 'select_row',   'title' => ''],
    ['type' => 'default', 'id' => 'dt_no',        'title' => 'No.'],
    ['type' => 'default', 'id' => 'icon',         'title' => __( 'Icon' )],
    ['type' => 'input',   'id' => 'name',         'title' => __( 'Name' ),        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'Name' ) ] )],
    ['type' => 'default', 'id' => 'min_players',  'title' => __( 'Min Players' )],
    ['type' => 'default', 'id' => 'max_players',  'title' => __( 'Max Players' )],
    ['type' => 'default', 'id' => 'sequence',     'title' => __( 'Sequence' )],
    ['type' => 'select',  'id' => 'status',       'title' => __( 'datatables.status' ), 'options' => $data['status']],
    ['type' => 'default', 'id' => 'dt_action',    'title' => __( 'datatables.action' )],
];
?>

<x-data-tables id="sport_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

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
    dt_table_name = '#sport_table',
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
            url: '{{ route( 'admin.sport.allSports' ) }}',
            data: { '_token': '{{ csrf_token() }}' },
            dataSrc: 'sports',
        },
        lengthMenu: [[10, 25], [10, 25]],
        order: [],
        columns: [
            { data: null },
            { data: null },
            { data: null },
            { data: 'icon_path' },
            { data: 'name' },
            { data: 'min_players' },
            { data: 'max_players' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "icon" ) }}' ),
                orderable: false,
                render: function( data ) {
                    return data
                        ? `<img src="${ data }" width="50" height="50" style="object-fit:contain;">`
                        : `<img src="{{ asset( 'admin/images/placeholder.png' ) }}" width="50">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function( data ) { return data ?? '-'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "min_players" ) }}' ),
                orderable: false,
                render: function( data ) { return data ?? '-'; },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "max_players" ) }}' ),
                orderable: false,
                render: function( data ) { return data ?? '-'; },
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
                    @canany( [ 'edit sports', 'delete sports' ] )
                    let edit = '', status = '';
                    @can( 'edit sports' )
                    edit = `<li class="dt-edit" data-id="${ row.encrypted_id }"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>`;
                    @endcan
                    @can( 'delete sports' )
                    status = row.status == 10
                        ? `<li class="dt-status" data-id="${ row.encrypted_id }" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>`
                        : `<li class="dt-status" data-id="${ row.encrypted_id }" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>`;
                    @endcan
                    return `<div class="dropdown"><a class="dropdown-toggle btn btn-icon btn-trigger" href="#" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a><div class="dropdown-menu"><ul class="link-list-opt">${ edit }${ status }</ul></div></div>`;
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

    // ── Row actions ───────────────────────────────────────────────────────────
    $( document ).on( 'click', '.dt-edit', function() {
        window.location.href = '{{ route( 'admin.sport.edit' ) }}?id=' + $( this ).data( 'id' );
    } );

    $( document ).on( 'click', '.dt-status', function() {
        $.ajax( {
            url:  '{{ route( 'admin.sport.updateSportStatus' ) }}',
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', 'id': $( this ).data( 'id' ), 'status': $( this ).data( 'status' ) },
            success: function( r ) {
                dt_table.draw( false );
                $( '#modal_success .caption-text' ).html( r.message );
                modalSuccess.toggle();
            },
        } );
    } );

    // ── SortableJS drag-and-drop ──────────────────────────────────────────────
    // Re-init sortable whenever DataTable redraws
    $( '#sport_table' ).on( 'draw.dt', function() {
        let tbody = document.querySelector( '#sport_table tbody' );
        if ( !tbody ) return;

        Sortable.create( tbody, {
            handle:    '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                // Collect encrypted IDs in new DOM order
                let ids = [];
                $( '#sport_table tbody tr' ).each( function() {
                    let id = $( this ).find( '.select-row' ).data( 'id' );
                    if ( id ) ids.push( id );
                } );

                if ( !ids.length ) return;

                $.ajax( {
                    url:  '{{ route( 'admin.sport.reorder' ) }}',
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
