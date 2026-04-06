<?php $sport_create = 'sport_create'; ?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => 'Sport' ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_name" class="col-sm-3 col-form-label">{{ __( 'Name' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $sport_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $sport_create }}_slug" class="col-sm-3 col-form-label">{{ __( 'Slug' ) }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="{{ $sport_create }}_slug">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_type" class="col-sm-3 col-form-label">{{ __( 'Type' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $sport_create }}_type">
                            <option value="">— Select Type —</option>
                            @foreach( $data['type_options'] as $value => $label )
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_pricing_method" class="col-sm-3 col-form-label">{{ __( 'Pricing Method' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $sport_create }}_pricing_method">
                            <option value="">— Select Pricing Method —</option>
                            @foreach( $data['pricing_method_options'] as $value => $label )
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_description" class="col-sm-3 col-form-label">{{ __( 'Description' ) }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="min-height: 80px;" id="{{ $sport_create }}_description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_min_players" class="col-sm-3 col-form-label">{{ __( 'Minimum Players' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="{{ $sport_create }}_min_players" value="1" min="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $sport_create }}_max_players" class="col-sm-3 col-form-label">{{ __( 'Maximum Players' ) }}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="{{ $sport_create }}_max_players" min="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">{{ __( 'Icon' ) }}</label>
                    <div class="col-sm-9">
                        <div class="dropzone mb-3" id="{{ $sport_create }}_icon" style="min-height: 0px;">
                            <div class="dz-message needsclick">
                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">{{ __( 'Image' ) }}</label>
                    <div class="col-sm-9">
                        <div class="dropzone mb-3" id="{{ $sport_create }}_image" style="min-height: 0px;">
                            <div class="dz-message needsclick">
                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label class="col-sm-3 col-form-label">{{ __( 'Thumbnail' ) }}</label>
                    <div class="col-sm-9">
                        <div class="dropzone mb-3" id="{{ $sport_create }}_thumbnail" style="min-height: 0px;">
                            <div class="dz-message needsclick">
                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $sport_create }}_tags" class="col-sm-3 col-form-label">{{ __( 'Tags' ) }}</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="{{ $sport_create }}_tags" data-placeholder="{{ __( 'Search or create tags...' ) }}" multiple></select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $sport_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $sport_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $sport_create }}',
            fileID = '',
            imageFileID = '',
            thumbnailFileID = '';

        // Auto-generate slug from name
        $( fc + '_name' ).on( 'input', function() {
            let slug = $( this ).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $( fc + '_slug' ).val( slug );
        } );

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.sport.index' ) }}';
        } );

        $( fc + '_tags' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $( this ).data( 'placeholder' ),
            tags: true,
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.sports_tag.all' ) }}',
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        name: params.term,
                        start: 0,
                        length: 20,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function( data ) {
                    return {
                        results: data.sports_tags.map( function( tag ) {
                            return { id: tag.id, text: tag.name };
                        } )
                    };
                }
            },
            createTag: function( params ) {
                let term = $.trim( params.term );
                if ( term === '' ) return null;
                return { id: 'new:' + term, text: term, newTag: true };
            },
            templateResult: function( data ) {
                if ( data.newTag ) {
                    return $( '<span><em>Create: </em>' + data.text + '</span>' );
                }
                return data.text;
            }
        } );

        // Initialize Dropzone for icon
        Dropzone.autoDiscover = false;
        let iconDropzone = new Dropzone( fc + '_icon', {
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml',
            addRemoveLinks: true,
            params: {
                '_token': '{{ csrf_token() }}'
            },
            removedfile: function( file ) {
                if ( file.id ) {
                    var idArrays = fileID.split(/\s*,\s*/).filter( Boolean );
                    var idx = idArrays.indexOf( file.id.toString() );
                    if ( idx !== -1 ) idArrays.splice( idx, 1 );
                    fileID = idArrays.join( ',' );
                }
                if ( file.previewElement ) file.previewElement.remove();
            },
            success: function( file, response ) {
                if ( response.status == 200 ) {
                    if ( fileID !== '' ) {
                        fileID += ',';
                    }
                    fileID += response.data.id;

                    file.id = response.data.id;
                }
            }
        } );

        let imageDropzone = new Dropzone( fc + '_image', {
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp',
            addRemoveLinks: true,
            params: {
                '_token': '{{ csrf_token() }}'
            },
            removedfile: function( file ) {
                if ( file.id ) {
                    var idArrays = imageFileID.split(/\s*,\s*/).filter( Boolean );
                    var idx = idArrays.indexOf( file.id.toString() );
                    if ( idx !== -1 ) idArrays.splice( idx, 1 );
                    imageFileID = idArrays.join( ',' );
                }
                if ( file.previewElement ) file.previewElement.remove();
            },
            success: function( file, response ) {
                if ( response.status == 200 ) {
                    if ( imageFileID !== '' ) {
                        imageFileID += ',';
                    }
                    imageFileID += response.data.id;
                    file.id = response.data.id;
                }
            }
        } );

        let thumbnailDropzone = new Dropzone( fc + '_thumbnail', {
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp',
            addRemoveLinks: true,
            params: {
                '_token': '{{ csrf_token() }}'
            },
            removedfile: function( file ) {
                if ( file.id ) {
                    var idArrays = thumbnailFileID.split(/\s*,\s*/).filter( Boolean );
                    var idx = idArrays.indexOf( file.id.toString() );
                    if ( idx !== -1 ) idArrays.splice( idx, 1 );
                    thumbnailFileID = idArrays.join( ',' );
                }
                if ( file.previewElement ) file.previewElement.remove();
            },
            success: function( file, response ) {
                if ( response.status == 200 ) {
                    if ( thumbnailFileID !== '' ) {
                        thumbnailFileID += ',';
                    }
                    thumbnailFileID += response.data.id;
                    file.id = response.data.id;
                }
            }
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( fc + '_name' ).val() );
            formData.append( 'slug', $( fc + '_slug' ).val() );
            formData.append( 'type', $( fc + '_type' ).val() );
            formData.append( 'pricing_method', $( fc + '_pricing_method' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'min_players', $( fc + '_min_players' ).val() );
            formData.append( 'max_players', $( fc + '_max_players' ).val() );
            formData.append( 'icon', fileID );
            formData.append( 'image', imageFileID );
            formData.append( 'thumbnail', thumbnailFileID );
            let tags = [];
            $( fc + '_tags' ).find( ':selected' ).each( function() {
                tags.push( $( this ).val() );
            } );
            formData.append( 'tags', JSON.stringify( tags ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.sport.createSport' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.sport.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

    } );
</script>
