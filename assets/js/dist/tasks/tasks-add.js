var client_files = '';

$('#task_breaker-submit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var __taskEditor = tinymce.get( 'task_breakerTaskDescription' );

    if ( __taskEditor ) {
       taskDescription =  __taskEditor.getContent();
    } else {
       taskDescription = $( '#task_breakerTaskDescription' ).val();
    }

    $.ajax({
        url: ajaxurl,
        data: {

            action: 'task_breaker_transactions_request',
            method: 'task_breaker_transaction_add_ticket',

            description: taskDescription,

            title: $('#task_breakerTaskTitle').val(),
            milestone_id: $('#task_breakerTaskMilestone').val(),
            priority: $('select#task_breaker-task-priority-select').val(),

            nonce: task_breakerProjectSettings.nonce,

            project_id: task_breakerTaskConfig.currentProjectId,
            user_id: task_breakerTaskConfig.currentUserId,
            user_id_collection: $('select#task-user-assigned').val(),
            file_attachments: client_files
        },

        method: 'post',

        success: function( message ) {

            // Total tasks view.
            var total_tasks = parseInt( $('.task_breaker-total-tasks').text().trim() );

            // Remaining tasks view
            var remaining_tasks = parseInt( $('.task_breaker-remaining-tasks-count').text().trim() );

           // console.log( message );

            if ( message.message === 'success' ) {

                element.text('Save Task');

                element.removeAttr('disabled');

                $('#task_breakerTaskDescription').val('');

                $('#task_breakerTaskTitle').val('');

                ThriveProjectView.updateStats( message.stats );

                location.href = "#tasks/view/" + message.response.id;


            } else {

                $('#task_breaker-add-task-message').html('<p class="error">'+message.response+'</p>').show().addClass('error');

                element.text('Save Task');

                element.removeAttr('disabled');

            }
        },
        error: function() {

        }
    }); // end $.ajax
}); // end $('#task_breaker-submit-btn').click()

// test
$('#task-breaker-form-file-attachment-field').on('change', function( event ){

    var files = event.target.files;
    var data = new FormData();

    $.each( files, function(key, value) {
        data.append(key, value);
    });

    data.append( 'action', 'task_breaker_transactions_request' );
    data.append( 'method', 'task_breaker_transaction_task_file_attachment' );
    data.append( 'nonce', task_breakerProjectSettings.nonce )

    $.ajax({
        url: task_breakerAjaxUrl,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function( response, textStatus, jqXHR )
        {
            if( typeof response.error === 'undefined' )
            {
                // Success so call function to process the form
                console.log('sucessfully sent the data..');
                console.log('here is the response');
                console.log( response );
                client_files = response.file;
            }
            else
            {
                // Handle errors here
                console.log('ERRORS: ' + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Handle errors here
            console.log('ERRORS: ' + textStatus);
            // STOP LOADING SPINNER
        },
        xhr: function(){
            var myXhr = $.ajaxSettings.xhr();
            var progress = 0;
            if ( myXhr.upload ) {

                // For handling the progress of the upload
                $('#tb-file-attachment-progress').addClass('active');
                $('#task_breaker-submit-btn').attr('disabled', true);

                myXhr.upload.addEventListener('progress', function(e) {

                    if ( e.lengthComputable ) {
                        $('progress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                        progress = ( e.loaded / e.total ) * 100;
                        if ( typeof progress === 'number' ) {
                            $('#tb-file-attachment-progress-movable').css({
                                width: Math.floor( progress ) + '%'
                            });
                        }
                    }

                    if ( progress === 100 ) {
                        $('#task_breaker-submit-btn').removeAttr('disabled');
                    }

                } , false );

            }
            return myXhr;
        },
        complete: function() {
            console.log('request complete... stopping the spinner...');
        }
    });

    

});
