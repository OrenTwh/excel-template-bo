<?php
$amenity_create = 'amenity_create';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="mb-3">
                <strong>{{ __( 'template.general_info' ) }}</strong>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $amenity_create }}_title" class="col-sm-5 col-form-label">{{ __( 'template.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $amenity_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="mb-3">
                <strong>{{ __( 'template.icon_image' ) }}</strong>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">{{ __( 'template.icon' ) }} <small class="text-muted">({{ __( 'template.ttf_png_supported' ) }})</small></label>
                    <div class="image-upload-zone" id="{{ $amenity_create }}_image_upload">
                        <div class="upload-content">
                            <i class="ni ni-upload" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="text-muted mb-0">{{ __( 'template.drag_drop_or_click' ) }}</p>
                            <small class="text-muted">{{ __( 'template.max_size_5mb' ) }}</small>
                        </div>
                        <input type="file" id="{{ $amenity_create }}_image_input" class="d-none" accept=".png,.jpg,.jpeg,.ttf,.otf,.svg">
                    </div>
                    <div id="{{ $amenity_create }}_image_preview" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between border rounded p-2">
                            <div class="d-flex align-items-center">
                                <div id="{{ $amenity_create }}_image_thumbnail_container" style="width: 50px; height: 50px;" class="rounded me-3 d-flex align-items-center justify-content-center border">
                                    <img id="{{ $amenity_create }}_image_thumbnail" src="" alt="" style="max-width: 50px; max-height: 50px; object-fit: contain;" class="rounded">
                                    <div id="{{ $amenity_create }}_file_icon" style="display: none;">
                                        <i class="ni ni-single-copy-04" style="font-size: 1.5rem; color: #6c757d;"></i>
                                    </div>
                                </div>
                                <div>
                                    <div id="{{ $amenity_create }}_image_name" class="fw-bold"></div>
                                    <small id="{{ $amenity_create }}_image_size" class="text-muted"></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $amenity_create }}_remove_image">
                                <i class="ni ni-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button id="{{ $amenity_create }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $amenity_create }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    var uploadedFiles = [];

    document.addEventListener( 'DOMContentLoaded', function() {

        let uc = '#{{ $amenity_create }}';
        
        // Image upload handling
        const imageUploadZone = document.getElementById('{{ $amenity_create }}_image_upload');
        const imageInput = document.getElementById('{{ $amenity_create }}_image_input');
        const imagePreview = document.getElementById('{{ $amenity_create }}_image_preview');
        const imageThumbnail = document.getElementById('{{ $amenity_create }}_image_thumbnail');
        const imageName = document.getElementById('{{ $amenity_create }}_image_name');
        const imageSize = document.getElementById('{{ $amenity_create }}_image_size');
        const removeImageBtn = document.getElementById('{{ $amenity_create }}_remove_image');
        const fileIcon = document.getElementById('{{ $amenity_create }}_file_icon');
        const thumbnailContainer = document.getElementById('{{ $amenity_create }}_image_thumbnail_container');

        imageUploadZone.addEventListener('click', () => imageInput.click());
        imageUploadZone.addEventListener('dragover', handleDragOver);
        imageUploadZone.addEventListener('drop', handleDrop);
        imageInput.addEventListener('change', handleFileSelect);
        removeImageBtn.addEventListener('click', removeImage);

        function handleDragOver(e) {
            e.preventDefault();
            imageUploadZone.classList.add('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            imageUploadZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        }

        function handleFile(file) {
            // Validate file type - Updated to properly support SVG
            const allowedTypes = [
                'image/png', 
                'image/jpeg', 
                'image/jpg', 
                'image/svg+xml',
                'font/ttf', 
                'application/x-font-ttf', 
                'font/otf', 
                'application/x-font-otf'
            ];
            
            const allowedExtensions = ['.png', '.jpg', '.jpeg', '.svg', '.ttf', '.otf'];
            const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
            
            if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                alert('{{ __( 'template.invalid_file_type' ) }}. Supported formats: PNG, JPG, JPEG, SVG, TTF, OTF');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('{{ __( 'template.file_too_large' ) }}');
                return;
            }

            // Upload file to temporary storage
            const formData = new FormData();
            formData.append('file', file);
            formData.append('amenity', true);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route( 'admin.file.upload' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    uploadedFiles = [response.data.id];
                    displayImagePreview(file, response.url || URL.createObjectURL(file));
                    console.log(uploadedFiles)
                },
                error: function(error) {
                    alert('{{ __( 'template.upload_failed' ) }}');
                    console.error('Upload error:', error);
                }
            });
        }

        function displayImagePreview(file, url) {
            imageName.textContent = file.name;
            imageSize.textContent = formatFileSize(file.size);
            
            // Handle different file types for preview
            const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
            const imageTypes = ['.png', '.svg'];
            
            if (imageTypes.includes(fileExtension) || file.type.startsWith('image/')) {
                imageThumbnail.src = url;
                imageThumbnail.style.display = 'block';
                fileIcon.style.display = 'none';
                thumbnailContainer.style.backgroundColor = 'transparent';
                
                // Special handling for SVG files
                if (fileExtension === '.svg' || file.type === 'image/svg+xml') {
                    imageThumbnail.style.backgroundColor = '#f8f9fa';
                    imageThumbnail.style.padding = '5px';
                }
            } else {
                // Show file icon for non-image files (TTF, OTF)
                imageThumbnail.style.display = 'none';
                fileIcon.style.display = 'block';
                thumbnailContainer.style.backgroundColor = '#f8f9fa';
            }
            
            imagePreview.style.display = 'block';
            imageUploadZone.style.display = 'none';
        }

        function removeImage() {
            uploadedFiles = [];
            imagePreview.style.display = 'none';
            imageUploadZone.style.display = 'block';
            imageInput.value = '';
            
            // Reset thumbnail state
            imageThumbnail.src = '';
            imageThumbnail.style.display = 'none';
            imageThumbnail.style.backgroundColor = 'transparent';
            imageThumbnail.style.padding = '0';
            fileIcon.style.display = 'none';
            thumbnailContainer.style.backgroundColor = 'transparent';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        $( uc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.amenity.index' ) }}';
        } );

        $( uc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'title', $( uc + '_title' ).val() );
            if (uploadedFiles.length > 0) {
                formData.append( 'icon', uploadedFiles.join(',') );
            }
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.amenity.createAmenity' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.amenity.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( uc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

<style>
    .image-upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 2rem;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    .image-upload-zone:hover {
        border-color: #adb5bd;
        background-color: #e9ecef;
    }
    .image-upload-zone.dragover {
        border-color: #0d6efd;
        background-color: #cff4fc;
        transform: scale(1.02);
    }
    
    /* SVG specific styling */
    #{{ $amenity_create }}_image_thumbnail {
        border-radius: 0.25rem;
    }
</style>