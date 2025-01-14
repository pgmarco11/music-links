jQuery(function($) {
    var mediaUploader; // Initialize mediaUploader globally
    var image_field; // Store the current input field

    $(document).on('click', '.select-img', function(evt) {
        evt.preventDefault();
        var target = $(this).data('target');  // Get the target input field
        image_field = $('input[name="' + target + '"]');  // Get the corresponding input field by name
        var previewContainer = $('#upload_logo_preview_' + target);  // Preview container for the image

        // Open the media uploader
        openMediaUploader(target, previewContainer);
    });

    function openMediaUploader(target, previewContainer) {
        // If the media uploader is already open, just return
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Initialize the media uploader
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        // When the user selects an image, update the input field and the preview
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            console.log('Selected image URL:', attachment.url);  // Debugging log
            image_field.val(attachment.url);  // Set the URL in the input field
            previewContainer.html('<img style="max-width:100%;" src="' + attachment.url + '" />');
        });

        // Open the media uploader
        mediaUploader.open();
    }
});
