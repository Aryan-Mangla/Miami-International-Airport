document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: 'textarea',
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak formatselect',
        toolbar_mode: 'floating',
        toolbar: 'formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
        setup: function(editor) {
            editor.on('init', function() {
                // Call countWords once when the TinyMCE editor is initialized
                countWords(editor.getContent());
            });
            editor.on('input', function() {
                // Update word count whenever the content changes
                countWords(editor.getContent());
            });
        }
    });
    
    
    

    function countWords(content) {
        // Strip HTML tags
        var text = content.replace(/(<([^>]+)>)/gi, "");
        // Remove leading and trailing whitespace and split the input into words
        var words = text.trim().split(/\s+/).filter(function(word) {
            return word.length > 0; // Filter out empty words
        });

        // Update the word count display
        document.getElementById('wordCount').textContent = 'Word count: ' + words.length;
    }
});
