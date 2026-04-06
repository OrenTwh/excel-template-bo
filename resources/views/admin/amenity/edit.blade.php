<?php
$amenity_edit = 'amenity_edit';
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="mb-3">
                <strong>{{ __( 'template.general_info' ) }}</strong>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $amenity_edit }}_title" class="col-sm-5 col-form-label">{{ __( 'template.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $amenity_edit }}_title">
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
                    
                    <!-- Current Image Display -->
                    <div id="{{ $amenity_edit }}_current_image" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-3">
                            <div class="d-flex align-items-center">
                                <div id="{{ $amenity_edit }}_current_thumbnail_container" style="width: 50px; height: 50px;" class="rounded me-3 d-flex align-items-center justify-content-center border">
                                    <img id="{{ $amenity_edit }}_current_thumbnail" src="" alt="" style="max-width: 50px; max-height: 50px; object-fit: contain;" class="rounded">
                                    <div id="{{ $amenity_edit }}_current_file_icon" style="display: none;">
                                        <i class="ni ni-single-copy-04" style="font-size: 1.5rem; color: #6c757d;"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ __( 'template.current_icon' ) }}</div>
                                    {{-- <small class="text-muted">{{ __( 'template.click_to_change' ) }}</small> --}}
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $amenity_edit }}_remove_current_image">
                                <i class="ni ni-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Upload Zone -->
                    <div class="image-upload-zone" id="{{ $amenity_edit }}_image_upload">
                        <div class="upload-content">
                            <i class="ni ni-upload" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="text-muted mb-0">{{ __( 'template.drag_drop_or_click' ) }}</p>
                            <small class="text-muted">{{ __( 'template.max_size_5mb' ) }}</small>
                        </div>
                        <input type="file" id="{{ $amenity_edit }}_image_input" class="d-none" accept=".png,.jpg,.jpeg,.ttf,.otf,.svg">
                    </div>

                    <!-- New Image Preview -->
                    <div id="{{ $amenity_edit }}_image_preview" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between border rounded p-2">
                            <div class="d-flex align-items-center">
                                <div id="{{ $amenity_edit }}_image_thumbnail_container" style="width: 50px; height: 50px;" class="rounded me-3 d-flex align-items-center justify-content-center border">
                                    <img id="{{ $amenity_edit }}_image_thumbnail" src="" alt="" style="max-width: 50px; max-height: 50px; object-fit: contain;" class="rounded">
                                    <div id="{{ $amenity_edit }}_file_icon" style="display: none;">
                                        <i class="ni ni-single-copy-04" style="font-size: 1.5rem; color: #6c757d;"></i>
                                    </div>
                                </div>
                                <div>
                                    <div id="{{ $amenity_edit }}_image_name" class="fw-bold"></div>
                                    <small id="{{ $amenity_edit }}_image_size" class="text-muted"></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $amenity_edit }}_remove_image">
                                <i class="ni ni-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button id="{{ $amenity_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $amenity_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    var uploadedFiles = [];

    document.addEventListener( 'DOMContentLoaded', function() {

        getAmenity();

        let ue = '#{{ $amenity_edit }}';
        let currentAmenity = null;
        let removeCurrentImage = false;
        
        // Image upload handling
        const imageUploadZone = document.getElementById('{{ $amenity_edit }}_image_upload');
        const imageInput = document.getElementById('{{ $amenity_edit }}_image_input');
        const imagePreview = document.getElementById('{{ $amenity_edit }}_image_preview');
        const imageThumbnail = document.getElementById('{{ $amenity_edit }}_image_thumbnail');
        const imageName = document.getElementById('{{ $amenity_edit }}_image_name');
        const imageSize = document.getElementById('{{ $amenity_edit }}_image_size');
        const removeImageBtn = document.getElementById('{{ $amenity_edit }}_remove_image');
        const fileIcon = document.getElementById('{{ $amenity_edit }}_file_icon');
        const thumbnailContainer = document.getElementById('{{ $amenity_edit }}_image_thumbnail_container');
        
        const currentImageDiv = document.getElementById('{{ $amenity_edit }}_current_image');
        const currentThumbnail = document.getElementById('{{ $amenity_edit }}_current_thumbnail');
        const currentFileIcon = document.getElementById('{{ $amenity_edit }}_current_file_icon');
        const currentThumbnailContainer = document.getElementById('{{ $amenity_edit }}_current_thumbnail_container');
        const removeCurrentImageBtn = document.getElementById('{{ $amenity_edit }}_remove_current_image');

        imageUploadZone.addEventListener('click', () => imageInput.click());
        imageUploadZone.addEventListener('dragover', handleDragOver);
        imageUploadZone.addEventListener('drop', handleDrop);
        imageInput.addEventListener('change', handleFileSelect);
        removeImageBtn.addEventListener('click', removeNewImage);
        removeCurrentImageBtn.addEventListener('click', removeCurrentImageHandler);

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
            
            const allowedExtensions = ['.png', '.svg', '.ttf', '.otf'];
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
                    currentImageDiv.style.display = 'none';
                    imageUploadZone.style.display = 'none';
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
            const imageTypes = ['.png', '.jpg', '.jpeg', '.svg'];
            
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
        }

        function removeNewImage() {
            uploadedFiles = [];
            imagePreview.style.display = 'none';
            
            // Reset thumbnail state
            imageThumbnail.src = '';
            imageThumbnail.style.display = 'none';
            imageThumbnail.style.backgroundColor = 'transparent';
            imageThumbnail.style.padding = '0';
            fileIcon.style.display = 'none';
            thumbnailContainer.style.backgroundColor = 'transparent';
            
            if (currentAmenity && currentAmenity.icon_path && !removeCurrentImage) {
                currentImageDiv.style.display = 'block';
            } else {
                imageUploadZone.style.display = 'block';
            }
            imageInput.value = '';
        }

        function removeCurrentImageHandler() {
            removeCurrentImage = true;
            currentImageDiv.style.display = 'none';
            imageUploadZone.style.display = 'block';
        }

        function displayCurrentImage(iconPath) {
            if (!iconPath) return;
            
            // Get file extension from path
            const fileExtension = iconPath.toLowerCase().substring(iconPath.lastIndexOf('.'));
            const imageTypes = ['.png', '.jpg', '.jpeg', '.svg'];
            
            if (imageTypes.includes(fileExtension)) {
                currentThumbnail.src = iconPath;
                currentThumbnail.style.display = 'block';
                currentFileIcon.style.display = 'none';
                currentThumbnailContainer.style.backgroundColor = 'transparent';
                
                // Special handling for SVG files
                if (fileExtension === '.svg') {
                    currentThumbnail.style.backgroundColor = '#f8f9fa';
                    currentThumbnail.style.padding = '5px';
                }
            } else {
                // Show file icon for non-image files (TTF, OTF)
                currentThumbnail.style.display = 'none';
                currentFileIcon.style.display = 'block';
                currentThumbnailContainer.style.backgroundColor = '#f8f9fa';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        $( ue + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.amenity.index' ) }}';
        } );

        $( ue + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'title', $( ue + '_title' ).val() );

            if (uploadedFiles.length > 0) {
                formData.append( 'icon', uploadedFiles.join(',') );
            } else if (removeCurrentImage) {
                // Send empty icon to remove current one
                formData.append( 'icon', '' );
            }
            
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.amenity.updateAmenity' ) }}',
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
                            $( ue + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );

        function getAmenity() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.amenity.oneAmenity' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    currentAmenity = response;

                    $( ue + '_title' ).val( response.title );

                    // Handle existing icon
                    if (response.icon_path) {
                        // Prepend the path for SVG and other icon files
                        const iconPath = response.icon_path.startsWith('http') || response.icon_path.startsWith('/')
                            ? response.icon_path
                            : '/storage/' + response.icon_path;
                        displayCurrentImage(iconPath);
                        currentImageDiv.style.display = 'block';
                        imageUploadZone.style.display = 'none';
                    } else {
                        imageUploadZone.style.display = 'block';
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
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
    
    /* SVG and file preview styling */
    #{{ $amenity_edit }}_image_thumbnail,
    #{{ $amenity_edit }}_current_thumbnail {
        border-radius: 0.25rem;
    }
</style>