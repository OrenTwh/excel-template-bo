<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.amenities' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add sports' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_tag">{{ __( 'template.add' ) }}</a>
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
        'type'  => 'default',
        'id'    => 'select_row',
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
        'type'  => 'default',
        'id'    => 'icon',
        'title' => __( 'Icon' ),
    ],
    [
        'type'        => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'Name' ) ] ),
        'id'          => 'name',
        'title'       => __( 'Name' ),
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

<x-data-tables id="sports_tag_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

{{-- ── Add Modal ─────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal_add_tag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'template.add_x', [ 'title' => __( 'template.amenity' ) ] ) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">{{ __( 'Name' ) }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="add_tag_name" placeholder="e.g. Parking, Shower, Cafe">
                    <div class="invalid-feedback d-block" id="add_tag_name_error"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __( 'Icon' ) }} <span class="text-muted fw-normal">({{ __( 'template.optional' ) }})</span></label>
                    <input type="file" class="form-control" id="add_tag_icon" accept="image/jpg,image/jpeg,image/png,image/svg+xml">
                    <div class="form-text">PNG, JPG, SVG. Max 2 MB.</div>
                    <div class="mt-2" id="add_tag_icon_preview_wrap" style="display:none;">
                        <img id="add_tag_icon_preview" src="" style="width:40px;height:40px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;">
                    </div>
                    <div class="invalid-feedback d-block" id="add_tag_icon_error"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_add_tag_submit">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Modal ────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal_edit_tag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'template.edit_x', [ 'title' => __( 'template.amenity' ) ] ) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_tag_id">

                <div class="mb-3">
                    <label class="form-label">{{ __( 'Name' ) }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_tag_name">
                    <div class="invalid-feedback d-block" id="edit_tag_name_error"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __( 'Icon' ) }}</label>
                    <div class="mb-2" id="edit_tag_current_icon_wrap" style="display:none;">
                        <img id="edit_tag_current_icon" src="" style="width:40px;height:40px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;">
                        <div class="form-text">{{ __( 'Current icon' ) }}</div>
                    </div>
                    <input type="file" class="form-control" id="edit_tag_icon" accept="image/jpg,image/jpeg,image/png,image/svg+xml">
                    <div class="form-text">{{ __( 'template.leave_blank' ) }}</div>
                    <div class="mt-2" id="edit_tag_icon_preview_wrap" style="display:none;">
                        <img id="edit_tag_icon_preview" src="" style="width:40px;height:40px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;">
                    </div>
                    <div class="invalid-feedback d-block" id="edit_tag_icon_error"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-primary" id="btn_edit_tag_submit">{{ __( 'template.save_changes' ) }}</button>
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
    dt_table_name = '#sports_tag_table',
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
            url: '{{ route( 'admin.sports_tag.allSportsTags' ) }}',
            data: function( d ) {
                d['_token']       = '{{ csrf_token() }}';
                d['name']         = window['name'];
                d['status']       = window['status'];
                d['created_date'] = window['created_date'];
            },
            dataSrc: 'sports_tags',
        },
        lengthMenu: [[10, 25], [10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'icon_path' },
            { data: 'name' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "icon" ) }}' ),
                orderable: false,
                render: function( data, type, row ) {
                    return data
                        ? `<img src="${data}" style="width:32px;height:32px;object-fit:contain;">`
                        : '<span class="text-muted">—</span>';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "name" ) }}' ),
                render: function( data, type, row ) {
                    return data ? data : '-';
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

                    @canany( [ 'edit sports', 'delete sports' ] )
                    let edit = '', status = '', del = '';

                    @can( 'edit sports' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete sports' )
                    status = row['status'] == 10
                        ? '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>'
                        : '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    del = '<li class="dt-delete" data-id="' + row['encrypted_id'] + '"><a href="#" class="text-danger"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';
                    @endcan

                    let html = `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                    `+del+`
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

        let modalAdd  = new bootstrap.Modal( document.getElementById( 'modal_add_tag' ) );
        let modalEdit = new bootstrap.Modal( document.getElementById( 'modal_edit_tag' ) );

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data( 'id' )] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        // ── Add icon preview ──────────────────────────────────────────────────
        $( '#add_tag_icon' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( '#add_tag_icon_preview' ).attr( 'src', e.target.result );
                    $( '#add_tag_icon_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( '#add_tag_icon_preview_wrap' ).hide();
            }
        } );

        // ── Edit icon preview ─────────────────────────────────────────────────
        $( '#edit_tag_icon' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( '#edit_tag_icon_preview' ).attr( 'src', e.target.result );
                    $( '#edit_tag_icon_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( '#edit_tag_icon_preview_wrap' ).hide();
            }
        } );

        // ── Add submit ────────────────────────────────────────────────────────
        $( '#btn_add_tag_submit' ).click( function() {
            $( '#add_tag_name_error, #add_tag_icon_error' ).text( '' );
            $( '#add_tag_name' ).removeClass( 'is-invalid' );

            let formData = new FormData();
            formData.append( 'name', $( '#add_tag_name' ).val() );
            let iconFile = $( '#add_tag_icon' )[0].files[0];
            if ( iconFile ) formData.append( 'icon', iconFile );
            formData.append( '_token', '{{ csrf_token() }}' );

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.sports_tag.createSportsTag' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    modalAdd.hide();
                    $( '#add_tag_name' ).val( '' );
                    $( '#add_tag_icon' ).val( '' );
                    $( '#add_tag_icon_preview_wrap' ).hide();
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        if ( errors.name ) { $( '#add_tag_name' ).addClass( 'is-invalid' ); $( '#add_tag_name_error' ).text( errors.name[0] ); }
                        if ( errors.icon ) { $( '#add_tag_icon_error' ).text( errors.icon[0] ); }
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // ── Open edit modal ───────────────────────────────────────────────────
        $( document ).on( 'click', '.dt-edit', function() {
            let id = $( this ).data( 'id' );
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.sports_tag.oneSportsTag' ) }}',
                type: 'POST',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#edit_tag_id' ).val( response.encrypted_id );
                    $( '#edit_tag_name' ).val( response.name ).removeClass( 'is-invalid' );
                    $( '#edit_tag_name_error, #edit_tag_icon_error' ).text( '' );
                    $( '#edit_tag_icon' ).val( '' );
                    $( '#edit_tag_icon_preview_wrap' ).hide();

                    if ( response.icon_path ) {
                        $( '#edit_tag_current_icon' ).attr( 'src', response.icon_path );
                        $( '#edit_tag_current_icon_wrap' ).show();
                    } else {
                        $( '#edit_tag_current_icon_wrap' ).hide();
                    }

                    modalEdit.show();
                },
                error: function() {
                    $( 'body' ).loading( 'stop' );
                }
            } );
        } );

        // ── Edit submit ───────────────────────────────────────────────────────
        $( '#btn_edit_tag_submit' ).click( function() {
            $( '#edit_tag_name_error, #edit_tag_icon_error' ).text( '' );
            $( '#edit_tag_name' ).removeClass( 'is-invalid' );

            let formData = new FormData();
            formData.append( 'id',   $( '#edit_tag_id' ).val() );
            formData.append( 'name', $( '#edit_tag_name' ).val() );
            let iconFile = $( '#edit_tag_icon' )[0].files[0];
            if ( iconFile ) formData.append( 'icon', iconFile );
            formData.append( '_token', '{{ csrf_token() }}' );

            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.sports_tag.updateSportsTag' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    modalEdit.hide();
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        if ( errors.name ) { $( '#edit_tag_name' ).addClass( 'is-invalid' ); $( '#edit_tag_name_error' ).text( errors.name[0] ); }
                        if ( errors.icon ) { $( '#edit_tag_icon_error' ).text( errors.icon[0] ); }
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // ── Status toggle ─────────────────────────────────────────────────────
        $( document ).on( 'click', '.dt-status', function() {
            $.ajax( {
                url: '{{ route( 'admin.sports_tag.updateSportsTagStatus' ) }}',
                type: 'POST',
                data: {
                    'id':     $( this ).data( 'id' ),
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

        // ── Delete ────────────────────────────────────────────────────────────
        $( document ).on( 'click', '.dt-delete', function() {
            if ( !confirm( '{{ __( 'template.are_you_sure' ) }}' ) ) return;

            let id = $( this ).data( 'id' );
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            $.ajax( {
                url: '{{ route( 'admin.sports_tag.deleteSportsTag' ) }}',
                type: 'POST',
                data: { id: id, _token: '{{ csrf_token() }}' },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.toggle();
                }
            } );
        } );

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
