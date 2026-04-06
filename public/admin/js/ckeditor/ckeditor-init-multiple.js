function UploadAdapterPlugin( editor ) {
    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
        return new UploadAdapter( loader, window.ckeupload_path, window.csrf_token );
    };
}

// Initialize CKEditor for multiple elements
function initializeMultipleCKEditors() {
    // Initialize global editors object to store all editor instances
    window.editors = {};
    
    // Check if we have multiple elements defined
    if (window.cke_elements && Array.isArray(window.cke_elements)) {
        // Initialize each CKEditor element
        window.cke_elements.forEach(function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                ClassicEditor
                .create(element, {
                    licenseKey: '',
                    extraPlugins: [ UploadAdapterPlugin ],
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'underline',
                            'strikethrough',
                            '|',
                            'bulletedList',
                            'numberedList',
                            '|',
                            'outdent',
                            'indent',
                            '|',
                            'alignment',
                            '|',
                            'link',
                            'insertImage',
                            'imageUpload',
                            'mediaEmbed',
                            '|',
                            'blockQuote',
                            'insertTable',
                            '|',
                            'fontFamily',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'sourceEditing',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en',
                    image: {
                        toolbar: [
                            'imageTextAlternative',
                            'imageStyle:inline',
                            'imageStyle:block',
                            'imageStyle:side'
                        ]
                    },
                    table: {
                        contentToolbar: [
                            'tableColumn',
                            'tableRow',
                            'mergeTableCells'
                        ]
                    }
                })
                .then(editor => {
                    // Store editor instance with element ID as key
                    window.editors[elementId] = editor;
                    console.log('CKEditor initialized for:', elementId);
                })
                .catch(error => {
                    console.error('Failed to initialize CKEditor for', elementId, ':', error);
                });
            } else {
                console.warn('Element not found:', elementId);
            }
        });
    }
    // Fallback to single editor initialization for backward compatibility
    else if (window.cke_element1) {
        const element = document.getElementById(window.cke_element1);
        if (element) {
            ClassicEditor
            .create(element, {
                licenseKey: '',
                extraPlugins: [ UploadAdapterPlugin ],
            })
            .then(editor => {
                window.editor = editor;
                window.editors = { [window.cke_element1]: editor };
            })
            .catch(error => {
                console.error('Oops, something went wrong!');
                console.error('Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:');
                console.warn('Build id: nhwyd6r0s6k-qyh8t72ssh4f');
                console.error(error);
            });
        }
    }
}

// Wait for DOM to be ready, then initialize editors
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMultipleCKEditors);
} else {
    // DOM is already ready
    initializeMultipleCKEditors();
}