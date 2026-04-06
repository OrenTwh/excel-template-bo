<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.projects' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add projects' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.project.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'type' => 'default',
        'id' => 'logo',
        'title' => __( 'project.logo' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'project.title' ) ] ),
        'id' => 'title',
        'title' => __( 'project.title' ),
    ],
    [
        'type' => 'default',
        'id' => 'short_description',
        'title' => __( 'project.short_description' ),
    ],
    [
        'type' => 'select',
        'options' => $data['property_type'],
        'id' => 'property_type',
        'title' => __( 'project.property_type' ),
    ],
    [
        'type' => 'default',
        'id' => 'total_blocks',
        'title' => __( 'project.total_blocks' ),
    ],
    [
        'type' => 'default',
        'id' => 'total_floors',
        'title' => __( 'project.total_floors' ),
    ],
    [
        'type' => 'default',
        'id' => 'unit_left_total_units',
        'title' => __( 'project.unit_left_total_units' ),
    ],
    [
        'type' => 'select',
        'options' => $data['project_status_type'],
        'id' => 'project_status',
        'title' => __( 'project.project_status' ),
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

<x-data-tables id="project_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        '10': {
            'text': '{{ __( 'datatables.activated' ) }}',
            'color': 'badge rounded-pill bg-success',
        },
        '20': {
            'text': '{{ __( 'datatables.suspended' ) }}',
            'color': 'badge rounded-pill bg-danger',
        },
    },
    projectStatusMapper = {
        '1': {
            'text': '{{ __( 'property.completed' ) }}',
            'color': 'badge rounded-pill bg-success',
        },
        '2': {
            'text': '{{ __( 'property.under_construction' ) }}',
            'color': 'badge rounded-pill bg-warning',
        },
    },
    dt_table,
    dt_table_name = '#project_table',
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
            url: '{{ route( 'admin.project.allProjects' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'projects',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'logo_path' },
            { data: 'title' },
            { data: 'short_description' },
            { data: 'property_type' },
            { data: 'blocks' },
            { data: 'total_floor' },
            { data: 'total_active_units' },
            { data: 'project_status' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "logo" ) }}' ),
                orderable: false,
                width: '10%',
                render: function( data, type, row, meta ) {
                    if ( data ) {

                        return '<img src="' + ( data ? data : '{{ asset( 'admin/images/placeholder.png' ) }}' ) + '" width="75px" />';

                    } else {

                        return '<img src="' + '{{ asset( 'admin/images/placeholder.png' ) }}' + '" width="75px" />'
                        
                    }
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_blocks" ) }}' ),
                width: '1%',
                render: function( data, type, row, meta ) {
                    return data ? data.length : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_floors" ) }}' ),
                width: '1%',
                render: function( data, type, row, meta ) {
                    
                    if (row.blocks && row.blocks.length > 0) {
                        let total = row.blocks.reduce((sum, block) => {
                            return sum + (parseInt(block.total_floors) || 0);
                        }, 0);
                        return total > 0 ? total : '-';
                    }
                    return '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "unit_left_total_units" ) }}' ),
                width: '18%',
                render: function( data, type, row, meta ) {
                    return data ? data + ' / ' + row.total_active_units  : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "short_description" ) }}' ),
                render: function( data, type, row, meta ) {
                    return row.decoded_translations ? row.decoded_translations['en'].short_description.substring(0, 50) + '...' : ( data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-' ) ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    let projectName = row.decoded_translations && row.decoded_translations['en']
                        ? row.decoded_translations['en'].title
                        : data;

                    if (projectName) {
                        return '<a href="#" class="project-name-link text-primary" ' +
                            'style="cursor: pointer;" ' +
                            'data-project-name="' + projectName + '" ' +
                            'data-project-id="' + row.id + '">' +
                            projectName +
                            '</a>' + 
                            (row.project_type_label ? '<br><small class="text-muted">' + row.project_type_label + '</small>' : '');
                    }
                    return '-';

                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "property_type" ) }}' ),
                visible: false
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "project_status" ) }}' ),
                render: function( data, type, row, meta ) {
                    if (data && projectStatusMapper[data]) {
                        return '<span class="' + projectStatusMapper[data].color + '">' + projectStatusMapper[data].text + '</span>';
                    }
                    return '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return '<span class="' + statusMapper[data].color + '">' + statusMapper[data].text + '</span>';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit projects', 'delete projects' ] )
                    let edit, status = '';

                    @can( 'edit projects' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete projects' )
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.project.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.project.updateProjectStatus' ) }}',
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

        $( document ).on( 'click', '.project-name-link', function(e) {
            e.preventDefault();
            const projectName = $( this ).data( 'project-name' );
            const projectId = $( this ).data( 'project-id' );
            
            // Redirect to property unit page with property name filter
            window.location.href = '{{ route( 'admin.module_parent.property.index' ) }}?project_filter=' + encodeURIComponent(projectId);
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>