<style>
    .ck-content ul {
      list-style-type: disc;
      margin-left: 20px;
    }
    
    /* Style for numbered lists inside CKEditor */
    .ck-content ol {
      list-style-type: decimal;
      margin-left: 20px;
    }
    
    /* Ensure list items have correct display inside CKEditor */
    .ck-content ul li, 
    .ck-content ol li {
      display: list-item;
    }
    
    /* Apply a minimum height to the CKEditor editable area */
    .ck-editor__editable_inline {
      min-height: 400px;
    }

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
    
    /* Dual Range Slider Styles */
    .dual-range-slider {
        position: relative;
        height: 24px;
        margin: 20px 0;
        background: #e9ecef;
        border-radius: 12px;
    }
    
    .dual-range-slider input[type="range"] {
        position: absolute;
        width: 100%;
        height: 24px;
        top: 0;
        left: 0;
        background: transparent;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        pointer-events: none;
        outline: none;
    }
    
    .dual-range-slider input[type="range"]::-webkit-slider-track {
        width: 100%;
        height: 6px;
        background: transparent;
        border-radius: 3px;
    }
    
    .dual-range-slider input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        pointer-events: all;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #fff;
        background-color: #d9ae80;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
    }
    
    .dual-range-slider input[type="range"]::-moz-range-track {
        width: 100%;
        height: 6px;
        background: transparent;
        border-radius: 3px;
        border: none;
    }
    
    .dual-range-slider input[type="range"]::-moz-range-thumb {
        pointer-events: all;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #fff;
        background-color: #d9ae80;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        -moz-appearance: none;
    }
    
    .dual-range-slider input[type="range"]:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 3px rgba(253, 209, 13, 0.25);
    }
    
    .dual-range-slider input[type="range"]:focus::-moz-range-thumb {
        box-shadow: 0 0 0 3px rgba(253, 209, 13, 0.25);
    }
    
    /* Different colors for min and max sliders */
    .dual-range-slider .range-min::-webkit-slider-thumb {
        background-color: #198754;
    }
    
    .dual-range-slider .range-min::-moz-range-thumb {
        background-color: #198754;
    }
    
    .dual-range-slider .range-max::-webkit-slider-thumb {
        background-color: #dc3545;
    }
    
    .dual-range-slider .range-max::-moz-range-thumb {
        background-color: #dc3545;
    }
    
    /* Track fill effect */
    .dual-range-slider::before{
        content: '';
        position: absolute;
        top: 9px;
        left: 0;
        right: 0;
        height: 6px;
        background: #dee2e6;
        border-radius: 3px;
        z-index: 1;
    }

    .dual-range-slider input[type="range"]::-webkit-slider-runnable-track {
        background: transparent; /* fully transparent */
        border: none;
    }

    .dual-range-slider input[type="range"]::-moz-range-track {
        background: transparent; /* fully transparent */
        border: none;
    }

    .dual-range-slider input[type="range"]::-ms-track {
        background: transparent; /* fully transparent */
        border: none;
        color: transparent; /* hide ticks */
    }

    .dual-range-slider::after {
        content: '';
        position: absolute;
        top: 9px;
        height: 6px;
        background: #198754; /* active color */
        border-radius: 3px;
        z-index: 1;
    }
</style>

<?php
$project_create = 'project_create';
$tenureTypes = $data['tenure_type'];
$propertyStatusTypes = $data['property_status_type'];
$propertyTypes = $data['property_type'];
$furnishingStatusTypes = $data['furnishing_status_type'];
$buildingTypes = $data['building_type'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.projects' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-6 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3">
                    <label>{{ __( 'project.logo' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $project_create }}_logo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <!-- Multi-Language Content -->
                <div class="mb-4">
                    <label class="form-label">{{ __( 'template.content_translations' ) }}</label>
                    <ul class="nav nav-tabs mb-3" role="tablist" id="language-tabs">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lang-en-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-en" type="button" role="tab">
                                English
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lang-zh_tw-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-zh_tw" type="button" role="tab">
                                繁体中文
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lang-id-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-id" type="button" role="tab">
                                Bahasa Indonesia
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lang-zh_cn-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-zh_cn" type="button" role="tab">
                                简体中文
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lang-ja-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-ja" type="button" role="tab">
                                日本語
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="lang-ko-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lang-ko" type="button" role="tab">
                                한국어
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="language-content">
                        <!-- English Content -->
                        <div class="tab-pane fade show active" id="lang-en" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (English)</label>
                                <input type="text" class="form-control" id="title_en">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (English)</label>
                                <input type="text" class="form-control" id="short_description_en">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (English)</label>
                                <textarea class="form-control" id="description_en" name="description_en"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Traditional Chinese Content -->
                        <div class="tab-pane fade" id="lang-zh_tw" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (繁体中文)</label>
                                <input type="text" class="form-control" id="title_zh_tw">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (繁体中文)</label>
                                <input type="text" class="form-control" id="short_description_zh_tw">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (繁体中文)</label>
                                <textarea class="form-control" id="description_zh_tw" name="description_zh_tw"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Indonesian Content -->
                        <div class="tab-pane fade" id="lang-id" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (Bahasa Indonesia)</label>
                                <input type="text" class="form-control" id="title_id">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (Bahasa Indonesia)</label>
                                <input type="text" class="form-control" id="short_description_id">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (Bahasa Indonesia)</label>
                                <textarea class="form-control" id="description_id" name="description_id"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Simplified Chinese Content -->
                        <div class="tab-pane fade" id="lang-zh_cn" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (简体中文)</label>
                                <input type="text" class="form-control" id="title_zh_cn">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (简体中文)</label>
                                <input type="text" class="form-control" id="short_description_zh_cn">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (简体中文)</label>
                                <textarea class="form-control" id="description_zh_cn" name="description_zh_cn"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Japanese Content -->
                        <div class="tab-pane fade" id="lang-ja" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (日本語)</label>
                                <input type="text" class="form-control" id="title_ja">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (日本語)</label>
                                <input type="text" class="form-control" id="short_description_ja">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (日本語)</label>
                                <textarea class="form-control" id="description_ja" name="description_ja"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Korean Content -->
                        <div class="tab-pane fade" id="lang-ko" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.title' ) }} (한국어)</label>
                                <input type="text" class="form-control" id="title_ko">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.short_description' ) }} (한국어)</label>
                                <input type="text" class="form-control" id="short_description_ko">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.description' ) }} (한국어)</label>
                                <textarea class="form-control" id="description_ko" name="description_ko"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_developers" class="col-sm-5 col-form-label">{{ __( 'template.developers' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_developers" multiple data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.developers' ) ] ) }}">
                            @forEach( $data['developers'] as $key => $developer )
                                <option value="{{ $key }}">{{ $developer }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_country" class="col-sm-5 col-form-label">{{ __( 'property.country' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_country" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'property.country' ) ] ) }}">
                            @forEach( $data['countries'] as $key => $country )
                                <option value="{{ $key }}">{{ $country }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_project_status" class="col-sm-5 col-form-label">{{ __( 'project.status' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_project_status">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'project.status' ) ] ) }}</option>
                            <option value="1">Completed</option>
                            <option value="2">Under Construction</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_amenities" class="col-sm-5 col-form-label">{{ __( 'property.amenities' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_amenities" multiple data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'property.amenities' ) ] ) }}">
                            @forEach( $data['amenities'] as $key => $amenity )
                                <option value="{{ intval( $key ) }}">{{ $amenity }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_property_type" class="col-sm-5 col-form-label">{{ __( 'project.property_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_property_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'project.property_type' ) ] ) }}</option>
                            <option value="1">Residential</option>
                            <option value="2">Commercial</option>
                            <option value="3">Industrial</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_tenure" class="col-sm-5 col-form-label">{{ __( 'property.tenure' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_tenure">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'property.tenure' ) ] ) }}</option>
                            <option value="freehold">Freehold</option>
                            <option value="leasehold">Leasehold</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <h5 class="card-title mb-4">{{ __( 'property.location' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $project_create }}_address_line_1" class="col-sm-5 col-form-label">{{ __( 'property.address_line_1' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_address_line_1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_address_line_2" class="col-sm-5 col-form-label">{{ __( 'property.address_line_2' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_address_line_2">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_address_line_3" class="col-sm-5 col-form-label">{{ __( 'property.address_line_3' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_address_line_3">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row" >
                    <label for="{{ $project_create }}_state" class="col-sm-5 col-form-label">{{ __( 'property.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_state">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'property.state' ) ] ) }}</option>
                            @php
                                $malaysianStates = [
                                    'Johor',
                                    'Kedah',
                                    'Kelantan',
                                    'Melaka',
                                    'Negeri Sembilan',
                                    'Pahang',
                                    'Penang',
                                    'Perak',
                                    'Perlis',
                                    'Sabah',
                                    'Sarawak',
                                    'Selangor',
                                    'Terengganu',
                                    'Kuala Lumpur',
                                    'Labuan',
                                    'Putrajaya'
                                ];
                            @endphp
                            @foreach($malaysianStates as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_project_locations" class="col-sm-5 col-form-label">{{ __( 'template.project_locations' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_project_locations" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.property_locations' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create}}_postcode" class="col-sm-5 col-form-label">{{ __( 'property.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create}}_postcode" >
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create}}_longitude" class="col-sm-5 col-form-label">{{ __( 'property.longitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create}}_longitude" >
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create}}_latitude" class="col-sm-5 col-form-label">{{ __( 'property.latitude' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create}}_latitude" >
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'project.project_details' ) }}</h5>

                <div class="mb-3 row">
                    <label class="col-sm-5 col-form-label">{{ __( 'property.bedrooms' ) }} Range</label>
                    <div class="col-sm-7">
                        <div class="mb-2">
                            <label class="form-label small">Bedrooms: <span id="bedrooms_range_display">0 - 5</span></label>
                            <div class="dual-range-slider">
                                <input type="range" class="form-range range-min" id="{{ $project_create }}_min_bedrooms" min="0" max="10" value="0" step="1">
                                <input type="range" class="form-range range-max" id="{{ $project_create }}_max_bedrooms" min="0" max="10" value="5" step="1">
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_bedroom_text" class="col-sm-5 col-form-label">{{ __( 'property.bedrooms' ) }} (Text)</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_bedroom_text" placeholder="e.g., Studio, 1-2, 2+1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-5 col-form-label">{{ __( 'property.bathrooms' ) }} Range</label>
                    <div class="col-sm-7">
                        <div class="mb-2">
                            <label class="form-label small">Bathrooms: <span id="bathrooms_range_display">1 - 3</span></label>
                            <div class="dual-range-slider">
                                <input type="range" class="form-range range-min" id="{{ $project_create }}_min_bathrooms" min="1" max="10" value="1" step="1">
                                <input type="range" class="form-range range-max" id="{{ $project_create }}_max_bathrooms" min="1" max="10" value="3" step="1">
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_bathroom_text" class="col-sm-5 col-form-label">{{ __( 'property.bathrooms' ) }} (Text)</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_bathroom_text" placeholder="e.g., 1-2, 2+1, Multiple">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-5 col-form-label">{{ __( 'project.min_carpark' ) }} Range</label>
                    <div class="col-sm-7">
                        <div class="mb-2">
                            <label class="form-label small">Carpark: <span id="carpark_range_display">0 - 3</span></label>
                            <div class="dual-range-slider">
                                <input type="range" class="form-range range-min" id="{{ $project_create }}_min_carpark" min="0" max="10" value="0" step="1">
                                <input type="range" class="form-range range-max" id="{{ $project_create }}_max_carpark" min="0" max="10" value="3" step="1">
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_carpark_text" class="col-sm-5 col-form-label">{{ __( 'project.carpark_text' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_carpark_text" placeholder="e.g., 1-2, 3+, Multiple">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-5 col-form-label">{{ __( 'project.min_storeroom' ) }} Range</label>
                    <div class="col-sm-7">
                        <div class="mb-2">
                            <label class="form-label small">Storeroom: <span id="storeroom_range_display">0 - 2</span></label>
                            <div class="dual-range-slider">
                                <input type="range" class="form-range range-min" id="{{ $project_create }}_min_storeroom" min="0" max="5" value="0" step="1">
                                <input type="range" class="form-range range-max" id="{{ $project_create }}_max_storeroom" min="0" max="5" value="2" step="1">
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_storeroom_text" class="col-sm-5 col-form-label">{{ __( 'project.storeroom_text' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $project_create }}_storeroom_text" placeholder="e.g., 1-2, None, Available">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_balcony" class="col-sm-5 col-form-label">{{ __( 'project.balcony' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $project_create }}_balcony" value="1">
                            <label class="form-check-label" for="{{ $project_create }}_balcony">
                                Has Balcony
                            </label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_building_type" class="col-sm-5 col-form-label">{{ __( 'property.building_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_building_type">
                            @forEach( $buildingTypes as $key => $buildingType )
                                <option value="{{ $key }}">{{ $buildingType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_furnishing_status" class="col-sm-5 col-form-label">{{ __( 'property.furnishing_status' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $project_create }}_furnishing_status">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'property.furnishing_status' ) ] ) }}</option>
                            <option value="1">Fully Furnished</option>
                            <option value="2">Partially Furnished</option>
                            <option value="3">Bared Unit</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_block_number" class="col-sm-5 col-form-label">Block Number</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_block_number" min="0" step="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_total_floor" class="col-sm-5 col-form-label">Total Floor</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_total_floor" min="0" step="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_build_up_area_psf" class="col-sm-5 col-form-label">{{ __( 'property.build_up_area_psf' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_build_up_area_psf" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_selling_price_psf" class="col-sm-5 col-form-label">{{ __( 'property.selling_price_psf' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_selling_price_psf" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_selling_price_unit" class="col-sm-5 col-form-label">{{ __( 'property.selling_price_unit' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_selling_price_unit" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $project_create }}_maintenance_fee_psf" class="col-sm-5 col-form-label">{{ __( 'property.maintenance_fee_psf' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_maintenance_fee_psf" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_completion_date" class="col-sm-5 col-form-label">{{ __( 'property.completion_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="date" class="form-control" id="{{ $project_create }}_completion_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Blocks Section -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ __( 'project.blocks' ) }}</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="{{ $project_create }}_add_block">
                            <i class="fas fa-plus me-1"></i>{{ __( 'project.add_block' ) }}
                        </button>
                    </div>

                    <div id="{{ $project_create }}_blocks_container">
                        <!-- Block items will be added here dynamically -->
                    </div>
                </div>

                <!-- Multilingual Project Details Section -->
                <h5 class="card-title mb-4 mt-5">{{ __( 'project.additional_project_details' ) }}</h5>

                <!-- Language Tabs -->
                <ul class="nav nav-tabs" id="projectDetailsLanguageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-en-tab" data-bs-toggle="tab" data-bs-target="#details-en" type="button" role="tab">English</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-zh_tw-tab" data-bs-toggle="tab" data-bs-target="#details-zh_tw" type="button" role="tab">繁體中文</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-id-tab" data-bs-toggle="tab" data-bs-target="#details-id" type="button" role="tab">Bahasa Indonesia</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-zh_cn-tab" data-bs-toggle="tab" data-bs-target="#details-zh_cn" type="button" role="tab">简体中文</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-ja-tab" data-bs-toggle="tab" data-bs-target="#details-ja" type="button" role="tab">日本語</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-ko-tab" data-bs-toggle="tab" data-bs-target="#details-ko" type="button" role="tab">한국어</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content border border-top-0 p-3" id="projectDetailsTabContent">
                    <!-- English Tab -->
                    <div class="tab-pane fade show active" id="details-en" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">English Project Details</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('en')">
                                    <i class="fas fa-plus me-1"></i>Add Detail
                                </button>
                            </div>
                            <div id="project_details_en_container">
                                <!-- Default example details -->
                            </div>
                        </div>
                    </div>

                    <!-- Chinese Traditional Tab -->
                    <div class="tab-pane fade" id="details-zh_tw" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">繁體中文項目詳情</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('zh_tw')">
                                    <i class="fas fa-plus me-1"></i>新增詳情
                                </button>
                            </div>
                            <div id="project_details_zh_tw_container">
                                <!-- Chinese Traditional details will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Indonesian Tab -->
                    <div class="tab-pane fade" id="details-id" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">Detail Proyek dalam Bahasa Indonesia</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('id')">
                                    <i class="fas fa-plus me-1"></i>Tambah Detail
                                </button>
                            </div>
                            <div id="project_details_id_container">
                                <!-- Indonesian details will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Chinese Simplified Tab -->
                    <div class="tab-pane fade" id="details-zh_cn" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">简体中文项目详情</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('zh_cn')">
                                    <i class="fas fa-plus me-1"></i>新增详情
                                </button>
                            </div>
                            <div id="project_details_zh_cn_container">
                                <!-- Chinese Simplified details will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Japanese Tab -->
                    <div class="tab-pane fade" id="details-ja" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">日本語プロジェクト詳細</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('ja')">
                                    <i class="fas fa-plus me-1"></i>詳細を追加
                                </button>
                            </div>
                            <div id="project_details_ja_container">
                                <!-- Japanese details will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Korean Tab -->
                    <div class="tab-pane fade" id="details-ko" role="tabpanel">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">한국어 프로젝트 세부정보</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProjectDetail('ko')">
                                    <i class="fas fa-plus me-1"></i>세부정보 추가
                                </button>
                            </div>
                            <div id="project_details_ko_container">
                                <!-- Korean details will be added here -->
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="card-title mt-3 mb-3">{{ __( 'project.project_sequence' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $project_create }}_sequence" class="col-sm-5 col-form-label">{{ __( 'property.sequence' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $project_create }}_sequence" min="1" max="99" value="1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $project_create }}_is_pet_friendly" class="col-sm-5 col-form-label">{{ __( 'property.is_pet_friendly' ) }}</label>
                    <div class="col-sm-7 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $project_create }}_is_pet_friendly">
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-12 mb-12">
                <h4 class="mb-3">Project Gallery</h4>

                <!-- Image Upload Section -->
                <div class="mb-3 row">
                    <div class="col-sm-12">
                        <div class="image-upload-zone" >
                            <input type="file" id="project_images" name="images[]" multiple accept="image/*" style="display: none;">
                            <div class="upload-content">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21,15 16,10 5,21"/>
                                </svg>
                                <p class="text-muted mb-1">Click to upload images or drag and drop</p>
                                <small class="text-muted">Supports: JPG, PNG, PDF (Max 5MB each)</small>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">You can upload multiple images at once. Drag to reorder images after upload.</small>

                        <div class="upload-progress">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="upload-status text-muted">Uploading...</small>
                        </div>
                    </div>
                </div>

                <!-- Gallery Stats -->
                <div class="mb-3 row">
                    <div class="col-sm-12">
                        <div class="gallery-stats">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Images:</strong>
                                    <span id="total_images_count">0</span>
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="select_all_images" disabled>
                                            <i class="ni ni-check-circle"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" id="delete_selected" disabled>
                                            <i class="ni ni-trash"></i> Delete Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Preview Section -->
                <div class="mb-3 row">
                    <div class="col-sm-12">
                        <div class="image-preview">
                            <div id="image_preview" class="row" data-sortable="true">
                                <!-- Dynamic image previews will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Images (for edit mode) -->
                <div class="mb-3 row" id="existing_images" style="display: none;">
                    <div class="col-sm-5">
                        <label class="col-form-label">Current Images</label>
                    </div>
                    <div class="col-sm-7">
                        <div id="existing_images_container" class="row">
                            <!-- Existing images will be loaded here via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="mb-3 row">
                    <div class="col-sm-12">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('project_images').click()">
                                <i class="ni ni-plus"></i> Add More Images
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="sort_by_name">
                                <i class="ni ni-sort-alpha-up"></i> Sort by Name
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="reset_order">
                                <i class="ni ni-reload"></i> Reset Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                <div class="mt-12 mb-12">
                    <h4 class="mb-3 mt-3">Project Floorplan</h4>
                    
                    <!-- Floorplan Upload Section -->
                    <div class="mb-3 row">
                        <div class="col-sm-12">
                            <div class="image-upload-zone" id="floorplan_upload_zone">
                                <input type="file" id="projects_floorplans" name="floorplans[]" multiple accept="image/*,application/pdf" style="display: none;">
                                <div class="upload-content">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <path d="M8.5 8.5h.01"/>
                                        <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                    <p class="text-muted mb-1">Click to upload floorplans or drag and drop</p>
                                    <small class="text-muted">Supports: JPG, PNG, PDF (Max 5MB each)</small>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">You can upload multiple floorplans at once. Drag to reorder floorplans after upload.</small>

                            <div class="upload-progress" id="floorplan_upload_progress">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="upload-status text-muted">Uploading...</small>
                            </div>
                            
                        </div>
                    </div>

                    <!-- Floorplan Preview Section -->
                    <div class="mb-3 row">
                        <div class="col-sm-12">
                            <div class="image-preview">
                                <div id="floorplan_preview" class="row" data-sortable="true">
                                    <!-- Dynamic floorplan previews will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Floorplans (for edit mode) -->
                    <div class="mb-3 row" id="existing_floorplans" style="display: none;">
                        <div class="col-sm-5">
                            <label class="col-form-label">Current Floorplans</label>
                        </div>
                        <div class="col-sm-7">
                            <div id="existing_floorplans_container" class="row">
                                <!-- Existing floorplans will be loaded here via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mb-3 row">
                        <div class="col-sm-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="select_all_floorplans">
                                    Select All
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="delete_selected_floorplans">
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $project_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $project_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $project_create }}',
                fileID = '';

        $( fc + '_completion_date' ).flatpickr( {
            disableMobile: true,
            dateFormat: "d/m/Y", // dd/mm/yyyy format
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
            }
        } );

        // Initialize dual-thumb range sliders
        function initializeSliders() {
            // Bedroom sliders
            const minBedroomsSlider = document.getElementById('{{ $project_create }}_min_bedrooms');
            const maxBedroomsSlider = document.getElementById('{{ $project_create }}_max_bedrooms');
            const bedroomsDisplay = document.getElementById('bedrooms_range_display');

            // Bathroom sliders
            const minBathroomsSlider = document.getElementById('{{ $project_create }}_min_bathrooms');
            const maxBathroomsSlider = document.getElementById('{{ $project_create }}_max_bathrooms');
            const bathroomsDisplay = document.getElementById('bathrooms_range_display');

            // Carpark sliders
            const minCarparkSlider = document.getElementById('{{ $project_create }}_min_carpark');
            const maxCarparkSlider = document.getElementById('{{ $project_create }}_max_carpark');
            const carparkDisplay = document.getElementById('carpark_range_display');

            // Storeroom sliders
            const minStoreroomSlider = document.getElementById('{{ $project_create }}_min_storeroom');
            const maxStoreroomSlider = document.getElementById('{{ $project_create }}_max_storeroom');
            const storeroomDisplay = document.getElementById('storeroom_range_display');

            function updateBedroomsRange() {
                let minVal = parseInt(minBedroomsSlider.value);
                let maxVal = parseInt(maxBedroomsSlider.value);
                
                // Ensure min is not greater than max
                if (minVal > maxVal) {
                    // If min slider moved beyond max, adjust max
                    if (event && event.target === minBedroomsSlider) {
                        maxVal = minVal;
                        maxBedroomsSlider.value = maxVal;
                    } else {
                        // If max slider moved below min, adjust min
                        minVal = maxVal;
                        minBedroomsSlider.value = minVal;
                    }
                }
                
                bedroomsDisplay.textContent = minVal + ' - ' + maxVal;
                
                // Subtle z-index adjustment - just bring active slider slightly forward
                if (event && event.target === minBedroomsSlider) {
                    minBedroomsSlider.style.zIndex = '3';
                    maxBedroomsSlider.style.zIndex = '1';
                } else if (event && event.target === maxBedroomsSlider) {
                    minBedroomsSlider.style.zIndex = '1';
                    maxBedroomsSlider.style.zIndex = '3';
                }
            }

            function updateBathroomsRange() {
                let minVal = parseInt(minBathroomsSlider.value);
                let maxVal = parseInt(maxBathroomsSlider.value);
                
                // Ensure min is not greater than max
                if (minVal > maxVal) {
                    // If min slider moved beyond max, adjust max
                    if (event && event.target === minBathroomsSlider) {
                        maxVal = minVal;
                        maxBathroomsSlider.value = maxVal;
                    } else {
                        // If max slider moved below min, adjust min
                        minVal = maxVal;
                        minBathroomsSlider.value = minVal;
                    }
                }
                
                bathroomsDisplay.textContent = minVal + ' - ' + maxVal;
                
                // Subtle z-index adjustment - just bring active slider slightly forward
                if (event && event.target === minBathroomsSlider) {
                    minBathroomsSlider.style.zIndex = '3';
                    maxBathroomsSlider.style.zIndex = '1';
                } else if (event && event.target === maxBathroomsSlider) {
                    minBathroomsSlider.style.zIndex = '1';
                    maxBathroomsSlider.style.zIndex = '3';
                }
            }

            function updateCarparkRange() {
                let minVal = parseInt(minCarparkSlider.value);
                let maxVal = parseInt(maxCarparkSlider.value);

                // Ensure min is not greater than max
                if (minVal > maxVal) {
                    // If min slider moved beyond max, adjust max
                    if (event && event.target === minCarparkSlider) {
                        maxVal = minVal;
                        maxCarparkSlider.value = maxVal;
                    } else {
                        // If max slider moved below min, adjust min
                        minVal = maxVal;
                        minCarparkSlider.value = minVal;
                    }
                }

                carparkDisplay.textContent = minVal + ' - ' + maxVal;

                // Subtle z-index adjustment
                if (event && event.target === minCarparkSlider) {
                    minCarparkSlider.style.zIndex = '3';
                    maxCarparkSlider.style.zIndex = '1';
                } else if (event && event.target === maxCarparkSlider) {
                    minCarparkSlider.style.zIndex = '1';
                    maxCarparkSlider.style.zIndex = '3';
                }
            }

            function updateStoreroomRange() {
                let minVal = parseInt(minStoreroomSlider.value);
                let maxVal = parseInt(maxStoreroomSlider.value);

                // Ensure min is not greater than max
                if (minVal > maxVal) {
                    // If min slider moved beyond max, adjust max
                    if (event && event.target === minStoreroomSlider) {
                        maxVal = minVal;
                        maxStoreroomSlider.value = maxVal;
                    } else {
                        // If max slider moved below min, adjust min
                        minVal = maxVal;
                        minStoreroomSlider.value = minVal;
                    }
                }

                storeroomDisplay.textContent = minVal + ' - ' + maxVal;

                // Subtle z-index adjustment
                if (event && event.target === minStoreroomSlider) {
                    minStoreroomSlider.style.zIndex = '3';
                    maxStoreroomSlider.style.zIndex = '1';
                } else if (event && event.target === maxStoreroomSlider) {
                    minStoreroomSlider.style.zIndex = '1';
                    maxStoreroomSlider.style.zIndex = '3';
                }
            }

            // Add event listeners with proper event passing
            if (minBedroomsSlider && maxBedroomsSlider) {
                minBedroomsSlider.addEventListener('input', updateBedroomsRange);
                maxBedroomsSlider.addEventListener('input', updateBedroomsRange);
                
                // Initialize displays
                updateBedroomsRange();
            }
            
            if (minBathroomsSlider && maxBathroomsSlider) {
                minBathroomsSlider.addEventListener('input', updateBathroomsRange);
                maxBathroomsSlider.addEventListener('input', updateBathroomsRange);

                // Carpark event listeners
                minCarparkSlider.addEventListener('input', updateCarparkRange);
                maxCarparkSlider.addEventListener('input', updateCarparkRange);

                // Storeroom event listeners
                minStoreroomSlider.addEventListener('input', updateStoreroomRange);
                maxStoreroomSlider.addEventListener('input', updateStoreroomRange);

                // Initialize displays
                updateBathroomsRange();
                updateCarparkRange();
                updateStoreroomRange();
            }
        }

        // Initialize sliders
        initializeSliders();

        // Project Details Management Functions
        window.addProjectDetail = function(language) {
            const container = document.getElementById(`project_details_${language}_container`);
            const detailItem = document.createElement('div');
            detailItem.className = 'mb-3 project-detail-item';
            detailItem.innerHTML = `
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label small">Title</label>
                        <input type="text" class="form-control" placeholder="Title">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small">Description</label>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProjectDetail(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea class="form-control" rows="2" placeholder="Description"></textarea>
                    </div>
                </div>
            `;
            container.appendChild(detailItem);
        };

        window.removeProjectDetail = function(button) {
            button.closest('.project-detail-item').remove();
        };

        // Function to collect all project details
        function collectProjectDetails() {
            const languages = ['en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko'];
            const projectDetails = {};

            languages.forEach(language => {
                const container = document.getElementById(`project_details_${language}_container`);
                const detailItems = container.querySelectorAll('.project-detail-item');
                const details = [];

                detailItems.forEach(item => {
                    const title = item.querySelector('.col-md-4 input').value.trim();
                    const description = item.querySelector('.col-md-8 textarea').value.trim();

                    if (title && description) {
                        details.push({
                            title: title,
                            description: description
                        });
                    }
                });

                if (details.length > 0) {
                    projectDetails[language] = details;
                }
            });

            return projectDetails;
        }

        // Block management variables and functions
        let blockCounter = 0;

        function createBlockItem() {
            blockCounter++;
            let blockHtml = `
                <div class="block-item border rounded p-3 mb-3" data-block-id="${blockCounter}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ __( 'project.block' ) }} #${blockCounter}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-block">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.block' ) }} {{ __( 'template.name' ) }}</label>
                                <input type="text" class="form-control block-name" placeholder="{{ __( 'project.block_name_placeholder' ) }}" maxlength="255" />
                            </div>
                        </div>
                        <div class="col-md-6 d-none">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.total_units' ) }}</label>
                                <input type="number" class="form-control block-total-units" min="0" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.block_prefix' ) }}</label>
                                <input type="text" class="form-control block-prefix" placeholder="{{ __( 'project.block_prefix_placeholder' ) }}" maxlength="10" />
                                <small class="form-text text-muted">{{ __( 'project.prefix_for_unit_number' ) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.total_floors' ) }}</label>
                                <input type="number" class="form-control block-total-floors" min="1" max="999" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'project.floor_prefix' ) }}</label>
                                <input type="text" class="form-control block-floor-prefix" placeholder="{{ __( 'project.floor_prefix_placeholder' ) }}" maxlength="10" />
                                <small class="form-text text-muted">{{ __( 'project.floor_prefix_if_any' ) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $(fc + '_blocks_container').append(blockHtml);
        }

        // Add block button
        $( fc + '_add_block' ).on('click', function() {
            createBlockItem();
        });

        // Remove block button
        $(document).on('click', '.remove-block', function() {
            $(this).closest('.block-item').remove();
        });

        // Initialize with one block
        createBlockItem();

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.project.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            buttonSubmitting( this );

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();

            // Sync current form input values for floorplans before getting data
            if (window.projectFloorplan) {
                window.projectFloorplan.syncFloorplanFormData();
            }

            // Get floorplan data from the ProjectFloorplan instance
            const floorplanData = window.projectFloorplan ? window.projectFloorplan.getFloorplanData() : null;

            if (floorplanData) {
                // Add new floorplan files and their data
                floorplanData.new_floorplans.forEach((floorplan, index) => {
                    // Add the actual file
                    formData.append(`new_floorplans[${index}]`, floorplan.file);
                    // Add additional attachment file if exists
                    if (floorplan.additional_attachment) {
                        formData.append(`new_floorplans_additional_attachment[${index}]`, floorplan.additional_attachment);
                    }
                    // Add metadata for this new floorplan
                    formData.append(`new_floorplans_remarks[${index}]`, floorplan.remarks || '');
                    formData.append(`new_floorplans_bathrooms[${index}]`, floorplan.bathrooms || '');
                    formData.append(`new_floorplans_bedrooms[${index}]`, floorplan.bedrooms || '');
                    formData.append(`new_floorplans_balcony[${index}]`, floorplan.balcony || '');
                    formData.append(`new_floorplans_storeroom[${index}]`, floorplan.storeroom || '');
                    formData.append(`new_floorplans_parking_spaces[${index}]`, floorplan.parking_spaces || '');
                    formData.append(`new_floorplans_upload_link[${index}]`, floorplan.upload_link || '');
                    formData.append(`new_floorplans_category[${index}]`, floorplan.category || '');
                });

                // Add existing floorplans data (updates only, not files)
                formData.append('existing_floorplans', JSON.stringify(floorplanData.existing_floorplans));
                
                // Add deleted floorplans IDs
                formData.append('deleted_floorplans', JSON.stringify(floorplanData.deleted_floorplans));
                
                // Add floorplan order
                formData.append('floorplans_order', JSON.stringify(floorplanData.floorplan_order));
                
                // Add counts for backend validation
                formData.append('new_floorplans_count', floorplanData.new_floorplans.length);
                formData.append('existing_floorplans_count', floorplanData.existing_floorplans.length);
                formData.append('deleted_floorplans_count', floorplanData.deleted_floorplans.length);
            } else {
                // Fallback method if ProjectFloorplan is not initialized
                console.warn('ProjectFloorplan not initialized, using fallback method');
                
                const fileInput = document.getElementById('projects_floorplans');
                if (fileInput && fileInput.files.length > 0) {
                    // Add all selected files
                    Array.from(fileInput.files).forEach((file, index) => {
                        formData.append(`new_floorplans[${index}]`, file);
                    });
                }
            }

            // Get gallery data from the ProjectGallery instance
            const galleryData = window.projectGallery ? window.projectGallery.getGalleryData() : null;

            if (galleryData) {
                // Add new image files and their data
                galleryData.new_images.forEach((image, index) => {
                    // Add the actual file
                    formData.append(`new_images[${index}]`, image.file);
                    // Add metadata for this new image
                    formData.append(`new_images_remarks[${index}]`, image.remarks || '');
                });

                // Add existing images data (updates only, not files)
                formData.append('existing_images', JSON.stringify(galleryData.existing_images));

                // Add deleted images IDs
                formData.append('deleted_images', JSON.stringify(galleryData.deleted_images));

                // Add image order
                formData.append('images_order', JSON.stringify(galleryData.image_order));


                // Add counts for backend validation
                formData.append('new_images_count', galleryData.new_images.length);
                formData.append('existing_images_count', galleryData.existing_images.length);
                formData.append('deleted_images_count', galleryData.deleted_images.length);
            } else {
                // Fallback method if ProjectGallery is not initialized
                console.warn('ProjectGallery not initialized, using fallback method');

                const fileInput = document.getElementById('project_images');
                if (fileInput && fileInput.files.length > 0) {
                    // Add all selected files
                    Array.from(fileInput.files).forEach((file, index) => {
                        formData.append(`new_images[${index}]`, file);
                    });
                }
            }

            // Collect project translations
            const languages = ['en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko'];
            const projectTranslations = {};
            languages.forEach(function(lang) {
                let descriptionData = '';
                // Get CKEditor data for this language
                if (window.editors && window.editors[`description_${lang}`]) {
                    descriptionData = window.editors[`description_${lang}`].getData();
                } else {
                    // Fallback to textarea value if CKEditor is not initialized
                    descriptionData = $(`#description_${lang}`).val() || '';
                }
                
                projectTranslations[lang] = {
                    title: $(`#title_${lang}`).val() || '',
                    short_description: $(`#short_description_${lang}`).val() || '',
                    description: descriptionData
                };
            });
            formData.append('translations', JSON.stringify(projectTranslations));
            
            formData.append( 'developers', JSON.stringify($( fc + '_developers' ).val()) );
            formData.append( 'developer_id', $( fc + '_developer' ).val() );
            formData.append( 'country_id', $( fc + '_country' ).val() );
            formData.append( 'project_status', $( fc + '_project_status' ).val() );
            let amenities = $(fc + '_amenities').val() || [];
            formData.append(
                'amenities',
                JSON.stringify(amenities.map(v => parseInt(v)))
            );
            // formData.append( 'amenities', JSON.stringify($( fc + '_amenities' ).val()) );
            formData.append( 'property_type', $( fc + '_property_type' ).val() );
            formData.append( 'tenure', $( fc + '_tenure' ).val() );
            formData.append( 'min_bedrooms', $( fc + '_min_bedrooms' ).val() );
            formData.append( 'max_bedrooms', $( fc + '_max_bedrooms' ).val() );
            formData.append( 'bedroom_text', $( fc + '_bedroom_text' ).val() );
            formData.append( 'min_bathrooms', $( fc + '_min_bathrooms' ).val() );
            formData.append( 'max_bathrooms', $( fc + '_max_bathrooms' ).val() );
            formData.append( 'bathroom_text', $( fc + '_bathroom_text' ).val() );
            formData.append( 'min_carpark', $( fc + '_min_carpark' ).val() );
            formData.append( 'max_carpark', $( fc + '_max_carpark' ).val() );
            formData.append( 'carpark_text', $( fc + '_carpark_text' ).val() );
            formData.append( 'min_storeroom', $( fc + '_min_storeroom' ).val() );
            formData.append( 'max_storeroom', $( fc + '_max_storeroom' ).val() );
            formData.append( 'storeroom_text', $( fc + '_storeroom_text' ).val() );
            formData.append( 'balcony', $( fc + '_balcony' ).is(':checked') ? 1 : 0 );
            formData.append( 'building_type', $( fc + '_building_type' ).val() );
            formData.append( 'furnishing_status', $( fc + '_furnishing_status' ).val() );
            formData.append( 'block_number', $( fc + '_block_number' ).val() );
            formData.append( 'total_floor', $( fc + '_total_floor' ).val() );

            // Collect blocks data
            let blocks = [];
            $(fc + '_blocks_container .block-item').each(function(index) {
                let blockData = {
                    name: $(this).find('.block-name').val(),
                    total_units: $(this).find('.block-total-units').val(),
                    block_prefix: $(this).find('.block-prefix').val(),
                    total_floors: $(this).find('.block-total-floors').val(),
                    floor_prefix: $(this).find('.block-floor-prefix').val()
                };
                blocks.push(blockData);
                formData.append('blocks[' + index + '][name]', blockData.name);
                formData.append('blocks[' + index + '][total_units]', blockData.total_units);
                formData.append('blocks[' + index + '][block_prefix]', blockData.block_prefix);
                formData.append('blocks[' + index + '][total_floors]', blockData.total_floors);
                formData.append('blocks[' + index + '][floor_prefix]', blockData.floor_prefix);
            });

            formData.append( 'build_up_area_psf', $( fc + '_build_up_area_psf' ).val() );
            formData.append( 'selling_price_psf', $( fc + '_selling_price_psf' ).val() );
            formData.append( 'selling_price_unit', $( fc + '_selling_price_unit' ).val() );
            formData.append( 'maintenance_fee_psf', $( fc + '_maintenance_fee_psf' ).val() );
            formData.append( 'completion_date', $( fc + '_completion_date' ).val() );
            formData.append( 'sequence', $( fc + '_sequence' ).val() );
            formData.append( 'is_pet_friendly', $( fc + '_is_pet_friendly' ).is(':checked') ? 1 : 0 );
            formData.append( 'address_line_1', $( fc + '_address_line_1' ).val() );
            formData.append( 'address_line_2', $( fc + '_address_line_2' ).val() );
            formData.append( 'address_line_3', $( fc + '_address_line_3' ).val() );
            formData.append( 'state', $( fc + '_state' ).val() );
            formData.append( 'project_locations', JSON.stringify($( fc + '_project_locations' ).val()) );
            formData.append( 'postcode', $( fc + '_postcode' ).val() );
            formData.append( 'longitude', $( fc + '_longitude' ).val() );
            formData.append( 'latitude', $( fc + '_latitude' ).val() );

            // Collect project details
            const projectDetails = collectProjectDetails();
            formData.append( 'project_details', JSON.stringify(projectDetails) );

            formData.append( 'logo', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.project.createProject' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    buttonSubmitted( $( fc + '_submit' ) );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.project.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    buttonSubmitted( $( fc + '_submit' ) );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            if (key === 'translations') {
                                // handle translation error
                                $('#title_en')
                                    .addClass('is-invalid')
                                    .nextAll('div.invalid-feedback')
                                    .text(value);
                            } else if (key.includes('block')) {
                                // if key contains "block", show danger modal
                                $('#modal_danger .caption-text').html('Block Prefix is required');
                                modalDanger.toggle();
                            } else if (key.includes('new_floorplans_remarks')) {
                                // if key contains "new_floorplans_remarks", show danger modal
                                $('#modal_danger .caption-text').html('Floorplan Name is required');
                                modalDanger.toggle();
                            } else {
                                // handle all other field errors
                                $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        // Developers select2 initialization
        let developersSelect2 = $( fc + '_developers' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
        } );

        let amenitiesSelect2 = $( fc + '_amenities' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
        } );

        // countries select2 initialization
        let countrySelect2 = $( fc + '_country' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
        } );

        // property locations select2 initialization
        let areaSelect2 = $( fc + '_project_locations' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.property_location.allPropertyLocations' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.property_locations.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );
        

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( fc + '_logo', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID !== '' ) {
                        fileID += ','; // Add a comma if fileID is not empty
                    }
                    fileID += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );

    } );

    class ProjectFloorplan {
        constructor() {
            this.projectId = document.querySelector('[data-project-id]')?.value || 
                            new URLSearchParams(window.location.search).get('id') || '';
            this.existingFloorplans = [];
            this.newFloorplans = [];
            this.deletedFloorplans = [];
            this.floorplanOrder = [];
            this.sortableInstance = null;
            this.init();
        }

        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.initializeComponents();
                });
            } else {
                this.initializeComponents();
            }
        }

        initializeComponents() {
            this.bindEvents();
            this.initDragDrop();
            this.loadExistingFloorplans();
        }

        bindEvents() {
            const fileInput = document.getElementById('projects_floorplans');
            const uploadZone = document.querySelector('#floorplan_upload_zone');
            const selectAllBtn = document.getElementById('select_all_floorplans');
            const deleteSelectedBtn = document.getElementById('delete_selected_floorplans');
            
            // File input change
            if (fileInput) {
                fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
            } else {
                console.warn('Project floorplan file input element not found');
            }
            
            // Upload zone click
            if (uploadZone && fileInput) {
                uploadZone.addEventListener('click', (e) => {
                    // Prevent clicking on the file input from triggering twice
                    if (e.target !== fileInput && !e.target.closest('input')) {
                        fileInput.click();
                    }
                });
            } else {
                console.warn('Project floorplan upload zone not found');
            }

            // Bulk actions
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => this.selectAllFloorplans());
            }
            
            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', () => this.deleteSelected());
            }
        }

        initDragDrop() {
            const uploadZone = document.getElementById('floorplan_upload_zone');
            
            if (!uploadZone) {
                console.warn('Project floorplan upload zone not found for drag-drop initialization');
                return;
            }

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadZone.addEventListener(eventName, () => {
                    uploadZone.classList.add('dragover');
                });
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, () => {
                    uploadZone.classList.remove('dragover');
                });
            });
            
            uploadZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                this.handleFiles(files);
            });
        }

        initSortable() {
            const container = document.getElementById('floorplan_preview');
            
            if (!container) {
                console.warn('Project floorplan container not found for sortable initialization');
                return;
            }
            
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }
            
            if (typeof Sortable !== 'undefined') {
                this.sortableInstance = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.card',
                    onEnd: (evt) => {
                        this.updateFloorplanOrder();
                    }
                });
            } else {
                console.warn('Sortable library not loaded');
            }
        }

        loadExistingFloorplans() {
            if (!this.projectId) {
                console.log('No project ID found, skipping floorplan load');
                return;
            }

            // This would be implemented when backend is ready
            console.log('Loading existing floorplans for project:', this.projectId);
            this.renderAllFloorplans();
        }

        handleFiles(files) {
            const validFiles = Array.from(files).filter(file => {
                const isImage = file.type.startsWith('image/');
                const isPdf = file.type === 'application/pdf';
                
                if (!isImage && !isPdf) {
                    this.showNotification(`File ${file.name} is not a supported format`, 'warning');
                    return false;
                }
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    this.showNotification(`File ${file.name} is too large (max 5MB)`, 'warning');
                    return false;
                }
                return true;
            });

            validFiles.forEach(file => {
                this.createFloorplanPreview(file);
            });
        }

        createFloorplanPreview(file) {
            const reader = new FileReader();
            const floorplanId = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            reader.onload = (e) => {
                const newFloorplan = {
                    id: floorplanId,
                    file: file,
                    url: e.target.result,
                    name: file.name,
                    remarks: '',
                    is_new: true
                };
                
                this.newFloorplans.push(newFloorplan);
                this.renderAllFloorplans();
            };
            
            reader.readAsDataURL(file);
        }

        renderAllFloorplans() {
            const container = document.getElementById('floorplan_preview');
            
            if (!container) {
                console.warn('Project floorplan container not found');
                return;
            }
            
            container.innerHTML = '';
            // Filter out deleted floorplans
            const activeExistingFloorplans = this.existingFloorplans.filter(fp => !this.deletedFloorplans.includes(fp.id));
            
            // Combine existing and new floorplans
            const allFloorplans = [...activeExistingFloorplans, ...this.newFloorplans];
            
            allFloorplans.forEach(floorplan => {
                container.appendChild(this.createFloorplanElement(floorplan));
            });
            
            this.initSortable();
        }

        createFloorplanElement(floorplan) {
            const div = document.createElement('div');
            div.className = 'col-md-4 col-lg-4 image-item';
            div.dataset.floorplanId = floorplan.id;
            div.dataset.sequence = floorplan.sequence || 0;
            
            const isNew = floorplan.is_new || false;
            const statusBadge = isNew ? '<span class="badge bg-info">New</span>' : '<span class="badge bg-success">Existing</span>';
            
            // Use floorplan_url for existing floorplans, url for new floorplans
            const floorplanSrc = floorplan.floorplan_url || floorplan.url;
            const floorplanAlt = floorplan.remarks || 'Project floorplan';
            const floorplanBathroomAlt = floorplan.bathrooms || '1';
            const floorplanBedroomAlt = floorplan.bedrooms || '1';
            
            // Check if it's a PDF file
            const isPdf = floorplan.file ? floorplan.file.type === 'application/pdf' : 
                         floorplan.name ? floorplan.name.toLowerCase().endsWith('.pdf') : false;
            
            const previewContent = isPdf ? 
                `<div class="d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                    <div class="text-center">
                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                        <p class="mb-0 small">${floorplan.name || 'PDF Floorplan'}</p>
                    </div>
                </div>` :
                `<img src="${floorplanSrc}" class="card-img-top" 
                    style="height: 200px; object-fit: cover;" 
                    alt="${floorplanAlt}">`;
            
            div.innerHTML = `
                <div class="card h-100">
                    <div class="image-controls">
                        <input type="checkbox" class="form-check-input floorplan-selector" value="${floorplan.id}">
                    </div>
                    ${previewContent}
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            ${statusBadge}
                            <button type="button" class="btn btn-sm btn-outline-danger remove-floorplan-btn" data-floorplan-id="${floorplan.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm floorplan-remarks"
                                data-floorplan-id="${floorplan.id}"
                                value="${floorplan.remarks || ''}"
                                placeholder="Floorplan Name">
                        </div>

                        <!-- Floorplan Source Type Selection -->
                        <div class="mb-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input floorplan-type-radio" type="radio"
                                    name="floorplan_source_${floorplan.id}"
                                    id="floorplan_upload_${floorplan.id}"
                                    value="upload" checked>
                                <label class="form-check-label" for="floorplan_upload_${floorplan.id}">
                                    Upload
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input floorplan-type-radio" type="radio"
                                    name="floorplan_source_${floorplan.id}"
                                    id="floorplan_link_${floorplan.id}"
                                    value="link">
                                <label class="form-check-label" for="floorplan_link_${floorplan.id}">
                                    Link
                                </label>
                            </div>
                        </div>

                        <!-- Upload Section -->
                        <div class="mb-2 floorplan-upload-section" id="upload_section_${floorplan.id}">
                            <label class="form-label-sm">Upload File</label>
                            <input type="file" class="form-control form-control-sm floorplan-file-input"
                                data-floorplan-id="${floorplan.id}"
                                name="floorplan_additional_attachment_${floorplan.id}"
                                accept="image/*,application/pdf">
                            <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                            ${floorplan.additional_attachment ? `<div class="mt-1"><small class="text-success">Current: ${floorplan.additional_attachment}</small></div>` : ''}
                        </div>

                        <!-- Link Section -->
                        <div class="mb-2 floorplan-link-section" id="link_section_${floorplan.id}" style="display: none;">
                            <label class="form-label-sm">Document Link</label>
                            <input type="url" class="form-control form-control-sm floorplan-document-link"
                                data-floorplan-id="${floorplan.id}"
                                name="floorplan_upload_link_${floorplan.id}"
                                value="${floorplan.upload_link || ''}"
                                placeholder="https://example.com/floorplan.pdf">
                            <small class="text-muted">Direct link to document</small>
                        </div>
                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_bedroom_${floorplan.id}">
                                Bedroom
                            </label>
                            <div class="col-sm-9">
                                <input type="number" 
                                    id="floorplan_bedroom_${floorplan.id}" 
                                    class="form-control form-control-sm floorplan-bedrooms" 
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.bedrooms || ''}" 
                                    placeholder="Enter floorplan bedroom">
                            </div>
                        </div>

                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_bathroom_${floorplan.id}">
                                Bathroom
                            </label>
                            <div class="col-sm-9">
                                <input type="number" 
                                    id="floorplan_bathroom_${floorplan.id}" 
                                    class="form-control form-control-sm floorplan-bathrooms" 
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.bathrooms || ''}" 
                                    placeholder="Enter floorplan bathroom">
                            </div>
                        </div>

                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_category_${floorplan.id}">
                                Category
                            </label>
                            <div class="col-sm-9">
                                <input type="text"
                                    id="floorplan_category_${floorplan.id}"
                                    class="form-control form-control-sm floorplan-category"
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.category || ''}"
                                    placeholder="Enter floorplan category">
                            </div>
                        </div>

                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_balcony_${floorplan.id}">
                                Balcony
                            </label>
                            <div class="col-sm-9">
                                <input type="number" 
                                    id="floorplan_balcony_${floorplan.id}" 
                                    class="form-control form-control-sm floorplan-balcony" 
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.balcony || ''}" 
                                    placeholder="Enter floorplan balcony">
                            </div>
                        </div>

                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_storeroom_${floorplan.id}">
                                Storeroom
                            </label>
                            <div class="col-sm-9">
                                <input type="number" 
                                    id="floorplan_storeroom_${floorplan.id}" 
                                    class="form-control form-control-sm floorplan-storeroom" 
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.storeroom || ''}" 
                                    placeholder="Enter floorplan storeroom">
                            </div>
                        </div>

                        <div class="mb-2 row d-none">
                            <label class="col-sm-5 col-form-labelcol-form-label-sm" for="floorplan_parking_spaces_${floorplan.id}">
                                Parking
                            </label>
                            <div class="col-sm-9">
                                <input type="number" 
                                    id="floorplan_parking_spaces_${floorplan.id}" 
                                    class="form-control form-control-sm floorplan-parking-spaces" 
                                    data-floorplan-id="${floorplan.id}"
                                    value="${floorplan.parking_spaces || ''}" 
                                    placeholder="Enter parking spaces">
                            </div>
                        </div>

                        <small class="text-muted d-block">Seq: ${floorplan.sequence || 'New'}</small>
                    </div>
                </div>
            `;
            
            // Add event listeners after element is created
            setTimeout(() => {
                const remarksInput = div.querySelector('.floorplan-remarks');
                if (remarksInput) {
                    remarksInput.addEventListener('change', (e) => {
                        this.updateFloorplanRemarks(floorplan.id, e.target.value);
                    });
                }

                const bedroomsInput = div.querySelector('.floorplan-bedrooms');
                if (bedroomsInput) {
                    bedroomsInput.addEventListener('change', (e) => {
                        this.updateFloorplanBedrooms(floorplan.id, e.target.value);
                    });
                }

                const bathroomsInput = div.querySelector('.floorplan-bathrooms');
                if (bathroomsInput) {
                    bathroomsInput.addEventListener('change', (e) => {
                        this.updateFloorplanBathrooms(floorplan.id, e.target.value);
                    });
                }

                const balconyInput = div.querySelector('.floorplan-balcony');
                if (balconyInput) {
                    balconyInput.addEventListener('change', (e) => {
                        this.updateFloorplanBalcony(floorplan.id, e.target.value);
                    });
                }

                const storeroomInput = div.querySelector('.floorplan-storeroom');
                if (storeroomInput) {
                    storeroomInput.addEventListener('change', (e) => {
                        this.updateFloorplanStoreroom(floorplan.id, e.target.value);
                    });
                }

                const parkingSpacesInput = div.querySelector('.floorplan-parking-spaces');
                if (parkingSpacesInput) {
                    parkingSpacesInput.addEventListener('change', (e) => {
                        this.updateFloorplanParkingSpaces(floorplan.id, e.target.value);
                    });
                }

                // Add event listeners for source type toggle
                const uploadRadio = div.querySelector(`#floorplan_upload_${floorplan.id}`);
                const linkRadio = div.querySelector(`#floorplan_link_${floorplan.id}`);
                const uploadSection = div.querySelector(`#upload_section_${floorplan.id}`);
                const linkSection = div.querySelector(`#link_section_${floorplan.id}`);

                if (uploadRadio && linkRadio && uploadSection && linkSection) {
                    const toggleSections = () => {
                        if (uploadRadio.checked) {
                            uploadSection.style.display = 'block';
                            linkSection.style.display = 'none';
                        } else {
                            uploadSection.style.display = 'none';
                            linkSection.style.display = 'block';
                        }
                    };

                    uploadRadio.addEventListener('change', toggleSections);
                    linkRadio.addEventListener('change', toggleSections);
                }

                // Add event listener for file input
                const fileInput = div.querySelector('.floorplan-file-input');
                if (fileInput) {
                    fileInput.addEventListener('change', (e) => {
                        this.updateFloorplanFile(floorplan.id, e.target.files[0]);
                    });
                }

                // Add event listener for document link
                const linkInput = div.querySelector('.floorplan-document-link');
                if (linkInput) {
                    linkInput.addEventListener('change', (e) => {
                        this.updateFloorplanDocumentLink(floorplan.id, e.target.value);
                    });
                }

                const removeBtn = div.querySelector('.remove-floorplan-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        this.removeFloorplan(floorplan.id);
                    });
                }
            }, 0);
            
            return div;
        }

        updateFloorplanRemarks(floorplanId, remarks) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.remarks = remarks;
            }
            
            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.remarks = remarks;
            }
        }

        updateFloorplanBedrooms(floorplanId, bedrooms) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.bedrooms = bedrooms;
            }
            
            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.bedrooms = bedrooms;
            }
        }

        updateFloorplanBathrooms(floorplanId, bathrooms) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.bathrooms = bathrooms;
            }
            
            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.bathrooms = bathrooms;
            }
        }

        updateFloorplanBalcony(floorplanId, balcony) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.balcony = balcony;
            }
            
            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.balcony = balcony;
            }
        }

        updateFloorplanStoreroom(floorplanId, storeroom) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.storeroom = storeroom;
            }
            
            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.storeroom = storeroom;
            }
        }

        updateFloorplanParkingSpaces(floorplanId, parkingSpaces) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.parking_spaces = parkingSpaces;
            }

            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.parking_spaces = parkingSpaces;
            }
        }

        updateFloorplanFile(floorplanId, file) {
            if (!file) return;

            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.file = file;
                existingFloorplan.upload_link = ''; // Clear link when file is uploaded
            }

            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.file = file;
                newFloorplan.upload_link = ''; // Clear link when file is uploaded
            }
        }

        updateFloorplanDocumentLink(floorplanId, documentLink) {
            // Update in existing floorplans
            const existingFloorplan = this.existingFloorplans.find(fp => fp.id === floorplanId);
            if (existingFloorplan) {
                existingFloorplan.upload_link = documentLink;
                existingFloorplan.file = null; // Clear file when link is provided
            }

            // Update in new floorplans
            const newFloorplan = this.newFloorplans.find(fp => fp.id === floorplanId);
            if (newFloorplan) {
                newFloorplan.upload_link = documentLink;
                newFloorplan.file = null; // Clear file when link is provided
            }
        }

        removeFloorplan(floorplanId) {
            if (!confirm('Are you sure you want to remove this floorplan?')) {
                return;
            }
            
            // Check if it's an existing floorplan
            const existingIndex = this.existingFloorplans.findIndex(fp => fp.id === floorplanId);
            if (existingIndex !== -1) {
                this.deletedFloorplans.push(floorplanId);
            } else {
                // Remove from new floorplans
                this.newFloorplans = this.newFloorplans.filter(fp => fp.id !== floorplanId);
            }
            
            this.renderAllFloorplans();
        }

        selectAllFloorplans() {
            const checkboxes = document.querySelectorAll('.floorplan-selector');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            
            // Update button text if exists
            const btn = document.getElementById('select_all_floorplans');
            if (btn) {
                btn.textContent = allChecked ? 'Select All' : 'Deselect All';
            }
        }

        deleteSelected() {
            const selected = document.querySelectorAll('.floorplan-selector:checked');
            if (selected.length === 0) {
                this.showNotification('Please select floorplans to delete', 'warning');
                return;
            }
            
            if (!confirm(`Delete ${selected.length} selected floorplan(s)?`)) {
                return;
            }
            
            selected.forEach(checkbox => {
                this.removeFloorplan(checkbox.value);
            });
        }

        updateFloorplanOrder() {
            const container = document.getElementById('floorplan_preview');
            
            if (!container) return;
            
            const items = container.querySelectorAll('.image-item');
            this.floorplanOrder = [];
            
            items.forEach((item, index) => {
                const floorplanId = item.dataset.floorplanId;
                this.floorplanOrder.push(floorplanId);
                
                // Update sequence in the data
                const existingFloorplan = this.existingFloorplans.find(fp => fp.id == floorplanId);
                if (existingFloorplan) {
                    existingFloorplan.sequence = index + 1;
                }
                
                const newFloorplan = this.newFloorplans.find(fp => fp.id == floorplanId);
                if (newFloorplan) {
                    newFloorplan.sequence = index + 1;
                }
                
                // Update visual sequence indicator
                const seqIndicator = item.querySelector('small.text-muted');
                if (seqIndicator) {
                    seqIndicator.textContent = `Seq: ${index + 1}`;
                }
            });
        }

        showNotification(message, type = 'info') {
            // Check if there's a notification function available
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                // Fallback to alert for critical messages
                if (type === 'error' || type === 'warning') {
                    alert(message);
                }
                console.log(`[${type.toUpperCase()}] ${message}`);
            }
        }

        syncFloorplanFormData() {
            // Sync data for new floorplans from form inputs
            this.newFloorplans.forEach((floorplan, index) => {
                const floorplanId = floorplan.tempId || `new_${index}`;

                // Read current values from form inputs
                const categoryInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-category`) ||
                                    document.querySelector(`.floorplan-category[data-floorplan-id="${floorplanId}"]`);
                const uploadLinkInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-document-link`) ||
                                      document.querySelector(`.floorplan-document-link[data-floorplan-id="${floorplanId}"]`);
                const remarksInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-remarks`) ||
                                   document.querySelector(`#floorplan_remarks_${floorplanId}`);
                const bathroomsInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-bathrooms`) ||
                                     document.querySelector(`#floorplan_bathrooms_${floorplanId}`);
                const bedroomsInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-bedrooms`) ||
                                    document.querySelector(`#floorplan_bedrooms_${floorplanId}`);
                const balconyInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-balcony`) ||
                                   document.querySelector(`#floorplan_balcony_${floorplanId}`);
                const storeroomInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-storeroom`) ||
                                     document.querySelector(`#floorplan_storeroom_${floorplanId}`);
                const parkingInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-parking-spaces`) ||
                                   document.querySelector(`#floorplan_parking_spaces_${floorplanId}`);
                const additionalFileInput = document.querySelector(`[data-floorplan-id="${floorplanId}"].floorplan-file-input`) ||
                                           document.querySelector(`input[name="floorplan_additional_attachment_${floorplanId}"]`);

                if (categoryInput) floorplan.category = categoryInput.value || '';
                if (uploadLinkInput) floorplan.upload_link = uploadLinkInput.value || '';
                if (remarksInput) floorplan.remarks = remarksInput.value || '';
                if (bathroomsInput) floorplan.bathrooms = bathroomsInput.value || '';
                if (bedroomsInput) floorplan.bedrooms = bedroomsInput.value || '';
                if (balconyInput) floorplan.balcony = balconyInput.value || '';
                if (storeroomInput) floorplan.storeroom = storeroomInput.value || '';
                if (parkingInput) floorplan.parking_spaces = parkingInput.value || '';
                if (additionalFileInput && additionalFileInput.files && additionalFileInput.files[0]) {
                    floorplan.additional_attachment = additionalFileInput.files[0];
                }
            });
        }

        getFloorplanData() {
            return {
                existing_floorplans: this.existingFloorplans.filter(fp => !this.deletedFloorplans.includes(fp.id))
                    .map(fp => ({
                        id: fp.id,
                        remarks: fp.remarks,
                        bathrooms: fp.bathrooms,
                        bedrooms: fp.bedrooms,
                        balcony: fp.balcony,
                        storeroom: fp.storeroom,
                        parking_spaces: fp.parking_spaces,
                        upload_link: fp.upload_link,
                        category: fp.category,
                        sequence: fp.sequence
                    })),
                new_floorplans: this.newFloorplans.map((fp, index) => ({
                    file: fp.file,
                    remarks: fp.remarks,
                    bathrooms: fp.bathrooms,
                    bedrooms: fp.bedrooms,
                    balcony: fp.balcony,
                    storeroom: fp.storeroom,
                    parking_spaces: fp.parking_spaces,
                    upload_link: fp.upload_link,
                    category: fp.category,
                    additional_attachment: fp.additional_attachment,
                    sequence: fp.sequence || (this.existingFloorplans.length + index + 1)
                })),
                deleted_floorplans: this.deletedFloorplans,
                floorplan_order: this.floorplanOrder
            };
        }
    }

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
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.initializeComponents();
                });
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
            const setPrimaryBtn = document.getElementById('set_primary_image');

            // File input change
            if (fileInput) {
                fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
            } else {
                console.warn('File input element not found');
            }

            // Upload zone click
            if (uploadZone && fileInput) {
                uploadZone.addEventListener('click', (e) => {
                    // Prevent clicking on the file input from triggering twice
                    if (e.target !== fileInput && !e.target.closest('input')) {
                        fileInput.click();
                    }
                });
            } else {
                console.warn('Upload zone not found');
            }

            // Bulk actions
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => this.selectAllImages());
            }

            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', () => this.deleteSelected());
            }

            if (setPrimaryBtn) {
                setPrimaryBtn.addEventListener('click', () => this.setPrimaryImage());
            }
        }

        initDragDrop() {
            const uploadZone = document.getElementById('gallery_upload_zone') ||
                            document.querySelector('.image-upload-zone');

            if (!uploadZone) {
                console.warn('Upload zone not found for drag-drop initialization');
                return;
            }

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadZone.addEventListener(eventName, () => {
                    uploadZone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, () => {
                    uploadZone.classList.remove('dragover');
                });
            });

            uploadZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                this.handleFiles(files);
            });
        }

        loadExistingGallery() {
            if (!this.projectId) {
                console.log('No project ID found, skipping gallery load');
                return;
            }

            // Check if jQuery is available
            if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
                console.error('jQuery is required for AJAX calls');
                return;
            }

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value;

            $.ajax({
                url: '{{ route( 'admin.project.getGallery' ) }}',
                type: 'POST',
                data: {
                    project_id: this.projectId,
                    _token: csrfToken
                },
                success: (response) => {
                    // Handle both response formats
                    let galleries = [];

                    // Check if response is direct array or has galleries property
                    if (Array.isArray(response)) {
                        galleries = response;
                    } else if (response.success && response.galleries) {
                        galleries = response.galleries;
                    } else if (response.data) {
                        galleries = response.data;
                    }

                    // Process galleries with proper structure
                    this.existingImages = galleries.map(gallery => ({
                        id: gallery.id,
                        image_url: gallery.image_url,
                        image_path: gallery.image_path,
                        remarks: gallery.remarks || '',
                        sequence: gallery.sequence || 0,
                        is_primary: gallery.is_primary || false,
                        is_new: false
                    }));

                    // Sort by sequence if available
                    this.existingImages.sort((a, b) => a.sequence - b.sequence);

                    // Set primary image if exists
                    const primaryImage = this.existingImages.find(img => img.is_primary);
                    if (primaryImage) {
                        this.primaryImageId = primaryImage.id;
                    }

                    console.log('Loaded existing images:', this.existingImages);
                    this.renderAllImages();
                    this.updateStats();
                },
                error: (error) => {
                    console.error('Error loading gallery:', error);
                    // Continue without existing images
                    this.renderAllImages();
                    this.updateStats();
                }
            });
        }

        handleFiles(files) {
            if (!files || files.length === 0) return;

            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    this.addNewImage(file);
                } else {
                    console.warn('File type not supported:', file.type);
                }
            });
        }

        addNewImage(file) {
            const imageId = `new_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

            const imageObj = {
                id: imageId,
                file: file,
                url: URL.createObjectURL(file),
                remarks: '',
                sequence: this.existingImages.length + this.newImages.length + 1,
                is_primary: this.newImages.length === 0 && this.existingImages.length === 0,
                is_new: true
            };

            // If this is the first image, make it primary
            if (imageObj.is_primary) {
                this.primaryImageId = imageId;
            }

            this.newImages.push(imageObj);
            this.renderAllImages();
            this.updateStats();
            this.initSortable();
        }

        renderAllImages() {
            const container = document.getElementById('all_images_container') ||
                            document.getElementById('image_preview');

            if (!container) {
                console.warn('Image container not found');
                return;
            }

            container.innerHTML = '';
            // Filter out deleted images
            const activeExistingImages = this.existingImages.filter(img => !this.deletedImages.includes(img.id));

            // Combine existing and new images
            const allImages = [...activeExistingImages, ...this.newImages];

            // Sort by sequence first, then by primary status
            allImages.sort((a, b) => {
                // Primary image always first
                if (a.is_primary && !b.is_primary) return -1;
                if (!a.is_primary && b.is_primary) return 1;

                // Then by sequence
                return (a.sequence || 0) - (b.sequence || 0);
            });

            allImages.forEach(image => {
                this.renderImageItem(image, container);
            });

            this.initSortable();
        }

        renderImageItem(image, container) {
            const isPrimary = image.id == this.primaryImageId;
            const imageUrl = image.image_url || image.image_path || image.url;

            const html = `
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card image-item" data-image-id="${image.id}">
                        <div class="image-controls">
                            <div class="form-check">
                                <input class="form-check-input image-checkbox" type="checkbox" value="${image.id}">
                            </div>
                            ${isPrimary ? '<span class="badge bg-warning position-absolute top-0 end-0 m-2">Primary</span>' : ''}
                        </div>
                        <div class="card-img-top position-relative">
                            <img src="${imageUrl}" alt="Image" class="img-fluid" style="height: 200px; width: 100%; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-0 m-2 delete-image" data-image-id="${image.id}">
                                <i class="ni ni-trash"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm image-remarks"
                                       placeholder="Add remarks..." value="${image.remarks || ''}"
                                       data-image-id="${image.id}">
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary set-primary-single"
                                        data-image-id="${image.id}" ${isPrimary ? 'disabled' : ''}>
                                    ${isPrimary ? 'Primary' : 'Set Primary'}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info preview-image"
                                        data-image-src="${imageUrl}">
                                    Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);

            // Bind events for this item
            const item = container.lastElementChild;

            // Delete button
            item.querySelector('.delete-image').addEventListener('click', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                this.deleteImage(imageId);
            });

            // Set primary button
            item.querySelector('.set-primary-single').addEventListener('click', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                this.setPrimaryImageById(imageId);
            });

            // Remarks input
            item.querySelector('.image-remarks').addEventListener('blur', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                const remarks = e.currentTarget.value;
                this.updateImageRemarks(imageId, remarks);
            });
        }

        initSortable() {
            const container = document.getElementById('all_images_container') ||
                            document.getElementById('image_preview');

            if (!container) {
                console.warn('Image container not found for sortable initialization');
                return;
            }

            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }

            if (typeof Sortable !== 'undefined') {
                this.sortableInstance = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.card',
                    onEnd: (evt) => {
                        this.updateImageOrder();
                    }
                });
            } else {
                console.warn('Sortable library not loaded');
            }
        }

        updateImageOrder() {
            const container = document.getElementById('all_images_container') ||
                            document.getElementById('image_preview');

            if (!container) return;

            const items = container.querySelectorAll('.image-item');
            this.imageOrder = [];

            items.forEach((item, index) => {
                const imageId = item.dataset.imageId;
                this.imageOrder.push(imageId);

                // Update sequence in the data
                const existingImage = this.existingImages.find(img => img.id == imageId);
                if (existingImage) {
                    existingImage.sequence = index + 1;
                }

                const newImage = this.newImages.find(img => img.id == imageId);
                if (newImage) {
                    newImage.sequence = index + 1;
                }
            });
        }

        deleteImage(imageId) {
            // Check if it's an existing image
            const existingImageIndex = this.existingImages.findIndex(img => img.id == imageId);
            if (existingImageIndex !== -1) {
                this.deletedImages.push(imageId);
            }

            // Check if it's a new image
            const newImageIndex = this.newImages.findIndex(img => img.id == imageId);
            if (newImageIndex !== -1) {
                // Revoke the object URL to free memory
                URL.revokeObjectURL(this.newImages[newImageIndex].url);
                this.newImages.splice(newImageIndex, 1);
            }

            // If this was the primary image, clear primary
            if (this.primaryImageId == imageId) {
                this.primaryImageId = null;
            }

            this.renderAllImages();
            this.updateStats();
        }

        updateImageRemarks(imageId, remarks) {
            // Update existing image
            const existingImage = this.existingImages.find(img => img.id == imageId);
            if (existingImage) {
                existingImage.remarks = remarks;
                return;
            }

            // Update new image
            const newImage = this.newImages.find(img => img.id == imageId);
            if (newImage) {
                newImage.remarks = remarks;
            }
        }

        setPrimaryImageById(imageId) {
            this.primaryImageId = imageId;

            // Update existing images
            this.existingImages.forEach(img => {
                img.is_primary = img.id == imageId;
            });

            // Update new images
            this.newImages.forEach(img => {
                img.is_primary = img.id == imageId;
            });

            this.renderAllImages();
            this.updateStats();
            this.showNotification('Primary image set successfully', 'success');
        }

        updateStats() {
            const totalImages = this.existingImages.filter(img => !this.deletedImages.includes(img.id)).length + this.newImages.length;
            const primaryStatus = this.primaryImageId ? 'Set' : 'Not Set';

            const totalImagesElement = document.getElementById('total_images_count');
            const primaryStatusElement = document.getElementById('primary_image_status');

            if (totalImagesElement) totalImagesElement.textContent = totalImages;
            if (primaryStatusElement) primaryStatusElement.textContent = primaryStatus;

            // Enable/disable bulk action buttons
            const selectAllBtn = document.getElementById('select_all_images');
            const deleteSelectedBtn = document.getElementById('delete_selected');
            const setPrimaryBtn = document.getElementById('set_primary_image');

            const hasImages = totalImages > 0;
            if (selectAllBtn) selectAllBtn.disabled = !hasImages;
            if (deleteSelectedBtn) deleteSelectedBtn.disabled = !hasImages;
            if (setPrimaryBtn) setPrimaryBtn.disabled = !hasImages;
        }

        getGalleryData() {
            return {
                existing_images: this.existingImages.filter(img => !this.deletedImages.includes(img.id))
                    .map(img => ({
                        id: img.id,
                        remarks: img.remarks,
                        sequence: img.sequence,
                        is_primary: img.id == this.primaryImageId
                    })),
                new_images: this.newImages.map((img, index) => ({
                    file: img.file,
                    remarks: img.remarks,
                    sequence: img.sequence || (this.existingImages.length + index + 1),
                    is_primary: img.id == this.primaryImageId
                })),
                deleted_images: this.deletedImages,
                image_order: this.imageOrder,
                primary_image_id: this.primaryImageId
            };
        }

        showNotification(message, type = 'info') {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                console.log(`[${type.toUpperCase()}] ${message}`);
            }
        }

        // Bulk action methods
        selectAllImages() {
            const checkboxes = document.querySelectorAll('.image-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        }

        deleteSelected() {
            const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                this.showNotification('No images selected', 'warning');
                return;
            }

            if (confirm(`Delete ${selectedIds.length} selected image(s)?`)) {
                selectedIds.forEach(id => this.deleteImage(id));
                this.showNotification(`${selectedIds.length} image(s) deleted`, 'success');
            }
        }

        setPrimaryImage() {
            const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');

            if (selectedCheckboxes.length !== 1) {
                this.showNotification('Please select exactly one image to set as primary', 'warning');
                return;
            }

            const imageId = selectedCheckboxes[0].value;
            this.setPrimaryImageById(imageId);
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        // Make it globally accessible if needed
        window.projectGallery = new ProjectGallery();
        window.projectFloorplan = new ProjectFloorplan();
    });
</script>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
// Multiple CKEditor elements for different languages
window.cke_elements = ['description_en', 'description_zh_tw', 'description_id', 'description_zh_cn', 'description_ja', 'description_ko'];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multiple.js' ) }}"></script>