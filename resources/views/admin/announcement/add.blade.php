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
</style>
<?php
$announcement_create = 'announcement_create';
$discountTypes = $data['discount_types'];
$voucherTypes = $data['voucher_type'];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.announcements' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3">
                    <label>{{ __( 'announcement.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $announcement_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                {{-- announcement custom --}}
                <div class="mb-3 d-none">
                    <label>{{ __( 'announcement.unclaimed_image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $announcement_create }}_unclaimed_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 d-none">
                    <label>{{ __( 'announcement.claiming_image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $announcement_create }}_claiming_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-6 d-none">
                    <label>{{ __( 'announcement.claimed_image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $announcement_create }}_claimed_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mt-5 mb-3 row d-none ">
                    <label for="{{ $announcement_create }}_voucher_type" class="col-sm-5 col-form-label">{{ __( 'announcement.voucher_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $announcement_create }}_voucher_type">
                            @forEach( $voucherTypes as $key => $announcementType )
                                <option value="{{ $key }}">{{ $announcementType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row d-none" >
                    <label for="{{ $announcement_create }}_discount_type" class="col-sm-5 col-form-label">{{ __( 'announcement.discount_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $announcement_create }}_discount_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'announcement.discount_type' ) ] ) }}</option>
                            @forEach( $discountTypes as $key => $discountType )
                                <option value="{{ $key }}">{{ $discountType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <section id="bxgy" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'announcement.buy' ) }}</h5>
                                            <small>{!!__( 'announcement.buy_description' )!!}</small>
                                        </div>
                                        <div class="col-sm-3">
                                            <select class="form-select form-select-sm" id="{{ $announcement_create }}_bxgy_buy_products" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'announcement.buy_products' ) ] ) }}" multiple>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'announcement.discount' ) }}</h5>
                                            <small>{!!__( 'announcement.discount_description' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'announcement.buy_quantity' ) }}</legend>
                                                        <input type="number" class="form-control form-control-sm" id="{{ $announcement_create }}_bxgy_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'announcement.get_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $announcement_create }}_bxgy_get_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                            <div class="col">
                                                                <select class="form-select form-select-sm" id="{{ $announcement_create }}_bxgy_get_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'announcement.get_product' ) ] ) }}">
                                                                </select>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="cartd" class="rule-section hidden mb-3 row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <div class="col-sm-3">
                                            <h5>{{ __( 'announcement.discount' ) }}</h5>
                                            <small>{!!__( 'announcement.discount_description' )!!}</small>
                                        </div>
                                        <div class="col-sm">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'announcement.buy_quantity_rm' ) }}</legend>
                                                        <input type="number" class="form-control form-control-sm" id="{{ $announcement_create }}_cartd_buy_quantity">
                                                        <div class="invalid-feedback"></div>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-12 col-md-8">
                                                    <fieldset class="border p-2">
                                                        <legend class="mb-0 fs-6 float-none w-auto">{{ __( 'announcement.discount_quantity' ) }}</legend>
                                                        <div class="row">
                                                            <div class="col">
                                                                <input type="number" class="form-control form-control-sm" id="{{ $announcement_create }}_cartd_discount_quantity">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create }}_new_user_only" class="col-sm-5 col-form-label">{{ __( 'announcement.new_user_only' ) }}</label>
                    <div class="col-sm-7 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $announcement_create }}_new_user_only">
                        </div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create }}_view_once" class="col-sm-5 col-form-label">{{ __( 'announcement.view_once' ) }}</label>
                    <div class="col-sm-7 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $announcement_create }}_view_once">
                        </div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create}}_points_required" class="col-sm-5 col-form-label">{{ __( 'announcement.points_required' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $announcement_create}}_points_required" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create}}_validity_days" class="col-sm-5 col-form-label">{{ __( 'announcement.validity_days' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $announcement_create}}_validity_days">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $announcement_create }}_promo_code" class="col-sm-5 col-form-label">{{ __( 'announcement.promo_code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $announcement_create }}_promo_code">
                        <div class="invalid-feedback"></div>
                    </div>
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
                                <label class="form-label">{{ __( 'announcement.title' ) }} (English)</label>
                                <input type="text" class="form-control" id="title_en">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (English)</label>
                                <textarea class="form-control" id="description_en" name="description_en"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Traditional Chinese Content -->
                        <div class="tab-pane fade" id="lang-zh_tw" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.title' ) }} (繁体中文)</label>
                                <input type="text" class="form-control" id="title_zh_tw">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (繁体中文)</label>
                                <textarea class="form-control" id="description_zh_tw" name="description_zh_tw"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Indonesian Content -->
                        <div class="tab-pane fade" id="lang-id" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.title' ) }} (Bahasa Indonesia)</label>
                                <input type="text" class="form-control" id="title_id">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (Bahasa Indonesia)</label>
                                <textarea class="form-control" id="description_id" name="description_id"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Simplified Chinese Content -->
                        <div class="tab-pane fade" id="lang-zh_cn" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.title' ) }} (简体中文)</label>
                                <input type="text" class="form-control" id="title_zh_cn">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (简体中文)</label>
                                <textarea class="form-control" id="description_zh_cn" name="description_zh_cn"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Japanese Content -->
                        <div class="tab-pane fade" id="lang-ja" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.title' ) }} (日本語)</label>
                                <input type="text" class="form-control" id="title_ja">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (日本語)</label>
                                <textarea class="form-control" id="description_ja" name="description_ja"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Korean Content -->
                        <div class="tab-pane fade" id="lang-ko" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.title' ) }} (한국어)</label>
                                <input type="text" class="form-control" id="title_ko">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __( 'announcement.description' ) }} (한국어)</label>
                                <textarea class="form-control" id="description_ko" name="description_ko"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $announcement_create}}_start_date" class="col-sm-5 col-form-label">{{ __( 'announcement.start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $announcement_create}}_start_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $announcement_create}}_expired_date" class="col-sm-5 col-form-label">{{ __( 'announcement.expired_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $announcement_create}}_expired_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $announcement_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $announcement_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.announcement.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
// Multiple CKEditor elements for different languages
window.cke_elements = ['description_en', 'description_zh_tw', 'description_id', 'description_zh_cn', 'description_ja', 'description_ko'];
</script>
<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init-multiple.js' ) }}"></script>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $announcement_create }}',
                fileID = '',
                fileID1 = '',
                fileID2 = '',
                fileID3 = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.announcement.index' ) }}';
        } );

        $( fc + '_start_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fc + '_expired_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            let type = $( fc + '_discount_type' ).val();

            let data = {};

            if( type == 3 ){
                data = bxgyData( data );
            }else {
                data = cartdData( data );
            }

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            // Collect announcement translations
            const languages = ['en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko'];
            const announcementTranslations = {};
            languages.forEach(function(lang) {
                // Get CKEditor data for this language
                let descriptionData = '';
                if (window.editors && window.editors[`description_${lang}`]) {
                    descriptionData = window.editors[`description_${lang}`].getData();
                }
                
                announcementTranslations[lang] = {
                    title: $(`#title_${lang}`).val() || '',
                    description: descriptionData || ''
                };
            });
            formData.append('translations', JSON.stringify(announcementTranslations));
            formData.append( 'view_once', $(fc + '_view_once').is(':checked') ? 1 : 0 );
            formData.append( 'new_user_only', $(fc + '_new_user_only').is(':checked') ? 1 : 0 );
            formData.append( 'promo_code', $( fc + '_promo_code' ).val() );
            formData.append( 'discount_type', type );
            formData.append( 'voucher_type', $( fc + '_voucher_type' ).val() );
            formData.append( 'points_required', $( fc + '_points_required' ).val() );
            formData.append( 'start_date', $( fc + '_start_date' ).val() );
            formData.append( 'expired_date', $( fc + '_expired_date' ).val() );
            formData.append( 'validity_days', $( fc + '_validity_days' ).val() );
            formData.append( 'claim_per_user', $( fc + '_claim_per_user' ).val() );
            formData.append( 'image', fileID );
            formData.append( 'unclaimed_image', fileID1 );
            formData.append( 'claiming_image', fileID2 );
            formData.append( 'claimed_image', fileID3 );
            formData.append( 'adjustment_data', JSON.stringify(data) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.announcement.createAnnouncement' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.announcement.index' ) }}';
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
        const dropzone = new Dropzone( fc + '_image', { 
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

        const dropzone1 = new Dropzone( fc + '_unclaimed_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID1.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID1 = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID1 !== '' ) {
                        fileID1 += ','; // Add a comma if fileID is not empty
                    }
                    fileID1 += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );

        const dropzone2 = new Dropzone( fc + '_claiming_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID2.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID2 = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID2 !== '' ) {
                        fileID2 += ','; // Add a comma if fileID is not empty
                    }
                    fileID2 += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );

        const dropzone3 = new Dropzone( fc + '_claimed_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID3.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID3 = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID3 !== '' ) {
                        fileID3 += ','; // Add a comma if fileID is not empty
                    }
                    fileID3 += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );

        $( fc + '_discount_type' ).change( function() {
            $( '.rule-section' ).addClass( 'hidden' );
            
            switch ( parseInt( $( this ).val() ) ) {
                case 3:
                    $( '#bxgy' ).removeClass( 'hidden' );
                    $( '#cartd' ).addClass( 'hidden' );
                    break;

                default:
                    $( '#cartd' ).removeClass( 'hidden' );
                    $( '#bxgy' ).addClass( 'hidden' );
                    break;
            }
        } );

        $( fc + '_bxgy_buy_products' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
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
            }
        } );

        $( fc + '_bxgy_get_product' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
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
            }
        } );

        function bxgyData( data ) {

            data['buy_products'] = [];
            let selected = $( fc + '_bxgy_buy_products' ).find(':selected');
            for ( let i = 0; i < selected.length; i++ ) {
                const element = selected[i];
                data['buy_products'].push( $( element ).val() );
            }

            data['buy_quantity'] = $( fc + '_bxgy_buy_quantity' ).val();
            data['get_quantity'] = $( fc + '_bxgy_get_quantity' ).val();
            data['get_product'] = $( fc + '_bxgy_get_product' ).val();

            return data;
        }

        function cartdData( data ) {

            data['buy_quantity'] = $( fc + '_cartd_buy_quantity' ).val();
            data['discount_quantity'] = $( fc + '_cartd_discount_quantity' ).val();

            return data;
        }

        function bxgyValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fc + '_bxgy_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

        function cartdValidation( error ) {
            let errors = error.responseJSON.errors;
            $.each( errors, function( key, value ) {
                $( fc + '_cartd_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
            } );
        }

    } );
</script>