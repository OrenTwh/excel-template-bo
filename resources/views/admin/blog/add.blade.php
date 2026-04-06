<?php
$blog_create = 'blog_create';
$languages = Config::get( 'languages' );
$languageKey = array_keys( $languages );
?>
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
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => 'Blog' ] ) }}</h3>
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
                            <label for="{{ $blog_create }}_{{ $lang }}_title" class="col-sm-4 col-form-label">{{ __( 'blog.title' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_create }}_{{ $lang }}_title">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $blog_create }}_{{ $lang }}_subtitle" class="col-sm-4 col-form-label">{{ __( 'blog.subtitle' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_create }}_{{ $lang }}_subtitle">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="{{ $blog_create }}_{{ $lang }}_description" class="col-sm-4 col-form-label">{{ __( 'blog.description' ) }} </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="{{ $blog_create }}_{{ $lang }}_description" row="10">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>{{ __( 'blog.image' ) }} </label>
                            <div class="dropzone mb-3" id="{{ $blog_create }}_{{ $lang }}_image" style="min-height: 0px;">
                                <div class="dz-message needsclick">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>    

                        <div class="mb-3">
                            <label>{{ __( 'blog.gallery' ) }} </label>
                            <div class="dropzone mb-3" id="{{ $blog_create }}_{{ $lang }}_gallery" style="min-height: 0px;">
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
                    <label for="{{ $blog_create }}_meta_title" class="col-sm-5 form-label">{{ __( 'blog.meta_title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $blog_create }}_meta_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>  
                <div class="mb-3 row">
                    <label for="{{ $blog_create }}_meta_desc" class="col-sm-5 form-label">{{ __( 'blog.meta_desc' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $blog_create }}_meta_desc">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>      

                <div class="mb-3 row">
                    <label for="{{ $blog_create }}_publish_date" class="col-sm-5 col-form-label">{{ __( 'blog.publish_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $blog_create }}_publish_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_create }}_tags" class="col-sm-5 col-form-label">{{ __( 'blog.tags' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_create }}_tags" class="form-control form-control-sm" data-role="tagsinput">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_create }}_slug" class="col-sm-5 col-form-label">{{ __( 'blog.slug' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_create }}_slug" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>    
                <div class="text-end">
                    <button id="{{ $blog_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $blog_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
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
    collect( $languageKey )->map( fn( $lang ) => "blog_create_{$lang}_description")->values()
);
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multiple.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $blog_create }}',
            fileID = '',
            languages = @json( $languageKey ),
            blogImageIDs = {},
            blogGalleryIDs = {};

        $( fc + '_publish_date' ).flatpickr( {
            dateFormat: "Y-m-d",
        } );

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.blog.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            languages.forEach( lang => {
                const titleSelector = `${fc}_${lang}_title`;
                const shortDescSelector = `${fc}_${lang}_subtitle`;
                const editorKey = `blog_create_${lang}_description`;

                const titleVal = $( titleSelector ).val() ?? null;
                const shortDescVal = $( shortDescSelector ).val() ?? null;
                const descVal = editors[editorKey] ? editors[editorKey].getData() : null;
                
                const imageID = blogImageIDs[lang];
                const galleryIDs = blogGalleryIDs[lang];

                formData.append( `${lang}_title`, titleVal );
                formData.append( `${lang}_subtitle`, shortDescVal );
                formData.append( `${lang}_description`, descVal );
                formData.append( `${lang}_image`, imageID );
                formData.append( `${lang}_gallery`, JSON.stringify( galleryIDs ) );

            } );
            formData.append( 'meta_title', $( fc + '_meta_title' ).val() ?? '' );
            formData.append( 'meta_desc', $( fc + '_meta_desc' ).val() ?? '' );
            formData.append( 'publish_date', $( fc + '_publish_date' ).val() );
            formData.append( 'tag', $( fc + '_tags' ).val() );
            formData.append( 'slug', $( fc + '_slug' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.blog.createBlog' ) }}',
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
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        languages.forEach( lang => {
            
            blogImageIDs[lang] = '';
            blogGalleryIDs[lang] = [];

            const dropzoneImage = new Dropzone( fc + `_${lang}_image`, {
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
                },
                removedfile: function( file ) {
                    blogImageIDs[lang] = '';
                    file.previewElement.remove();
                },
                success: function( file, response ) {
                    blogImageIDs[lang] = response.data.file;
                }
            } );
            
            const dropzoneGallery = new Dropzone( fc + `_${lang}_gallery`, {
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
                        // Clean up video URL if it exists
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

    } );
</script>