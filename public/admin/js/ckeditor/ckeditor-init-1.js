function UploadAdapterPlugin( editor ) {
    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
        return new UploadAdapter( loader, window.ckeupload_path, window.csrf_token );
    };
}

ClassicEditor
.create( document.getElementById( window.cke_element2 ), {
    licenseKey: '',
    extraPlugins: [ UploadAdapterPlugin ],
} )
.then( editor => {
    window.editor1 = editor;
} )
.catch( error => {
    console.error( 'Oops, something went wrong!' );
    console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
    console.warn( 'Build id: nhwyd6r0s6k-qyh8t72ssh4f' );
    console.error( error );
} );