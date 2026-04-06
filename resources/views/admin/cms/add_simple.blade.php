    class ProjectGallery {
        constructor() {
            this.projectId = document.querySelector('[data-project-id]')?.value ||
                            new URLSearchParams(window.location.search).get('id') || '';
            this.existingImages = [];
            this.newImages = [];
            this.deletedImages = [];
            this.imageOrder = [];
            this.sortableInstance = null;
            this.init();
        }

        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
            } else {
                this.initializeComponents();
            }
        }

        initializeComponents() {
            this.bindEvents();
            this.initDragDrop();
            this.loadExistingGallery();
        }

        bindEvents() {
            const fileInput = document.getElementById('project_images');
            const uploadZone = document.querySelector('.image-upload-zone') || document.getElementById('gallery_upload_zone');
            const selectAllBtn = document.getElementById('select_all_images');
            const deleteSelectedBtn = document.getElementById('delete_selected');

            if (fileInput) {
                fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
            } else {
                console.warn('File input element not found');
            }

            if (uploadZone) {
                uploadZone.addEventListener('click', () => {
                    if (fileInput) fileInput.click();
                });

                uploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadZone.classList.add('dragover');
                });

                uploadZone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    uploadZone.classList.remove('dragover');
                });

                uploadZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadZone.classList.remove('dragover');
                    this.handleFiles(e.dataTransfer.files);
                });
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => this.selectAllImages());
            }

            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', () => this.deleteSelectedImages());
            }
        }

        initDragDrop() {
            const container = document.getElementById('images_preview');
            if (container && typeof Sortable !== 'undefined') {
                this.sortableInstance = Sortable.create(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: (evt) => {
                        this.updateImageOrder();
                    }
                });
            }
        }

        loadExistingGallery() {
            if (!this.projectId) {
                console.log('No project ID found, skipping gallery load');
                return;
            }

            if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
                console.error('jQuery is required for AJAX calls');
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value;

            $.ajax({
                url: '{{ route( 'admin.property.getGallery' ) }}',
                type: 'POST',
                data: {
                    project_id: this.projectId,
                    _token: csrfToken
                },
                success: (response) => {
                    let galleries = [];

                    if (Array.isArray(response)) {
                        galleries = response;
                    } else if (response.success && response.galleries) {
                        galleries = response.galleries;
                    } else if (response.data) {
                        galleries = response.data;
                    }

                    if (galleries && galleries.length > 0) {
                        this.existingImages = galleries.map(gallery => ({
                            id: gallery.id,
                            image_url: gallery.image_url || gallery.image_path,
                            image_path: gallery.image_path,
                            remarks: gallery.remarks || '',
                            sequence: gallery.sequence || 0,
                            is_new: false
                        }));

                        this.existingImages.sort((a, b) => a.sequence - b.sequence);

                        console.log('Loaded existing images:', this.existingImages);
                        this.renderAllImages();
                        this.updateStats();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Failed to load existing gallery:', error);
                }
            });
        }

        handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    this.addNewImage(file);
                } else {
                    console.warn('Skipping non-image file:', file.name);
                }
            });
        }

        addNewImage(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const imageId = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const imageObj = {
                    id: imageId,
                    file: file,
                    url: e.target.result,
                    name: file.name,
                    remarks: '',
                    sequence: this.existingImages.length + this.newImages.length + 1,
                    is_new: true
                };

                this.newImages.push(imageObj);
                this.renderAllImages();
                this.updateStats();
            };

            reader.readAsDataURL(file);
        }

        renderAllImages() {
            const container = document.getElementById('images_preview');
            if (!container) return;

            container.innerHTML = '';

            const activeExistingImages = this.existingImages.filter(img => !this.deletedImages.includes(img.id));
            const allImages = [...activeExistingImages, ...this.newImages];

            allImages.sort((a, b) => {
                const seqA = a.sequence || 999;
                const seqB = b.sequence || 999;
                return seqA - seqB;
            });

            allImages.forEach(image => this.renderImageItem(image, container));
        }

        renderImageItem(image, container) {
            const imageUrl = image.image_url || image.image_path || image.url;

            const div = document.createElement('div');
            div.className = 'col-lg-3 col-md-4 col-sm-6 mb-3';
            div.dataset.imageId = image.id;
            div.dataset.sequence = image.sequence || 0;

            const isNew = image.is_new || false;
            const statusBadge = isNew ? '<span class="badge bg-info">New</span>' : '<span class="badge bg-success">Existing</span>';

            if (!imageUrl) {
                console.warn('No image URL available for image:', image);
                return;
            }

            div.innerHTML = `
                <div class="card h-100">
                    <div class="image-controls">
                        <input type="checkbox" class="form-check-input image-selector" value="${image.id}">
                    </div>
                    <div class="image-wrapper">
                        <img src="${imageUrl}" class="card-img-top" alt="Gallery Image"
                             style="height: 200px; object-fit: cover;">
                    </div>
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            ${statusBadge}
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-danger delete-single"
                                        data-image-id="${image.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Remarks:</label>
                            <textarea class="form-control form-control-sm image-remarks"
                                    rows="2" data-image-id="${image.id}">${image.remarks || ''}</textarea>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(div);

            const item = container.lastElementChild;

            // Delete button
            item.querySelector('.delete-single').addEventListener('click', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                this.deleteImage(imageId);
            });

            // Remarks textarea
            item.querySelector('.image-remarks').addEventListener('blur', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                const remarks = e.currentTarget.value;
                this.updateImageRemarks(imageId, remarks);
            });

            // Checkbox
            item.querySelector('.image-selector').addEventListener('change', () => {
                this.updateBulkActionButtons();
            });
        }

        updateImageRemarks(imageId, remarks) {
            const existingImage = this.existingImages.find(img => img.id === imageId);
            if (existingImage) {
                existingImage.remarks = remarks;
                return;
            }

            const newImage = this.newImages.find(img => img.id === imageId);
            if (newImage) {
                newImage.remarks = remarks;
            }
        }

        deleteImage(imageId) {
            const existingImageIndex = this.existingImages.findIndex(img => img.id === imageId);
            if (existingImageIndex !== -1) {
                this.deletedImages.push(imageId);
            } else {
                this.newImages = this.newImages.filter(img => img.id !== imageId);
            }

            this.renderAllImages();
            this.updateStats();
        }

        selectAllImages() {
            const checkboxes = document.querySelectorAll('.image-selector');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });

            this.updateBulkActionButtons();
        }

        deleteSelectedImages() {
            const selectedCheckboxes = document.querySelectorAll('.image-selector:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                this.showNotification('Please select images to delete', 'warning');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selectedIds.length} image(s)?`)) {
                selectedIds.forEach(imageId => {
                    this.deleteImage(imageId);
                });
                this.updateBulkActionButtons();
            }
        }

        updateImageOrder() {
            const items = document.querySelectorAll('#images_preview > div');
            this.imageOrder = Array.from(items).map((item, index) => ({
                id: item.dataset.imageId,
                sequence: index + 1
            }));

            this.imageOrder.forEach(order => {
                const existingImage = this.existingImages.find(img => img.id === order.id);
                if (existingImage) {
                    existingImage.sequence = order.sequence;
                }

                const newImage = this.newImages.find(img => img.id === order.id);
                if (newImage) {
                    newImage.sequence = order.sequence;
                }
            });
        }

        updateBulkActionButtons() {
            const selectedCheckboxes = document.querySelectorAll('.image-selector:checked');
            const hasSelection = selectedCheckboxes.length > 0;

            const deleteBtn = document.getElementById('delete_selected');
            if (deleteBtn) {
                deleteBtn.disabled = !hasSelection;
            }
        }

        updateStats() {
            const totalImages = this.existingImages.filter(img => !this.deletedImages.includes(img.id)).length + this.newImages.length;

            const totalImagesElement = document.getElementById('total_images_count');

            if (totalImagesElement) totalImagesElement.textContent = totalImages;

            const selectAllBtn = document.getElementById('select_all_images');
            const deleteSelectedBtn = document.getElementById('delete_selected');

            const hasImages = totalImages > 0;
            if (selectAllBtn) selectAllBtn.disabled = !hasImages;
            if (deleteSelectedBtn) deleteSelectedBtn.disabled = !hasImages;
        }

        getGalleryData() {
            return {
                existing_images: this.existingImages.filter(img => !this.deletedImages.includes(img.id))
                    .map(img => ({
                        id: img.id,
                        remarks: img.remarks,
                        sequence: img.sequence
                    })),
                new_images: this.newImages.map((img, index) => ({
                    file: img.file,
                    remarks: img.remarks,
                    sequence: img.sequence || (this.existingImages.length + index + 1)
                })),
                deleted_images: this.deletedImages,
                image_order: this.imageOrder
            };
        }

        showNotification(message, type = 'info') {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }