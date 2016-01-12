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
            user_id: task_breakerTaskConfig.currentUserId
        },

        method: 'post',

        success: function( message ) {

            // Total tasks view.
            var total_tasks = parseInt( $('.task_breaker-total-tasks').text().trim() );

            // Remaining tasks view
            var remaining_tasks = parseInt( $('.task_breaker-remaining-tasks-count').text().trim() );

            message = JSON.parse( message );

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
