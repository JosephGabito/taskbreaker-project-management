/**
 * This global object will hold the string that contains the name of the file attached in to task.
 * @type Object
 */
var taskbreaker_file_attachments = {
	attached_files: ''
};
/**
 * This function serves as a callback function for the file attachment event handler.
 * @param  object event The onchange event callback argument.
 * @return void
 */
var taskbreaker_process_file_attachment = function ( event, container_id, __form_data ) {

    // The upload file event object.
    var files = event.target.files;

    if ( files.length <= 0 ) {
        return;
    }
    
    // The form data.
    var data = new FormData();
    // The unique container that will hold the file attachments.
    var container = '#' + container_id + ' ';
    // The name of the file selected.
    var file_name = event.target.files[0].name;
    // The file errors count
    var file_errors = 0;

    if ( files.length >= 1 ) {
        $.each( files, function() {
            if ( this.size > parseInt( task_breakerProjectSettings.max_file_size ) ) {
                file_errors++;
            }
        });
    }

    if ( file_errors >= 1 ) {
        alert('There was an error uploading your file. File size exceeded the allowed number of bytes per request.');
        return;
    }

    // Change the file name accordingly.
    $( container + '.tasbreaker-file-attached').html( file_name );

    // Append all files into data form data.
    $.each( files, function( key, value ) {
        data.append( key, value );
    });

    // Append __form_data attribute if not empty.
    if ( typeof __form_data !== 'null' ) {
    	$.each( __form_data, function(k, v){
    		data.append(k, v);
    	});
    }

    // Append the action.
    data.append( 'action', 'task_breaker_transactions_request' );
    // Append the method.
    data.append( 'method', 'task_breaker_transaction_task_file_attachment' );
    // Append the nonce.
    data.append( 'nonce', task_breakerProjectSettings.nonce );
    // Remove any existing error messages.
    $( container + '.taskbreaker-upload-error' ).remove();
    // Clear any progress messages.
    $( container + '.taskbreaker-upload-error-text-helper').removeClass('active');
    $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');

    // Begin ajax request.
    $.ajax({
        url: task_breakerAjaxUrl,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files.
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request.
        success: function( response, textStatus, jqXHR )
        {
           
            if( typeof response.error === 'undefined' )
            {   
                if ( response !== 0 ) {

                    if ( response.message === 'fail' ) {
                        taskbreaker_file_attachments.attached_files = '';
                        $( container + '.tb-file-attachment-progress').parent().append('<div class="taskbreaker-upload-error">'+response.response+'</div>');
                        $( container + '.taskbreaker-upload-error-text-helper').addClass('active');
                        $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');
                    } else {
                        taskbreaker_file_attachments.attached_files = response.file;
                        $( container + '.taskbreaker-upload-error').remove();
                        $( container + '.taskbreaker-upload-error-text-helper').removeClass('active');
                        $( container + '.taskbreaker-upload-success-text-helper').addClass('active');
                    }
                    
                } else {
                    $( container + '.taskbreaker-upload-error-text-helper').addClass('active');
                    $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');
                    $( container + '.tb-file-attachment-progress').parent().append('<div class="taskbreaker-upload-error">The application did not received any response from the server. Try uploading smaller files.</div>');
                    taskbreaker_file_attachments.attached_files = '';
                }
                
            }
            else
            {
                // Handle errors here
                console.log('File attachment errors debug: ' + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Handle errors here
            console.log('File attachment errors debug: ' + textStatus);
            // STOP LOADING SPINNER
        },
        xhr: function(){

            var myXhr = $.ajaxSettings.xhr();
            var progress = 0;
            var progress_percentage = '0%';

            if ( myXhr.upload ) {

                // For handling the progress of the upload
                $( container + '.tb-file-attachment-progress-wrap').addClass('active');
                $( '#task_breaker-submit-btn').attr('disabled', true);
                $( '#task_breaker-edit-btn').attr('disabled', true);

                myXhr.upload.addEventListener('progress', function(e) {

                    if ( e.lengthComputable ) {
                        $('progress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                        progress = ( e.loaded / e.total ) * 100;
                        if ( typeof progress === 'number' ) {
                            progress_percentage = Math.floor( progress ) + '%';
                            $( container + '.tb-file-attachment-progress-movable').css({
                                width: progress_percentage
                            });
                            $( container + '.taskbreaker-upload-progress-value').html( progress_percentage );
                        }
                    }

                } , false );

            }
            return myXhr;
        },
        complete: function() {
            console.log('finished');
            $( '#task_breaker-submit-btn').removeAttr( 'disabled' );
            $( '#task_breaker-edit-btn').removeAttr( 'disabled' );
        }
    });
};