<?php
$agent_create = 'agent_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.agents' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'agent.agent_information' ) }}</h5>
                
                <div class="mb-3">
                    <label>{{ __( 'agent.profile_picture' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $agent_create }}_profile_picture" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_name" class="col-sm-5 col-form-label">{{ __( 'agent.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $agent_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_nickname" class="col-sm-5 col-form-label">{{ __( 'agent.nickname' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $agent_create }}_nickname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_serial_number" class="col-sm-5 col-form-label">{{ __( 'agent.serial_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $agent_create }}_serial_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <h5 class="card-title mb-4 mt-5">{{ __( 'agent.contact_information' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'user.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <select class="form-select flex-shrink-0" id="{{ $agent_create }}_calling_code" style="max-width: 100px;">
                                <option value="+60" selected>+60</option>
                                <option value="+32">+32</option>
                            </select>
                            <input type="text" class="form-control" id="{{ $agent_create }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div> 

                <h5 class="card-title mb-4 mt-5">{{ __( 'agent.social_media_links' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_whatsapp_link" class="col-sm-5 col-form-label">{{ __( 'agent.whatsapp_link' ) }}</label>
                    <div class="col-sm-7">
                        <input type="url" class="form-control" id="{{ $agent_create }}_whatsapp_link">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_facebook_link" class="col-sm-5 col-form-label">{{ __( 'agent.facebook_link' ) }}</label>
                    <div class="col-sm-7">
                        <input type="url" class="form-control" id="{{ $agent_create }}_facebook_link">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_telegram_link" class="col-sm-5 col-form-label">{{ __( 'agent.telegram_link' ) }}</label>
                    <div class="col-sm-7">
                        <input type="url" class="form-control" id="{{ $agent_create }}_telegram_link">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $agent_create }}_instagram_link" class="col-sm-5 col-form-label">{{ __( 'agent.instagram_link' ) }}</label>
                    <div class="col-sm-7">
                        <input type="url" class="form-control" id="{{ $agent_create }}_instagram_link">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $agent_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $agent_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $agent_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.agent.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( fc + '_name' ).val() );
            formData.append( 'nickname', $( fc + '_nickname' ).val() );
            formData.append( 'serial_number', $( fc + '_serial_number' ).val() );
            formData.append( 'calling_code', $( fc + '_calling_code' ).val() );
            formData.append( 'phone_number', $( fc + '_phone_number' ).val() );
            formData.append( 'whatsapp_link', $( fc + '_whatsapp_link' ).val() );
            formData.append( 'facebook_link', $( fc + '_facebook_link' ).val() );
            formData.append( 'telegram_link', $( fc + '_telegram_link' ).val() );
            formData.append( 'instagram_link', $( fc + '_instagram_link' ).val() );
            formData.append( 'profile_picture', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.agent.createAgent' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.agent.index' ) }}';
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
        const dropzone = new Dropzone( fc + '_profile_picture', { 
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
</script>