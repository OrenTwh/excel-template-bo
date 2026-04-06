{{--
    Reusable CKEditor article-content partial (multi-language tabs).

    Required variables:
        $prefix  (string) — unique ID prefix, e.g. 'cms_create'

    Fields per language tab:
        title, slug (auto-generated), short_description, content (CKEditor)

    JS globals exposed after render:
        window['{prefix}_editors']  — object keyed by lang code, each is a CKEditor instance
                                      e.g. window['cms_create_editors']['en'].getData()
--}}

<?php $cms_languages = Config::get( 'languages' ); ?>

<style>
    .ck-editor__editable, .ck-content { min-height: 300px; }
    .ck-content ul  { list-style-type: disc;    padding-left: 40px; }
    .ck-content ol  { list-style-type: decimal; padding-left: 40px; }
    .ck-content ul li, .ck-content ol li { display: list-item; }
    .ck-content blockquote {
        font-style: italic;
        border-left: 4px solid #b27c44;
        padding: 12px 20px;
        margin-left: 0;
    }
    .ck-content table  { border-collapse: collapse; width: 100%; }
    .ck-content td, .ck-content th { border: 1px solid #ddd; padding: 8px; }
    .ck-content th { background: #f2f2f2; font-weight: bold; }
    .ck-content p  { margin: 0 0 1em 0; }
    .ck-editor__editable_inline { padding: 0 30px; }
</style>

{{-- Language tabs --}}
<ul class="nav nav-tabs mb-3" id="{{ $prefix }}_lang_tabs">
    @foreach( $cms_languages as $lang => $langName )
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
               data-bs-toggle="tab"
               data-bs-target="#{{ $prefix }}_tab_{{ $lang }}"
               href="#">
                {{ $langName }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content" id="{{ $prefix }}_lang_tab_content">
    @foreach( $cms_languages as $lang => $langName )
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="{{ $prefix }}_tab_{{ $lang }}">

            <div class="mb-3 row">
                <label for="{{ $prefix }}_{{ $lang }}_title" class="col-sm-3 col-form-label">
                    {{ __( 'template.title' ) }}
                    @if( $loop->first ) <span class="text-danger">*</span> @endif
                </label>
                <div class="col-sm-9">
                    <input type="text" class="form-control {{ $prefix }}-title-input"
                           id="{{ $prefix }}_{{ $lang }}_title"
                           data-lang="{{ $lang }}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $prefix }}_{{ $lang }}_slug" class="col-sm-3 col-form-label">
                    {{ __( 'template.slug' ) }}
                </label>
                <div class="col-sm-9">
                    <input type="text" class="form-control bg-light"
                           id="{{ $prefix }}_{{ $lang }}_slug"
                           readonly>
                    <div class="form-text">{{ __( 'template.auto_generated_from_title' ) }}</div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $prefix }}_{{ $lang }}_short_desc" class="col-sm-3 col-form-label">
                    {{ __( 'template.short_description' ) }}
                </label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="{{ $prefix }}_{{ $lang }}_short_desc"
                              rows="3" style="resize: vertical;"></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="{{ $prefix }}_{{ $lang }}_content" class="col-sm-3 col-form-label">
                    {{ __( 'template.content' ) }}
                </label>
                <div class="col-sm-9">
                    <textarea class="form-control {{ $prefix }}-ckeditor"
                              id="{{ $prefix }}_{{ $lang }}_content"
                              data-lang="{{ $lang }}"></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

        </div>
    @endforeach
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script>
document.addEventListener( 'DOMContentLoaded', function() {

    var pfx = '{{ $prefix }}';

    // ── CKEditor upload adapter ───────────────────────────────────────────────
    function UtilsUploadAdapter( loader ) {
        this.loader = loader;
    }
    UtilsUploadAdapter.prototype.upload = function() {
        var self = this;
        return self.loader.file.then( function( file ) {
            return new Promise( function( resolve, reject ) {
                var fd = new FormData();
                fd.append( 'upload', file );
                fd.append( '_token', '{{ csrf_token() }}' );
                fetch( '{{ route( 'admin.utils.ckeUpload' ) }}', { method: 'POST', body: fd } )
                    .then( function( r ) { return r.json(); } )
                    .then( function( data ) {
                        data.url ? resolve( { default: data.url } ) : reject( data.error?.message || 'Upload failed' );
                    } )
                    .catch( reject );
            } );
        } );
    };
    UtilsUploadAdapter.prototype.abort = function() {};

    function UtilsUploadAdapterPlugin( editor ) {
        editor.plugins.get( 'FileRepository' ).createUploadAdapter = function( loader ) {
            return new UtilsUploadAdapter( loader );
        };
    }

    // ── Initialise one CKEditor per language tab ──────────────────────────────
    window[ pfx + '_editors' ] = {};

    document.querySelectorAll( '.' + pfx + '-ckeditor' ).forEach( function( textarea ) {
        var lang = textarea.getAttribute( 'data-lang' );

        ClassicEditor.create( textarea, {
            extraPlugins: [ UtilsUploadAdapterPlugin ],
            removePlugins: [
                'RealTimeCollaborativeEditing', 'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges', 'PresenceList', 'Comments',
                'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination',
                'WProofreader', 'MathType', 'SlashCommand', 'Template',
                'DocumentOutline', 'FormatPainter', 'TableOfContents',
                'PasteFromOfficeEnhanced', 'CaseChange',
            ],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', '|',
                    'link', '|',
                    'bulletedList', 'numberedList',
                    'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'outdent', 'indent', '|',
                    'uploadImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', '|',
                    'undo', 'redo', '|',
                    'sourceEditing',
                ],
                shouldNotGroupWhenFull: true,
            },
        } )
        .then( function( editor ) {
            window[ pfx + '_editors' ][ lang ] = editor;
        } )
        .catch( function( err ) { console.error( err ); } );
    } );

    // ── Auto-generate slug from title ─────────────────────────────────────────
    document.querySelectorAll( '.' + pfx + '-title-input' ).forEach( function( input ) {
        input.addEventListener( 'input', function() {
            var lang = this.getAttribute( 'data-lang' );
            var slug = this.value
                .toLowerCase()
                .replace( /[^a-z0-9\u4e00-\u9fff\uac00-\ud7af\u3040-\u309f]+/g, '-' )
                .replace( /^-+|-+$/g, '' );
            document.getElementById( pfx + '_' + lang + '_slug' ).value = slug;
        } );
    } );

} );
</script>
