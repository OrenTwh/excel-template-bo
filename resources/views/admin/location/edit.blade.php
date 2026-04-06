<?php $location_edit = 'location_edit'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => 'Location' ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_parent_id" class="col-sm-3 col-form-label">{{ __( 'Parent Location' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $location_edit }}_parent_id">
                            <option value="">{{ __( 'None (Root Location)' ) }}</option>
                            @foreach($data['parent_locations'] as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->full_path }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_type" class="col-sm-3 col-form-label">{{ __( 'Type' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $location_edit }}_type">
                            @foreach($data['types'] as $key => $type)
                                <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_name" class="col-sm-3 col-form-label">{{ __( 'Name' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $location_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_slug" class="col-sm-3 col-form-label">{{ __( 'Slug' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $location_edit }}_slug">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_description" class="col-sm-3 col-form-label">{{ __( 'Description' ) }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="min-height: 80px;" id="{{ $location_edit }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_code" class="col-sm-3 col-form-label">{{ __( 'Code' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $location_edit }}_code" placeholder="Postal code, area code, etc.">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_latitude" class="col-sm-3 col-form-label">{{ __( 'Latitude' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.00000001" class="form-control" id="{{ $location_edit }}_latitude" placeholder="e.g., 3.1390">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_longitude" class="col-sm-3 col-form-label">{{ __( 'Longitude' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.00000001" class="form-control" id="{{ $location_edit }}_longitude" placeholder="e.g., 101.6869">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $location_edit }}_active" class="col-sm-3 col-form-label">{{ __( 'Active' ) }}</label>
                    <div class="col-sm-9">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $location_edit }}_active">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $location_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $location_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $location_edit }}';

        // Auto-generate slug from name
        $( fe + '_name' ).on( 'input', function() {
            let slug = $( this ).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $( fe + '_slug' ).val( slug );
        } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.location.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'name', $( fe + '_name' ).val() );
            formData.append( 'slug', $( fe + '_slug' ).val() );
            formData.append( 'type', $( fe + '_type' ).val() );
            formData.append( 'description', $( fe + '_description' ).val() );
            formData.append( 'code', $( fe + '_code' ).val() );
            formData.append( 'latitude', $( fe + '_latitude' ).val() );
            formData.append( 'longitude', $( fe + '_longitude' ).val() );

            let parentId = $( fe + '_parent_id' ).val();
            if ( parentId ) {
                formData.append( 'parent_id', parentId );
            }

            if ( $( fe + '_active' ).is( ':checked' ) ) {
                formData.append( 'active', 1 );
            }
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.location.updateLocation' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.location.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getLocation();

        function getLocation() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.location.oneLocation' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( fe + '_name' ).val( response.name );
                    $( fe + '_slug' ).val( response.slug );
                    $( fe + '_type' ).val( response.type );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_code' ).val( response.code );
                    $( fe + '_latitude' ).val( response.latitude );
                    $( fe + '_longitude' ).val( response.longitude );

                    if ( response.parent_id ) {
                        $( fe + '_parent_id' ).val( response.parent_id );
                    }

                    if ( response.active ) {
                        $( fe + '_active' ).prop( 'checked', true );
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>
