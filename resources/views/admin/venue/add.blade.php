<?php $venue_create = 'venue_create'; ?>

<style>
    .ck-content ul { list-style-type: disc; margin-left: 20px; }
    .ck-content ol { list-style-type: decimal; margin-left: 20px; }
    .ck-content ul li, .ck-content ol li { display: list-item; }
    .ck-editor__editable_inline { min-height: 200px; }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => 'Venue' ] ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route( 'admin.module_parent.venue.index' ) }}" class="btn btn-outline-secondary btn-sm">
                <em class="icon ni ni-arrow-left"></em> <span>Back to List</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">

        {{-- ── General Info ─────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">General Information</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_name" class="col-sm-3 col-form-label">Name <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_name" placeholder="e.g. XPark Sports Hub KL">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row d-none">
            <label for="{{ $venue_create }}_slug" class="col-sm-3 col-form-label">Slug</label>
            <div class="col-sm-9">
                <input type="text" class="form-control bg-light" id="{{ $venue_create }}_slug" readonly>
                <div class="form-text">Auto-generated from name.</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_description" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
                <textarea class="form-control" style="min-height:80px;" id="{{ $venue_create }}_description" placeholder="Brief description of the venue..."></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Address ──────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Address Details</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_address_1" class="col-sm-3 col-form-label">Address Line 1 <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_address_1" placeholder="Street / unit number">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_address_2" class="col-sm-3 col-form-label">Address Line 2</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_address_2" placeholder="Area / neighbourhood (optional)">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">City &amp; State <span class="text-danger">*</span></label>
            <div class="col-sm-4 pe-sm-1 mb-2 mb-sm-0">
                <input type="text" class="form-control" id="{{ $venue_create }}_city" placeholder="City">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-5 ps-sm-1">
                <select class="form-select" id="{{ $venue_create }}_state">
                    <option value="">— Select State —</option>
                    @foreach( ['Johor','Kedah','Kelantan','Malacca','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya'] as $state )
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_postcode" class="col-sm-3 col-form-label">Postcode</label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="{{ $venue_create }}_postcode" maxlength="10" placeholder="e.g. 50450">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── GPS ──────────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-1">GPS Coordinates <span class="text-muted fw-normal text-lowercase">(optional)</span></h6>
        <p class="text-muted small mb-3">Used for map display in the mobile app.</p>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Latitude / Longitude</label>
            <div class="col-sm-4 pe-sm-1 mb-2 mb-sm-0">
                <input type="number" step="any" class="form-control" id="{{ $venue_create }}_latitude" placeholder="e.g. 3.1570">
                <div class="form-text">-90 to 90</div>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-5 ps-sm-1">
                <input type="number" step="any" class="form-control" id="{{ $venue_create }}_longitude" placeholder="e.g. 101.7120">
                <div class="form-text">-180 to 180</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Cover Image ──────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Cover Image</h6>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Image</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="{{ $venue_create }}_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                <div class="invalid-feedback"></div>
                <div class="mt-2" id="{{ $venue_create }}_image_preview_wrap" style="display:none;">
                    <img id="{{ $venue_create }}_image_preview" src="" class="rounded border" style="max-height:150px; max-width:300px;">
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── About Us ─────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">About Us</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_about_us" class="col-sm-3 col-form-label">About Us</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_create }}_about_us"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Amenities ────────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-1">Amenities</h6>
        <p class="text-muted small mb-3">Select the facilities available at this venue.</p>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_amenities" class="col-sm-3 col-form-label">Amenities</label>
            <div class="col-sm-9">
                <select class="form-select" id="{{ $venue_create }}_amenities" multiple data-placeholder="Search amenities..."></select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Opening Hours ────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Opening Hours</h6>

        <div class="mb-3">
            @foreach( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $day )
            <div class="row align-items-center mb-2">
                <div class="col-sm-3 col-form-label">{{ ucfirst( $day ) }}</div>
                <div class="col-sm-2">
                    <div class="form-check">
                        <input class="form-check-input oh-closed-check" type="checkbox" id="{{ $venue_create }}_oh_{{ $day }}_closed" data-day="{{ $day }}">
                        <label class="form-check-label" for="{{ $venue_create }}_oh_{{ $day }}_closed">Closed</label>
                    </div>
                </div>
                <div class="col-sm-3 pe-sm-1 oh-time-wrap" id="{{ $venue_create }}_oh_{{ $day }}_wrap">
                    <input type="time" class="form-control" id="{{ $venue_create }}_oh_{{ $day }}_open" placeholder="Opens">
                    <div class="form-text">Opens</div>
                </div>
                <div class="col-sm-3 ps-sm-1 oh-time-wrap" id="{{ $venue_create }}_oh_{{ $day }}_wrap2">
                    <input type="time" class="form-control" id="{{ $venue_create }}_oh_{{ $day }}_close" placeholder="Closes">
                    <div class="form-text">Closes</div>
                </div>
            </div>
            @endforeach
        </div>

        <hr class="my-4">

        {{-- ── Opening Hours ─────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Opening Hours</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_opening_hours_pricing" class="col-sm-3 col-form-label">Details</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_create }}_opening_hours_pricing"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Venue Layout ─────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-1">Venue Layout</h6>
        <p class="text-muted small mb-3">Upload a floor plan or layout image of the venue.</p>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Layout Image</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="{{ $venue_create }}_venue_layout" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                <div class="invalid-feedback"></div>
                <div class="mt-2" id="{{ $venue_create }}_venue_layout_preview_wrap" style="display:none;">
                    <img id="{{ $venue_create }}_venue_layout_preview" src="" class="rounded border" style="max-height:200px; max-width:400px;">
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Venue Policy ─────────────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Venue Policy</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_venue_policy" class="col-sm-3 col-form-label">Policy</label>
            <div class="col-sm-9">
                <textarea id="{{ $venue_create }}_venue_policy"></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Navigation & Contact ─────────────────────────────────────────── --}}
        <h6 class="overline-title text-primary-alt mb-3">Navigation &amp; Contact</h6>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_gmap_link" class="col-sm-3 col-form-label">Google Maps Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_gmap_link" placeholder="https://maps.google.com/...">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_waze_link" class="col-sm-3 col-form-label">Waze Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_waze_link" placeholder="https://waze.com/...">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Phone Number</label>
            <div class="col-sm-3 pe-sm-1 mb-2 mb-sm-0">
                <select class="form-select" id="{{ $venue_create }}_calling_code">
                    <option value="+60">+60 Malaysia</option>
                    <option value="+65">+65 Singapore</option>
                    <option value="+62">+62 Indonesia</option>
                    <option value="+66">+66 Thailand</option>
                    <option value="+63">+63 Philippines</option>
                    <option value="+673">+673 Brunei</option>
                    <option value="+84">+84 Vietnam</option>
                    <option value="+95">+95 Myanmar</option>
                    <option value="+855">+855 Cambodia</option>
                    <option value="+856">+856 Laos</option>
                    <option value="+61">+61 Australia</option>
                    <option value="+64">+64 New Zealand</option>
                    <option value="+86">+86 China</option>
                    <option value="+81">+81 Japan</option>
                    <option value="+82">+82 South Korea</option>
                    <option value="+91">+91 India</option>
                    <option value="+44">+44 United Kingdom</option>
                    <option value="+1">+1 USA / Canada</option>
                    <option value="+971">+971 UAE</option>
                    <option value="+966">+966 Saudi Arabia</option>
                    <option value="+880">+880 Bangladesh</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-sm-4 ps-sm-1">
                <input type="text" class="form-control" id="{{ $venue_create }}_phone_number" placeholder="123456789" maxlength="20">
                <div class="form-text">Number (without code)</div>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="{{ $venue_create }}_whatsapp_link" class="col-sm-3 col-form-label">WhatsApp Link</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="{{ $venue_create }}_whatsapp_link" placeholder="https://wa.me/60123456789">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="text-end mt-2">
            <a href="{{ route( 'admin.module_parent.venue.index' ) }}" class="btn btn-outline-secondary me-1">{{ __( 'template.cancel' ) }}</a>
            <button id="{{ $venue_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>

    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vc = '#{{ $venue_create }}';

        // Auto-slug from name
        $( vc + '_name' ).on( 'input', function() {
            $( vc + '_slug' ).val(
                $( this ).val().toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' )
            );
        } );

        // Amenities Select2
        $( vc + '_amenities' ).select2( {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search amenities...',
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.sports_tag.all' ) }}',
                dataType: 'json',
                delay: 250,
                data: function( params ) {
                    return {
                        name:   params.term,
                        start:  0,
                        length: 30,
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
            }
        } );

        // Closed-day toggle for opening hours
        $( '.oh-closed-check' ).on( 'change', function() {
            let day = $( this ).data( 'day' );
            let wrap1 = $( '#' + vc.slice(1) + '_oh_' + day + '_wrap' );
            let wrap2 = $( '#' + vc.slice(1) + '_oh_' + day + '_wrap2' );
            if ( $( this ).is( ':checked' ) ) {
                wrap1.hide();
                wrap2.hide();
            } else {
                wrap1.show();
                wrap2.show();
            }
        } );

        // Venue layout preview
        $( vc + '_venue_layout' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( vc + '_venue_layout_preview' ).attr( 'src', e.target.result );
                    $( vc + '_venue_layout_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( vc + '_venue_layout_preview_wrap' ).hide();
            }
        } );

        // Image preview
        $( vc + '_image' ).on( 'change', function() {
            let file = this.files[0];
            if ( file ) {
                let reader = new FileReader();
                reader.onload = function( e ) {
                    $( vc + '_image_preview' ).attr( 'src', e.target.result );
                    $( vc + '_image_preview_wrap' ).show();
                };
                reader.readAsDataURL( file );
            } else {
                $( vc + '_image_preview_wrap' ).hide();
            }
        } );

        $( vc + '_submit' ).click( function() {

            resetInputValidation();
            $( 'body' ).loading( { message: '{{ __( 'template.loading' ) }}' } );

            let formData = new FormData();
            formData.append( 'name',           $( vc + '_name' ).val() );
            formData.append( 'slug',           $( vc + '_slug' ).val() );
            formData.append( 'description',    $( vc + '_description' ).val() );
            formData.append( 'about_us',               window.editor_venue_about_us   ? window.editor_venue_about_us.getData()   : $( vc + '_about_us' ).val() );
            formData.append( 'opening_hours_pricing', window.editor_venue_ohp         ? window.editor_venue_ohp.getData()         : $( vc + '_opening_hours_pricing' ).val() );
            formData.append( 'venue_policy',   window.editor_venue_policy ? window.editor_venue_policy.getData() : $( vc + '_venue_policy' ).val() );
            formData.append( 'gmap_link',      $( vc + '_gmap_link' ).val() );
            formData.append( 'waze_link',      $( vc + '_waze_link' ).val() );
            formData.append( 'calling_code',   $( vc + '_calling_code' ).val() );
            formData.append( 'phone_number',   $( vc + '_phone_number' ).val() );
            formData.append( 'whatsapp_link',  $( vc + '_whatsapp_link' ).val() );
            formData.append( 'address_1',      $( vc + '_address_1' ).val() );
            formData.append( 'address_2',      $( vc + '_address_2' ).val() );
            formData.append( 'city',           $( vc + '_city' ).val() );
            formData.append( 'state',          $( vc + '_state' ).val() );
            formData.append( 'postcode',       $( vc + '_postcode' ).val() );
            formData.append( 'latitude',       $( vc + '_latitude' ).val() );
            formData.append( 'longitude',      $( vc + '_longitude' ).val() );

            // Amenities (Select2)
            let amenities = $( vc + '_amenities' ).val() || [];
            amenities.forEach( function( id ) { formData.append( 'amenities[]', id ); } );

            // Opening hours — build JSON
            let days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            let oh = {};
            days.forEach( function( day ) {
                let closed = $( '#{{ $venue_create }}_oh_' + day + '_closed' ).is( ':checked' );
                oh[day] = {
                    closed: closed,
                    open:   closed ? null : $( '#{{ $venue_create }}_oh_' + day + '_open' ).val(),
                    close:  closed ? null : $( '#{{ $venue_create }}_oh_' + day + '_close' ).val(),
                };
            } );
            formData.append( 'opening_hours', JSON.stringify( oh ) );

            let allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            let imageFile = $( vc + '_image' )[0].files[0];
            if ( imageFile ) {
                if ( !allowedTypes.includes( imageFile.type ) ) {
                    $( 'body' ).loading( 'stop' );
                    $( vc + '_image' ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( 'Only image files (JPG, PNG, GIF, WEBP) are allowed.' );
                    return;
                }
                formData.append( 'image', imageFile );
            }

            let layoutFile = $( vc + '_venue_layout' )[0].files[0];
            if ( layoutFile ) {
                if ( !allowedTypes.includes( layoutFile.type ) ) {
                    $( 'body' ).loading( 'stop' );
                    $( vc + '_venue_layout' ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( 'Only image files (JPG, PNG, GIF, WEBP) are allowed.' );
                    return;
                }
                formData.append( 'venue_layout', layoutFile );
            }

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.venue.createVenue' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function() {
                        window.location.href = '{{ route( 'admin.venue.edit' ) }}?id=' + response.data.encrypted_id;
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( vc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script>
document.addEventListener( 'DOMContentLoaded', function() {
    ClassicEditor.create( document.getElementById( '{{ $venue_create }}_about_us' ) )
        .then( function( e ) { window.editor_venue_about_us = e; } )
        .catch( function( e ) { console.error( e ); } );

    ClassicEditor.create( document.getElementById( '{{ $venue_create }}_opening_hours_pricing' ) )
        .then( function( e ) { window.editor_venue_ohp = e; } )
        .catch( function( e ) { console.error( e ); } );

    ClassicEditor.create( document.getElementById( '{{ $venue_create }}_venue_policy' ) )
        .then( function( e ) { window.editor_venue_policy = e; } )
        .catch( function( e ) { console.error( e ); } );
} );
</script>
