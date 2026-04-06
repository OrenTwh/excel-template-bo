{{--
    Reusable user-info fields partial.

    Required variables:
        $prefix  (string)  — unique ID prefix, e.g. 'user_create' or 'staff_create'

    Required data available in $data:
        $data['calling_codes']   — assoc array  ['+60' => 'Malaysia (+60)', ...]
        $data['select_options']  — array of     [['value' => ..., 'text' => ...], ...]
        $data['multiselect_options']  — array of [['value' => ..., 'text' => ...], ...]

    JS globals exposed after render (keyed by $prefix):
        window['{prefix}_imagePath']        — single dropzone uploaded path (string)
        window['{prefix}_imagePaths']       — multi  dropzone uploaded paths (array)
--}}

<div class="mb-3 row">
    <label for="{{ $prefix }}_fullname" class="col-sm-5 col-form-label">{{ __( 'user.fullname' ) }}</label>
    <div class="col-sm-7">
        <input type="text" class="form-control" id="{{ $prefix }}_fullname">
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'user.phone_number' ) }}</label>
    <div class="col-sm-7">
        <div class="input-group">
            <select class="form-select flex-shrink-0" id="{{ $prefix }}_calling_code" style="max-width: 100px;">
                @foreach( $data['calling_codes'] as $key => $code )
                    <option value="{{ $key }}" {{ $key == '+60' ? 'selected' : '' }}>
                        {{ $key }}
                    </option>
                @endforeach
            </select>
            <input type="text" class="form-control" id="{{ $prefix }}_phone_number">
        </div>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_password" class="col-sm-5 col-form-label">{{ __( 'user.password' ) }}</label>
    <div class="col-sm-7">
        <input type="password" class="form-control" id="{{ $prefix }}_password" autocomplete="new-password">
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_numeric" class="col-sm-5 col-form-label">{{ __( 'template.numeric' ) }}</label>
    <div class="col-sm-7">
        <input type="number" class="form-control" id="{{ $prefix }}_numeric" min="0" step="1" placeholder="0">
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_decimal" class="col-sm-5 col-form-label">{{ __( 'template.decimal' ) }}</label>
    <div class="col-sm-7">
        <input type="number" class="form-control" id="{{ $prefix }}_decimal" min="0" step="0.01" placeholder="0.00">
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_select" class="col-sm-5 col-form-label">{{ __( 'template.select' ) }}</label>
    <div class="col-sm-7">
        <select class="form-select" id="{{ $prefix }}_select" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.select' ) ] ) }}">
            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'template.select' ) ] ) }}</option>
            @foreach( $data['select_options'] as $option )
                <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_multiselect" class="col-sm-5 col-form-label">{{ __( 'template.multiselect' ) }}</label>
    <div class="col-sm-7">
        <select class="form-select" id="{{ $prefix }}_multiselect" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.multiselect' ) ] ) }}" multiple>
            @foreach( $data['multiselect_options'] as $option )
                <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label for="{{ $prefix }}_tags" class="col-sm-5 col-form-label">{{ __( 'template.tags' ) }}</label>
    <div class="col-sm-7">
        <select class="form-select" id="{{ $prefix }}_tags" data-placeholder="{{ __( 'template.search_or_create_tags' ) }}" multiple></select>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label class="col-sm-5 col-form-label">{{ __( 'template.image' ) }}</label>
    <div class="col-sm-7">
        <div id="{{ $prefix }}_dropzone" class="dropzone" style="min-height: 0;">
            <div class="dz-message needsclick">
                <em class="icon ni ni-img fs-3 text-muted d-block mb-1"></em>
                <span class="text-muted small">{{ __( 'template.drop_files_or_click_to_upload' ) }}</span>
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="mb-3 row">
    <label class="col-sm-5 col-form-label">{{ __( 'template.images' ) }}</label>
    <div class="col-sm-7">
        <div id="{{ $prefix }}_multi_dropzone" class="dropzone" style="min-height: 0;">
            <div class="dz-message needsclick">
                <em class="icon ni ni-img fs-3 text-muted d-block mb-1"></em>
                <span class="text-muted small">{{ __( 'template.drop_files_or_click_to_upload' ) }}</span>
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {
        var pfx = '#{{ $prefix }}';

        // ── Select2 (single) ──────────────────────────────────────────────────
        $( pfx + '_select' ).select2( {
            theme:       'bootstrap-5',
            width:       '100%',
            allowClear:  true,
            placeholder: $( pfx + '_select' ).data( 'placeholder' ),
        } );

        // ── Select2 (multi) ───────────────────────────────────────────────────
        $( pfx + '_multiselect' ).select2( {
            theme:         'bootstrap-5',
            width:         '100%',
            allowClear:    true,
            closeOnSelect: false,
            placeholder:   $( pfx + '_multiselect' ).data( 'placeholder' ),
        } );

        // ── Select2 (tags — search + create) ─────────────────────────────────
        $( pfx + '_tags' ).select2( {
            theme:         'bootstrap-5',
            width:         '100%',
            tags:          true,
            closeOnSelect: false,
            placeholder:   $( pfx + '_tags' ).data( 'placeholder' ),
            ajax: {
                method:   'POST',
                url:      '{{ route( 'admin.sports_tag.all' ) }}',
                dataType: 'json',
                delay:    250,
                data: function( params ) {
                    return {
                        name:   params.term,
                        start:  0,
                        length: 20,
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
            },
            createTag: function( params ) {
                let term = $.trim( params.term );
                if ( term === '' ) return null;
                return { id: 'new:' + term, text: term, newTag: true };
            },
        } );

        // ── Dropzone (single) ─────────────────────────────────────────────────
        Dropzone.autoDiscover = false;
        window['{{ $prefix }}_imagePath'] = '';

        new Dropzone( pfx + '_dropzone', {
            url:            '{{ route( 'admin.file.upload' ) }}',
            maxFiles:       1,
            maxFilesize:    3,
            acceptedFiles:  'image/jpg,image/jpeg,image/png,image/webp',
            addRemoveLinks: true,
            headers:        { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function( file, response ) {
                if ( response.status == 200 ) {
                    file.serverPath = response.data.file;
                    window['{{ $prefix }}_imagePath'] = response.data.file;
                }
            },
            removedfile: function( file ) {
                if ( file.serverPath ) window['{{ $prefix }}_imagePath'] = '';
                file.previewElement.remove();
            },
        } );

        // ── Dropzone (multi) ──────────────────────────────────────────────────
        window['{{ $prefix }}_imagePaths'] = [];

        new Dropzone( pfx + '_multi_dropzone', {
            url:            '{{ route( 'admin.file.upload' ) }}',
            maxFilesize:    3,
            acceptedFiles:  'image/jpg,image/jpeg,image/png,image/webp',
            addRemoveLinks: true,
            headers:        { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function( file, response ) {
                if ( response.status == 200 ) {
                    file.serverPath = response.data.file;
                    window['{{ $prefix }}_imagePaths'].push( response.data.file );
                }
            },
            removedfile: function( file ) {
                if ( file.serverPath ) {
                    window['{{ $prefix }}_imagePaths'] = window['{{ $prefix }}_imagePaths'].filter(
                        function( p ) { return p !== file.serverPath; }
                    );
                }
                file.previewElement.remove();
            },
        } );
    } );
</script>
