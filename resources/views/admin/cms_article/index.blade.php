<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">CMS Articles</h3>
        </div>
        @can( 'add cms_article' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.cms_article.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
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
        'id' => 'thumbnail',
        'title' => 'Thumbnail',
    ],
    [
        'type' => 'input',
        'placeholder' => 'Search Title',
        'id' => 'title',
        'title' => 'Title',
    ],
    [
        'type' => 'date',
        'placeholder' => 'Search Publish Date',
        'id' => 'publish_date',
        'title' => 'Publish Date',
    ],
    [
        'type' => 'default',
        'id' => 'banners_count',
        'title' => 'Banners',
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

<x-data-tables id="cms_article_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );

@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = {
        11: {
            'text': 'Draft',
            'color': 'badge rounded-pill bg-warning',
        },
        10: {
            'text': 'Published',
            'color': 'badge rounded-pill bg-success',
        },
        1: {
            'text': 'Published',
            'color': 'badge rounded-pill bg-success',
        },
    },
    dt_table,
    dt_table_name = '#cms_article_table',
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
            url: '{{ route( 'admin.cms_article.allProjects' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'articles',
        },
        lengthMenu: [[10, 25, 50, 100],[10, 25, 50, 100]],
        order: [[ 4, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'thumbnail_path' },
            { data: 'title' },
            { data: 'publish_date' },
            { data: 'banners' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: 0,
                width: '1%',
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
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "thumbnail" ) }}' ),
                width: '10%',
                orderable: false,
                className: 'text-center',
                render: function( data, type, row, meta ) {
                    if (data) {
                        return '<img src="' + data + '" alt="Thumbnail" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">';
                    }
                    return '<div style="width: 80px; height: 80px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><em class="icon ni ni-img" style="font-size: 24px; color: #999;"></em></div>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "title" ) }}' ),
                width: '25%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "publish_date" ) }}' ),
                width: '15%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "banners_count" ) }}' ),
                width: '10%',
                className: 'text-center',
                orderable: false,
                render: function( data, type, row, meta ) {
                    const count = data ? data.length : 0;
                    return '<span class="badge rounded-pill bg-primary">' + count + ' Banners</span>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return '<span class="' + statusMapper[row.status].color + '">' + statusMapper[row.status].text + '</span>';
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit cms_article', 'delete cms_article' ] )
                    let edit, status, del = '';

                    @can( 'edit cms_article' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete cms_article' )
                    status = row['status'] == 10 ?
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="11"><a href="#"><em class="icon ni ni-file-text"></em><span>Set as Draft</span></a></li>' :
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>Publish</span></a></li>';

                    del = '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';
                    @endcan

                    let html =
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                    `+del+`
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

        $( '#publish_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.cms_article.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.cms_article.updateProjectStatus' ) }}',
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

        $( document ).on( 'click', '.dt-delete', function() {
            const articleId = $( this ).data( 'id' );

            if ( confirm( '{{ __( 'datatables.are_you_sure_delete' ) }}' ) ) {
                $.ajax( {
                    url: '{{ route( 'admin.cms_article.deleteProject' ) }}',
                    type: 'POST',
                    data: {
                        'id': articleId,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function( response ) {
                        dt_table.draw( false );
                        $( '#modal_success .caption-text' ).html( response.message );
                        modalSuccess.toggle();
                    },
                    error: function( xhr ) {
                        $( '#modal_danger .caption-text' ).html( xhr.responseJSON.message );
                        modalDanger.toggle();
                    }
                } );
            }
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
