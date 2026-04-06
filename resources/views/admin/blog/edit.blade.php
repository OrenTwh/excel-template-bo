@php
$languages = Config::get('languages');
$languageKey = array_keys($languages);
$blog_edit = 'blog_edit';
@endphp

<style>
    .bootstrap-tagsinput .tag {
        border: 1px solid black;
        background-color: #5d5d5d;
        border-radius: 10px;
        padding: 2px 5px;
    }
    .bootstrap-tagsinput {
        width: 100% !important;
    }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => 'Blog' ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                {{-- <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist" style="gap:20px;">
                        @foreach ( $languages as $lang => $langName )
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $lang }}_name-tab" data-bs-toggle="tab" data-bs-target="#{{ $lang }}_name" type="button" role="tab" aria-controls="{{ $lang }}_name" aria-selected="{{ $loop->first ? 'true' : 'false' }}"> {{ $langName }} </button>
                        @endforeach
                    </div>
                </nav> --}}
                <div class="tab-content" id="nav-tabContent">
                    @foreach ($languages as $lang => $langName)
                    <div class="tab-pane fade pt-4 {{ $loop->first ? 'show active' : '' }}" id="{{ $lang }}_name" role="tabpanel" aria-labelledby="{{ $lang }}_name-tab">
                        <div class="mb-3 row">
                            <label for="{{ $blog_edit }}_{{ $lang }}_title" class="col-sm-4 col-form-label">{{ __( 'blog.title' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_{{ $lang }}_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $blog_edit }}_{{ $lang }}_subtitle" class="col-sm-4 col-form-label">{{ __( 'blog.subtitle' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_{{ $lang }}_subtitle">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $blog_edit }}_{{ $lang }}_description" class="col-sm-4 col-form-label">{{ __( 'blog.description' ) }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_{{ $lang }}_description" row="10">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>{{ __( 'blog.image' ) }} </label>
                            <div class="dropzone mb-3" id="{{ $blog_edit }}_{{ $lang }}_image" style="min-height: 0px;">
                                <div class="dz-message needsclick">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>    

                        <div class="mb-3">
                            <label>{{ __( 'blog.gallery' ) }} </label>
                            <div class="dropzone mb-3" id="{{ $blog_edit }}_{{ $lang }}_gallery" style="min-height: 0px;">
                                <div class="dz-message needsclick">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>    
                    </div>
                    @endforeach
                </div>

                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_meta_title" class="col-sm-5 col-form-label">{{ __( 'blog.meta_title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $blog_edit }}_meta_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>  
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_meta_desc" class="col-sm-5 col-form-label">{{ __( 'blog.meta_desc' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $blog_edit }}_meta_desc">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>      

                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_publish_date" class="col-sm-5 col-form-label">{{ __( 'blog.publish_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_publish_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_tags" class="col-sm-5 col-form-label">{{ __( 'blog.tags' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_edit }}_tags" class="form-control form-control-sm" data-role="tagsinput">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_slug" class="col-sm-5 col-form-label">{{ __( 'blog.slug' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_edit }}_slug" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>    
                <div class="text-end">
                    <button id="{{ $blog_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $blog_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.blog.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = @json(
    collect( $languageKey )->map( fn( $lang ) => "blog_edit_{$lang}_description")->values()
);
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multi.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $blog_edit }}',
            fileID = '',
            languages = @json( $languageKey ),
            blogImageIDs = {},
            blogGalleryIDs = {};

        $( fe + '_publish_date' ).flatpickr( {
            dateFormat: "Y-m-d",
        } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.blog.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request('id') }}' );
            languages.forEach( lang => {
                const titleSelector = `${fe}_${lang}_title`;
                const shortDescSelector = `${fe}_${lang}_subtitle`;
                const editorKey = `blog_edit_${lang}_description`;

                const titleVal = $( titleSelector ).val() ?? null;
                const shortDescVal = $( shortDescSelector ).val() ?? null;
                const descVal = editors[editorKey] ? editors[editorKey].getData() : null;

                const imageID = blogImageIDs[lang];
                const galleryIDs = blogGalleryIDs[lang];

                formData.append( `${lang}_subtitle`, shortDescVal );
                formData.append( `${lang}_title`, titleVal );
                formData.append( `${lang}_description`, descVal );
                formData.append( `${lang}_image`, imageID );
                formData.append( `${lang}_gallery`, JSON.stringify( galleryIDs ) );
            } );
            formData.append( 'meta_title', $( fe + '_meta_title' ).val() ?? '' );
            formData.append( 'meta_desc', $( fe + '_meta_desc' ).val() ?? '' );
            formData.append( 'publish_date', $( fe + '_publish_date' ).val() );
            formData.append( 'tag', $( fe + '_tags' ).val() );
            formData.append( 'slug', $( fe + '_slug' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.blog.updateBlog' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.blog.index' ) }}';
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

        Dropzone.autoDiscover = false;
        getBlog();

        function getBlog() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.blog.oneBlog' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    let titles = JSON.parse( response.multi_lang_title ?? {} );
                    let descs = JSON.parse( response.multi_lang_description ?? {} );
                    let short_descs = JSON.parse( response.multi_lang_subtitle ?? {} );
                    let images = JSON.parse( response.multi_lang_image ?? {} );
                    const baseImagePath = '{{ asset("storage") }}/';
                    
                    languages.forEach( lang => {
                        $( `${fe}_${lang}_title` ).val( titles[lang] ?? '' );
                        $( `${fe}_${lang}_subtitle` ).val( short_descs[lang] ?? '' );
                        const editorKey = `blog_edit_${lang}_description`;
                        if ( editors[editorKey] ) {
                            editors[editorKey].setData( descs[lang] ?? '' );
                        }
                        
                        if( images[lang] && images[lang] != 'null' ) {
                            imagePath = baseImagePath + images[lang];
                            blogImageIDs[lang] = images[lang];
                        } else {
                            imagePath = null;
                            blogImageIDs[lang] = null;
                        }
                        blogGalleryIDs[lang] = [];

                        const dropzoneImage = new Dropzone( fe + `_${lang}_image`, { 
                            url: '{{ route( 'admin.file.upload' ) }}',
                            maxFiles: 1,
                            acceptedFiles: 'image/*,.heic,.heif,.webp',
                            addRemoveLinks: true,
                            init: function() {
                                this.on("addedfile", function (file) {
                                    if (this.files.length > 1) {
                                        this.removeFile(this.files[0]);
                                    }
                                });
                                if ( imagePath ) {
                                    let myDropzone = this,
                                        mockFile = { name: 'Default', size: 1024, accepted: true };

                                    myDropzone.files.push( mockFile );
                                    myDropzone.displayExistingFile( mockFile, imagePath );
                                }
                            },
                            removedfile: function( file ) {
                                blogImageIDs[lang] = '';
                                file.previewElement.remove();
                            },
                            success: function( file, response ) {
                                blogImageIDs[lang] = response.data.file;
                            }
                        } );
                        
                        const dropzoneGallery = new Dropzone( fe + `_${lang}_gallery`, {
                            url: '{{ route("admin.file.upload") }}',
                            acceptedFiles: 'image/*,.heic,.heif,.webp,video/*',
                            addRemoveLinks: true,
                            previewTemplate: `
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-image">
                                        <img data-dz-thumbnail />
                                        <video class="dz-video" controls style="display: none;">
                                            <source data-dz-video-source />
                                        </video>
                                    </div>
                                    <div class="dz-error-message"><span data-dz-errormessage></span></div>
                                    <div class="dz-success-mark"><span>✔</span></div>
                                    <div class="dz-error-mark"><span>✘</span></div>
                                </div>
                            `,
                            init: function() {
                                const dz = this;
                                const allGallery = response.all_gallery || [];
                                const galleryList = allGallery.filter(g => g.lang === lang);

                                galleryList.forEach(function(image) {
                                    const storedId = image.image;
                                    const publicUrl = image.image_path;

                                    const mockFile = {
                                        name: image.name ?? 'default',
                                        size: image.size ?? 1024,
                                        accepted: true,
                                        fileID: storedId
                                    };

                                    dz.displayExistingFile(mockFile, publicUrl);

                                    setTimeout(() => {
                                        blogGalleryIDs[lang].push(storedId);

                                        const previewElement = mockFile.previewElement;
                                        if (!previewElement) return;

                                        // detect video type
                                        if (storedId.match(/\.(mp4|webm|ogg|avi|mov|quicktime)$/i)) {
                                            const imgPreview = previewElement.querySelector('img[data-dz-thumbnail]');
                                            const videoPreview = previewElement.querySelector('.dz-video');
                                            const videoSource = videoPreview?.querySelector('[data-dz-video-source]');
                                            if (imgPreview) imgPreview.style.display = 'none';
                                            if (videoPreview && videoSource) {
                                                videoPreview.style.display = 'block';
                                                videoSource.src = publicUrl;
                                                try { videoPreview.load(); } catch (e) {}
                                            }
                                        } else {
                                            const videoPreview = previewElement.querySelector('.dz-video');
                                            if (videoPreview) videoPreview.style.display = 'none';
                                        }

                                        dz.emit('complete', mockFile);
                                    }, 50);
                                });

                                // normal add/remove events
                                this.on("addedfile", function(file) {
                                    if (file.type.match(/video/)) {
                                        let previewElement = file.previewElement;
                                        let imgPreview = previewElement.querySelector('img[data-dz-thumbnail]');
                                        let videoPreview = previewElement.querySelector('.dz-video');
                                        let videoSource = videoPreview.querySelector('[data-dz-video-source]');
                                        
                                        imgPreview.style.display = 'none';
                                        videoPreview.style.display = 'block';
                                        
                                        let videoUrl = URL.createObjectURL(file);
                                        videoSource.src = videoUrl;
                                        videoPreview.load();
                                    }
                                });

                                this.on("success", function(file, response) {
                                    if (response.data && response.data.file) {
                                        file.fileID = response.data.file;
                                        blogGalleryIDs[lang].push(response.data.file);
                                    }
                                });

                                this.on("removedfile", function(file) {
                                    if (file.fileID) {
                                        blogGalleryIDs[lang] = blogGalleryIDs[lang].filter(url => url !== file.fileID);
                                    }
                                    if (file.type.match(/video/)) {
                                        let videoPreview = file.previewElement.querySelector('.dz-video source');
                                        if (videoPreview && videoPreview.src) {
                                            URL.revokeObjectURL(videoPreview.src);
                                        }
                                    }
                                });
                            }

                        });
                    
                    } );

                    $( fe + '_meta_title' ).val( response.meta_title );
                    $( fe + '_meta_desc' ).val( response.meta_desc );
                    $( fe + '_publish_date' ).val( response.publish_date ? response.publish_date.split(' ')[0] : '' );
                    $( fe + '_slug' ).val( response.slug );

                    $.each( response.tags, function( i, v ) {
                        $( fe + '_tags').tagsinput( 'add', v.tag );
                    } );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>