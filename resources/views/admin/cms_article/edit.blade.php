<style>
/* CKEditor Content Styling - Match Frontend article_content styles */
.ck-editor__editable,
.ck-content {
    min-height: 300px;
    font-family: 'Montserrat' !important;
}

/* Headings - Match frontend sizes */
.ck-editor__editable h1,
.ck-content h1 {
    font-size: 21px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

.ck-editor__editable h2,
.ck-content h2 {
    font-size: 20px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

.ck-editor__editable h3,
.ck-content h3 {
    font-size: 19px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

.ck-editor__editable h4,
.ck-content h4 {
    font-size: 18px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

.ck-editor__editable h5,
.ck-content h5 {
    font-size: 17px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

.ck-editor__editable h6,
.ck-content h6 {
    font-size: 16px !important;
    line-height: 1.2 !important;
    font-weight: bold !important;
}

/* Block quotes - Match frontend style */
.ck-editor__editable blockquote,
.ck-content blockquote {
    line-height: 1.2 !important;
    font-style: italic !important;
    border-style: inherit !important;
    border-left: 5px solid #b27c44 !important;
    padding: 25px !important;
    margin-left: 0 !important;
}

/* Links - Match frontend style */
.ck-editor__editable a,
.ck-content a {
    border-bottom: 1px solid #b27c44 !important;
    color: #b27c44 !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

/* Unordered lists */
.ck-editor__editable ul,
.ck-content ul {
    list-style-type: disc !important;
    padding-left: 40px !important;
}

.ck-editor__editable ul li,
.ck-content ul li {
    list-style-type: disc !important;
    list-style-position: outside !important;
    display: list-item !important;
}

/* Nested unordered lists */
.ck-editor__editable ul ul li,
.ck-content ul ul li {
    list-style-type: circle !important;
}

.ck-editor__editable ul ul ul li,
.ck-content ul ul ul li {
    list-style-type: square !important;
}

/* Ordered lists */
.ck-editor__editable ol,
.ck-content ol {
    list-style-type: decimal !important;
    padding-left: 40px !important;
}

.ck-editor__editable ol li,
.ck-content ol li {
    list-style-position: outside !important;
    display: list-item !important;
}

/* Support different ordered list styles via inline style/type attribute */
.ck-editor__editable ol[style*="decimal"] li,
.ck-content ol[style*="decimal"] li {
    list-style-type: decimal !important;
}

.ck-editor__editable ol[style*="decimal-leading-zero"] li,
.ck-content ol[style*="decimal-leading-zero"] li {
    list-style-type: decimal-leading-zero !important;
}

.ck-editor__editable ol[style*="upper-latin"] li,
.ck-content ol[style*="upper-latin"] li {
    list-style-type: upper-latin !important;
}

.ck-editor__editable ol[style*="lower-latin"] li,
.ck-content ol[style*="lower-latin"] li {
    list-style-type: lower-latin !important;
}

.ck-editor__editable ol[style*="upper-roman"] li,
.ck-content ol[style*="upper-roman"] li {
    list-style-type: upper-roman !important;
}

.ck-editor__editable ol[style*="lower-roman"] li,
.ck-content ol[style*="lower-roman"] li {
    list-style-type: lower-roman !important;
}

/* Text formatting */
.ck-editor__editable strong,
.ck-content strong,
.ck-editor__editable b,
.ck-content b {
    font-weight: bold !important;
}

.ck-editor__editable em,
.ck-content em,
.ck-editor__editable i,
.ck-content i {
    font-style: italic !important;
}

.ck-editor__editable u,
.ck-content u {
    text-decoration: underline !important;
}

.ck-editor__editable s,
.ck-content s {
    text-decoration: line-through !important;
}

/* Tables */
.ck-editor__editable table,
.ck-content table {
    border-collapse: collapse !important;
    width: 100% !important;
}

.ck-editor__editable table td,
.ck-content table td,
.ck-editor__editable table th,
.ck-content table th {
    border: 1px solid #ddd !important;
    padding: 8px !important;
}

.ck-editor__editable table th,
.ck-content table th {
    background-color: #f2f2f2 !important;
    font-weight: bold !important;
}

/* Paragraphs */
.ck-editor__editable p,
.ck-content p {
    margin: 0 0 1em 0 !important;
}

.ck-editor__editable_inline {
    padding: 0 30px !important;
}

/* Responsive styles for smaller screens (below 1280px) */
@media (max-width: 1280px) {
    .ck-editor__editable h1,
    .ck-content h1 {
        font-size: 21px !important;
    }

    .ck-editor__editable h2,
    .ck-content h2 {
        font-size: 20px !important;
    }

    .ck-editor__editable h3,
    .ck-content h3 {
        font-size: 19px !important;
    }

    .ck-editor__editable h4,
    .ck-content h4 {
        font-size: 18px !important;
    }

    .ck-editor__editable h5,
    .ck-content h5 {
        font-size: 17px !important;
    }

    .ck-editor__editable h6,
    .ck-content h6 {
        font-size: 16px !important;
    }

    .ck-editor__editable blockquote,
    .ck-content blockquote {
        font-size: 14px !important;
        padding: 15px !important;
    }
}

    /* Image Upload Zone Styles */
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

    .image-preview {
        max-height: 600px;
        overflow-y: auto;
        padding: 1rem 0;
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    .image-item {
        cursor: move;
        transition: all 0.3s ease;
    }

    .image-item .card {
        transition: transform 0.2s;
        height: 100%;
    }

    .image-item:hover .card {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .image-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }

    .upload-progress {
        display: none;
        margin-top: 1rem;
    }

    .upload-progress.active {
        display: block;
    }

    /* Language Tabs */
    .nav-tabs .nav-link {
        cursor: pointer;
    }

    .nav-tabs .nav-link.active {
        font-weight: bold;
    }

    .language-tab-content {
        display: none;
    }

    .language-tab-content.active {
        display: block;
    }
</style>

<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Edit CMS Article</h3>
                        </div>
                        <div class="nk-block-head-content">
                            <a href="{{ route('admin.module_parent.cms_article.index') }}" class="btn btn-outline-light bg-white">
                                <em class="icon ni ni-arrow-left"></em><span>Back</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="nk-block">
                    <div class="card card-bordered">
                        <div class="card-inner">
                            <form id="cms_article_form" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="article_id" name="id" value="">

                                <!-- Thumbnail Upload -->
                                <div class="mb-3 row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="thumbnail">Thumbnail</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Upload article thumbnail image (leave empty to keep existing)</small>
                                        <div id="thumbnail_preview" class="mt-2"></div>
                                        <div id="existing_thumbnail" class="mt-2" style="display: none;">
                                            <label class="form-label">Current Thumbnail:</label>
                                            <div id="existing_thumbnail_image"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Publish Date -->
                                <div class="mb-3 row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="publish_date">Publish Date</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" id="publish_date" name="publish_date" value="{{ date('Y-m-d') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mb-3 row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="status">Status</label>
                                    </div>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="status" name="status">
                                            <option value="11">Draft</option>
                                            <option value="10">Published</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Language Tabs -->
                                <h4 class="mb-3">Article Content (Multi-Language)</h4>

                                <ul class="nav nav-tabs mb-3" id="languageTabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-lang="en">English</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-lang="zh_cn">简体中文</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-lang="ms">Bahasa Melayu</a>
                                    </li>
                                </ul>

                                <!-- Language Tab Contents -->
                                <div id="languageTabContents">
                                    <!-- English -->
                                    <div class="language-tab-content active" data-lang="en">
                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Title (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[en][title]" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Slug (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[en][slug]" required>
                                                <div class="invalid-feedback"></div>
                                                <small class="form-text text-muted">URL-friendly version (e.g., my-article-title)</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Short Description (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[en][short_description]" rows="3"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Description (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control ckeditor" name="translations[en][description]" data-lang="en"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Title (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[en][meta_title]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Description (EN)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[en][meta_description]" rows="2"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Chinese Simplified -->
                                    <div class="language-tab-content" data-lang="zh_cn">
                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Title (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[zh_cn][title]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Slug (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[zh_cn][slug]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Short Description (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[zh_cn][short_description]" rows="3"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Description (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control ckeditor" name="translations[zh_cn][description]" data-lang="zh_cn"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Title (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[zh_cn][meta_title]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Description (简中)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[zh_cn][meta_description]" rows="2"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Malay -->
                                    <div class="language-tab-content" data-lang="ms">
                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Title (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[ms][title]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Slug (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[ms][slug]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Short Description (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[ms][short_description]" rows="3"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Description (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control ckeditor" name="translations[ms][description]" data-lang="ms"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Title (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="translations[ms][meta_title]">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label">Meta Description (MS)</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="translations[ms][meta_description]" rows="2"></textarea>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Banner Gallery -->
                                <div class="mt-4 mb-4">
                                    <h4 class="mb-3">Article Banners</h4>

                                    <!-- Banner Upload Section -->
                                    <div class="mb-3 row">
                                        <div class="col-sm-12">
                                            <div class="image-upload-zone" id="banner_upload_zone">
                                                <input type="file" id="banner_images" name="banners[]" multiple accept="image/*" style="display: none;">
                                                <div class="upload-content">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                                        <polyline points="21,15 16,10 5,21"/>
                                                    </svg>
                                                    <p class="text-muted mb-1">Click to upload banners or drag and drop</p>
                                                    <small class="text-muted">Supports: JPG, PNG (Max 5MB each)</small>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                            <small class="form-text text-muted">Upload multiple banners. They will appear as a carousel slider on the frontend.</small><br>
                                            <small class="form-text text-muted">New Banner will be sorted to front.</small>

                                            <div class="upload-progress">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="upload-status text-muted">Uploading...</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Banner Stats -->
                                    <div class="mb-3 row">
                                        <div class="col-sm-12">
                                            <div class="gallery-stats">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>Total Banners:</strong>
                                                        <span id="total_banners_count">0</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-secondary" id="select_all_banners" disabled>
                                                                <i class="ni ni-check-circle"></i> Select All
                                                            </button>
                                                            <button type="button" class="btn btn-danger" id="delete_selected_banners" disabled>
                                                                <i class="ni ni-trash"></i> Delete Selected
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Banner Preview Section -->
                                    <div class="mb-3 row">
                                        <div class="col-sm-12">
                                            <div class="image-preview">
                                                <div id="banner_preview" class="row" data-sortable="true">
                                                    <!-- Dynamic banner previews will be inserted here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bulk Actions -->
                                    <div class="mb-3 row">
                                        <div class="col-sm-12">
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('banner_images').click()">
                                                    <i class="ni ni-plus"></i> Add More Banners
                                                </button>
                                                <button type="button" class="btn btn-outline-info" id="sort_banners_by_name">
                                                    <i class="ni ni-sort-alpha-up"></i> Sort by Name
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" id="reset_banner_order">
                                                    <i class="ni ni-reload"></i> Reset Order
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Submit Buttons -->
                                <div class="mb-3 row">
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary" id="submit_btn">
                                            <em class="icon ni ni-save"></em><span>Update Article</span>
                                        </button>
                                        <a href="{{ route('admin.module_parent.cms_article.index') }}" class="btn btn-outline-light">
                                            <span>Cancel</span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
<!-- Sortable.js for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// CKEditor configuration
window.ckeupload_path = '{{ route('admin.cms_article.ckeUpload') }}';
window.csrf_token = '{{ csrf_token() }}';
window.editorInstances = {};

// Custom Upload Adapter
class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('upload', file);

                fetch(window.ckeupload_path, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': window.csrf_token
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.url) {
                        resolve({
                            default: result.url
                        });
                    } else {
                        reject(result.error || 'Upload failed');
                    }
                })
                .catch(error => {
                    reject(error);
                });
            }));
    }

    abort() {
        // Handle abort
    }
}

// Upload Adapter Plugin
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Load article data
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('id');

    let loadedArticle = null;

    if (articleId) {
        // Fetch article data
        fetch('{{ route("admin.cms_article.oneProject") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: articleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data) {
                loadedArticle = data;
                populateForm(data);
            }
        })
        .catch(error => {
            console.error('Error loading article:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load article data',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route("admin.module_parent.cms_article.index") }}';
            });
        });
    }

    function populateForm(article) {
        // Populate basic fields
        document.getElementById('article_id').value = article.encrypted_id || '';
        document.getElementById('publish_date').value = article.publish_date
            ? new Date(article.publish_date).toISOString().split('T')[0]
            : '';
        document.getElementById('status').value = article.status || '0';

        // Show existing thumbnail
        if (article.thumbnail_path) {
            const existingThumb = document.getElementById('existing_thumbnail');
            const existingThumbImage = document.getElementById('existing_thumbnail_image');
            existingThumb.style.display = 'block';
            existingThumbImage.innerHTML = `
                <div class="card" style="max-width: 300px;">
                    <img src="${article.thumbnail_path}" class="card-img-top" alt="Current Thumbnail">
                </div>
            `;
        }

        // Populate translations
        if (article.translations) {
            // Handle translations as object with locale keys
            Object.keys(article.translations).forEach(locale => {
                const translation = article.translations[locale];

                // Populate text fields
                const titleInput = document.querySelector(`input[name="translations[${locale}][title]"]`);
                const slugInput = document.querySelector(`input[name="translations[${locale}][slug]"]`);
                const shortDescTextarea = document.querySelector(`textarea[name="translations[${locale}][short_description]"]`);
                const metaTitleInput = document.querySelector(`input[name="translations[${locale}][meta_title]"]`);
                const metaDescTextarea = document.querySelector(`textarea[name="translations[${locale}][meta_description]"]`);
                const metaKeywordsTextarea = document.querySelector(`textarea[name="translations[${locale}][meta_keywords]"]`);

                if (titleInput) titleInput.value = translation.title || '';
                if (slugInput) slugInput.value = translation.slug || '';
                if (shortDescTextarea) shortDescTextarea.value = translation.short_description || '';
                if (metaTitleInput) metaTitleInput.value = translation.meta_title || '';
                if (metaDescTextarea) metaDescTextarea.value = translation.meta_description || '';
                if (metaKeywordsTextarea) metaKeywordsTextarea.value = translation.meta_keywords || '';

                // Set CKEditor content after editors are initialized
                if (translation.description && window.editorInstances[locale]) {
                    window.editorInstances[locale].setData(translation.description);
                }
            });
        }

        // Load existing banners
        if (article.banners && article.banners.length > 0) {
            article.banners.forEach(banner => {
                window.bannerGallery.addExistingBanner(banner);
            });
        }
    }

    // Initialize CKEditor for all textareas
    const ckeditorTextareas = document.querySelectorAll('.ckeditor');
    ckeditorTextareas.forEach(textarea => {
        const lang = textarea.getAttribute('data-lang');

        CKEDITOR.ClassicEditor
            .create(textarea, {
                extraPlugins: [ MyCustomUploadAdapterPlugin ],
                removePlugins: [
                    'RealTimeCollaborativeEditing',
                    'RealTimeCollaborativeComments',
                    'RealTimeCollaborativeTrackChanges',
                    'RealTimeCollaborativeRevisionHistory',
                    'PresenceList',
                    'Comments',
                    'TrackChanges',
                    'TrackChangesData',
                    'RevisionHistory',
                    'Pagination',
                    'WProofreader',
                    'MathType',
                    'SlashCommand',
                    'Template',
                    'DocumentOutline',
                    'FormatPainter',
                    'TableOfContents',
                    'PasteFromOfficeEnhanced',
                    'CaseChange',
                    'AI',
                    'AICommands',
                    'AIAssistant',
                ],
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', '|',
                        'link', '|',
                        'bulletedList', 'numberedList', 'todoList',
                        'fontsize', 'fontColor', 'fontBackgroundColor', '|',
                        'alignment', '|',
                        'outdent', 'indent', '|',
                        'uploadImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', '|',
                        'undo', 'redo', '|',
                        'sourceEditing'
                    ],
                    shouldNotGroupWhenFull: true
                },
                list: {
                    properties: {
                        styles: true,
                        startIndex: true,
                        reversed: true
                    }
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                        { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                        { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                        { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                    ]
                },
                fontFamily: {
                    supportAllValues: true
                },
                fontSize: {
                    options: [10, 12, 14, 'default', 18, 20, 22],
                    supportAllValues: true
                },
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ]
                },
                image: {
                    toolbar: [
                        'imageTextAlternative', 'toggleImageCaption', 'imageStyle:inline',
                        'imageStyle:block', 'imageStyle:side', 'linkImage'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells',
                        'tableCellProperties', 'tableProperties'
                    ]
                },
            })
            .then(editor => {
                window.editorInstances[lang] = editor;

                // If article is already loaded, populate editor content
                if (loadedArticle && loadedArticle.translations) {
                    const translation = loadedArticle.translations.find(t => t.locale === lang);
                    if (translation && translation.description) {
                        editor.setData(translation.description);
                    }
                }
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    });

    // Language Tab Switching
    const languageTabs = document.querySelectorAll('#languageTabs .nav-link');
    const languageContents = document.querySelectorAll('.language-tab-content');

    languageTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');

            // Remove active class from all tabs and contents
            languageTabs.forEach(t => t.classList.remove('active'));
            languageContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.querySelector(`.language-tab-content[data-lang="${lang}"]`).classList.add('active');
        });
    });

    // Thumbnail Preview
    const thumbnailInput = document.getElementById('thumbnail');
    const thumbnailPreview = document.getElementById('thumbnail_preview');

    thumbnailInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                thumbnailPreview.innerHTML = `
                    <div class="card" style="max-width: 300px;">
                        <img src="${event.target.result}" class="card-img-top" alt="Thumbnail Preview">
                        <div class="card-body">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    // Banner Gallery Manager
    class BannerGalleryManager {
        constructor() {
            this.banners = [];
            this.bannerCounter = 0;
            this.initializeElements();
            this.attachEventListeners();
            this.initializeSortable();
        }

        initializeElements() {
            this.fileInput = document.getElementById('banner_images');
            this.uploadZone = document.getElementById('banner_upload_zone');
            this.previewContainer = document.getElementById('banner_preview');
            this.totalCount = document.getElementById('total_banners_count');
            this.selectAllBtn = document.getElementById('select_all_banners');
            this.deleteSelectedBtn = document.getElementById('delete_selected_banners');
            this.sortByNameBtn = document.getElementById('sort_banners_by_name');
            this.resetOrderBtn = document.getElementById('reset_banner_order');
        }

        attachEventListeners() {
            if (this.fileInput) {
                this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
            }

            if (this.uploadZone) {
                this.uploadZone.addEventListener('click', () => this.fileInput.click());
                this.uploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    this.uploadZone.classList.add('dragover');
                });
                this.uploadZone.addEventListener('dragleave', () => {
                    this.uploadZone.classList.remove('dragover');
                });
                this.uploadZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    this.uploadZone.classList.remove('dragover');
                    this.handleFiles(e.dataTransfer.files);
                });
            }

            if (this.selectAllBtn) {
                this.selectAllBtn.addEventListener('click', () => this.selectAll());
            }

            if (this.deleteSelectedBtn) {
                this.deleteSelectedBtn.addEventListener('click', () => this.deleteSelected());
            }

            if (this.sortByNameBtn) {
                this.sortByNameBtn.addEventListener('click', () => this.sortByName());
            }

            if (this.resetOrderBtn) {
                this.resetOrderBtn.addEventListener('click', () => this.resetOrder());
            }
        }

        handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    this.addBanner(file);
                }
            });
        }

        addBanner(file) {
            const id = this.bannerCounter++;
            const banner = {
                id: id,
                file: file,
                name: file.name,
                selected: false
            };

            this.banners.push(banner);

            const reader = new FileReader();
            reader.onload = (e) => {
                this.renderBanner(banner, e.target.result);
                this.updateStats();
            };
            reader.readAsDataURL(file);
        }

        renderBanner(banner, dataUrl) {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-6 mb-3 image-item';
            col.dataset.bannerId = banner.id;

            // Add existing-banner class and data attribute for existing banners
            if (banner.isExisting) {
                col.classList.add('existing-banner');
                col.dataset.existingBannerId = banner.existingId;
            }

            col.innerHTML = `
                <div class="card">
                    <div class="image-controls">
                        <div class="form-check">
                            <input class="form-check-input banner-checkbox" type="checkbox" data-banner-id="${banner.id}">
                        </div>
                    </div>
                    <img src="${dataUrl}" class="card-img-top" alt="${banner.name}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">${banner.name}</small>
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Alt text" data-banner-id="${banner.id}" name="banner_alt_text[${banner.id}]" value="${banner.altText || ''}">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Target Page" data-banner-id="${banner.id}" name="banner_target_page[${banner.id}]" value="${banner.targetPage || ''}">
                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Target Page Web" data-banner-id="${banner.id}" name="banner_target_page_web[${banner.id}]" value="${banner.targetPageWeb || ''}">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-md btn-danger delete-banner" data-banner-id="${banner.id}">
                                <i class="ni ni-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            this.previewContainer.appendChild(col);

            // Attach events
            col.querySelector('.banner-checkbox').addEventListener('change', (e) => {
                banner.selected = e.target.checked;
                this.updateStats();
            });

            col.querySelector('.delete-banner').addEventListener('click', () => {
                this.deleteBanner(banner.id);
            });
        }

        deleteBanner(id) {
            const index = this.banners.findIndex(b => b.id === id);
            if (index > -1) {
                this.banners.splice(index, 1);
                const element = this.previewContainer.querySelector(`[data-banner-id="${id}"]`);
                if (element) {
                    element.remove();
                }
                this.updateStats();
            }
        }

        selectAll() {
            const checkboxes = this.previewContainer.querySelectorAll('.banner-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                const id = parseInt(cb.dataset.bannerId);
                const banner = this.banners.find(b => b.id === id);
                if (banner) {
                    banner.selected = !allChecked;
                }
            });

            this.updateStats();
        }

        deleteSelected() {
            const selectedIds = this.banners.filter(b => b.selected).map(b => b.id);
            selectedIds.forEach(id => this.deleteBanner(id));
        }

        sortByName() {
            this.banners.sort((a, b) => a.name.localeCompare(b.name));
            this.rerenderBanners();
        }

        resetOrder() {
            this.banners.sort((a, b) => a.id - b.id);
            this.rerenderBanners();
        }

        rerenderBanners() {
            this.previewContainer.innerHTML = '';
            this.banners.forEach(banner => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.renderBanner(banner, e.target.result);
                };
                reader.readAsDataURL(banner.file);
            });
        }

        updateStats() {
            this.totalCount.textContent = this.banners.length;
            const selectedCount = this.banners.filter(b => b.selected).length;

            this.selectAllBtn.disabled = this.banners.length === 0;
            this.deleteSelectedBtn.disabled = selectedCount === 0;
        }

        initializeSortable() {
            if (this.previewContainer) {
                new Sortable(this.previewContainer, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: () => {
                        this.updateBannersFromDOM();
                    }
                });
            }
        }

        updateBannersFromDOM() {
            const elements = this.previewContainer.querySelectorAll('.image-item');
            const newOrder = [];

            elements.forEach(element => {
                const id = parseInt(element.dataset.bannerId);
                const banner = this.banners.find(b => b.id === id);
                if (banner) {
                    newOrder.push(banner);
                }
            });

            this.banners = newOrder;
        }

        addExistingBanner(bannerData) {
            const id = this.bannerCounter++;
            const banner = {
                id: id,
                existingId: bannerData.id,
                name: bannerData.image || 'existing-banner.jpg',
                selected: false,
                isExisting: true,
                altText: bannerData.alt_text || '',
                targetPage: bannerData.target_page || '',
                targetPageWeb: bannerData.target_page_web || '',
                sortOrder: bannerData.sort_order || id
            };

            this.banners.push(banner);

            // Render existing banner
            const imageUrl = bannerData.image_path || bannerData.image;
            this.renderBanner(banner, imageUrl);
            this.updateStats();
        }

        getBannersData() {
            return this.banners;
        }
    }

    // Initialize Banner Gallery Manager
    window.bannerGallery = new BannerGalleryManager();

    // Form Submission
    $('#cms_article_form').on('submit', function(e) {
        e.preventDefault();

        // Clear previous validation errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $('body').loading();

        const formData = new FormData();

        // Article ID
        const articleId = $('#article_id').val();
        if (articleId) {
            formData.append('id', articleId);
        }

        // Basic fields
        const thumbnail = document.getElementById('thumbnail').files[0];
        if (thumbnail) {
            formData.append('thumbnail', thumbnail);
        }

        formData.append('publish_date', $('#publish_date').val());
        formData.append('status', $('#status').val());

        // Translations
        const languages = ['en', 'zh_cn', 'ms'];
        languages.forEach(lang => {
            const title = $(`input[name="translations[${lang}][title]"]`).val();
            const slug = $(`input[name="translations[${lang}][slug]"]`).val();
            const shortDesc = $(`textarea[name="translations[${lang}][short_description]"]`).val();
            const metaTitle = $(`input[name="translations[${lang}][meta_title]"]`).val();
            const metaDesc = $(`textarea[name="translations[${lang}][meta_description]"]`).val();
            const metaKeywords = $(`textarea[name="translations[${lang}][meta_keywords]"]`).val();

            if (title) formData.append(`title_${lang}`, title);
            if (slug) formData.append(`slug_${lang}`, slug);
            if (shortDesc) formData.append(`short_description_${lang}`, shortDesc);
            if (metaTitle) formData.append(`meta_title_${lang}`, metaTitle);
            if (metaDesc) formData.append(`meta_description_${lang}`, metaDesc);
            if (metaKeywords) formData.append(`meta_keywords_${lang}`, metaKeywords);

            // Get CKEditor content
            if (window.editorInstances[lang]) {
                const content = window.editorInstances[lang].getData();
                if (content) {
                    formData.append(`description_${lang}`, content);
                }
            }
        });

        // Collect banners in DOM order (respects drag & drop)
        const allBannerElements = document.querySelectorAll('.image-item');
        const existingBannerIds = [];
        const bannerAltTexts = [];
        const bannerTargetPages = [];
        const bannerTargetPagesWeb = [];
        let newBannerIndex = 0;

        allBannerElements.forEach((bannerElement, index) => {
            const bannerId = bannerElement.dataset.bannerId;
            const isExisting = bannerElement.classList.contains('existing-banner');

            if (isExisting) {
                // Collect existing banner database ID
                const existingBannerId = bannerElement.dataset.existingBannerId;
                if (existingBannerId) {
                    existingBannerIds.push(existingBannerId);
                }
            } else {
                // Find the new banner in bannersData and upload it
                const banner = window.bannerGallery.banners.find(b => b.id == bannerId);
                if (banner && banner.file && banner.file instanceof File) {
                    formData.append(`banner_images[${newBannerIndex}]`, banner.file);
                    newBannerIndex++;
                }
            }

            // Collect metadata for all banners (existing + new) in order
            const altTextInput = bannerElement.querySelector(`input[name^="banner_alt_text"]`);
            const targetPageInput = bannerElement.querySelector(`input[name^="banner_target_page"]:not([name*="web"])`);
            const targetPageWebInput = bannerElement.querySelector(`input[name^="banner_target_page_web"]`);

            bannerAltTexts[index] = altTextInput ? altTextInput.value : '';
            bannerTargetPages[index] = targetPageInput ? targetPageInput.value : '';
            bannerTargetPagesWeb[index] = targetPageWebInput ? targetPageWebInput.value : '';
        });

        // Append existing banner IDs in order
        existingBannerIds.forEach((id, index) => {
            formData.append(`existing_banner_ids[${index}]`, id);
        });

        // Append banner metadata as arrays
        bannerAltTexts.forEach((text, index) => {
            formData.append(`banner_alt_texts[${index}]`, text);
        });
        bannerTargetPages.forEach((page, index) => {
            formData.append(`banner_target_pages[${index}]`, page);
        });
        bannerTargetPagesWeb.forEach((page, index) => {
            formData.append(`banner_target_pages_web[${index}]`, page);
        });

        $.ajax({
            url: '{{ route("admin.cms_article.updateProject") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('body').loading('stop');
                $('#modal_success .caption-text').html(response.message);
                modalSuccess.toggle();

                document.getElementById('modal_success').addEventListener('hidden.bs.modal', function (event) {
                    window.location.href = '{{ route("admin.module_parent.cms_article.index") }}';
                });
            },
            error: function(xhr) {
                $('body').loading('stop');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let bannerErrors = [];

                    $.each(errors, function(key, value) {
                        // Check if it's a banner image error
                        if (key.startsWith('banner_images.')) {
                            const bannerIndex = key.match(/banner_images\.(\d+)/)[1];
                            bannerErrors.push(`Banner ${parseInt(bannerIndex) + 1}: ${Array.isArray(value) ? value[0] : value}`);
                        } else {
                            // Handle regular field errors
                            const fieldName = key.replace(/_/g, '_');
                            const field = $('#' + fieldName + ', [name="' + key + '"]').first();

                            if (field.length) {
                                field.addClass('is-invalid');
                                const feedback = field.next('.invalid-feedback');
                                if (feedback.length) {
                                    feedback.text(Array.isArray(value) ? value[0] : value);
                                }
                            }
                        }
                    });

                    // Show banner errors in an alert
                    if (bannerErrors.length > 0) {
                        $('#modal_danger .caption-text').html(
                            '<strong>Banner Upload Errors:</strong><br>' +
                            bannerErrors.join('<br>')
                        );
                        modalDanger.toggle();
                    }

                    $('.form-control.is-invalid:first').get(0)?.scrollIntoView({ block: 'center' });
                } else {
                    $('#modal_danger .caption-text').html(xhr.responseJSON?.message || 'Failed to update CMS article');
                    modalDanger.toggle();
                }
            }
        });
    });
});
</script>
