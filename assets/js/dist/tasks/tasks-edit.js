$('#task_breaker-edit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";

    var taskDescriptionObject = tinymce.get( 'task_breakerTaskEditDescription' );

    if ( taskDescriptionObject ) {

        taskDescription = taskDescriptionObject.getContent();

    } else {

        taskDescription = $('#task_breakerTaskEditDescription').val();
        
    }

    var httpRequestParameters = {
        description: taskDescription,
        nonce: task_breakerProjectSettings.nonce,
        project_id: task_breakerTaskConfig.currentProjectId,
        user_id: task_breakerTaskConfig.currentUserId,

        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transaction_edit_ticket',

        title: $('#task_breakerTaskEditTitle').val(),
        milestone_id: $('#task_breakerTaskMilestone').val(),
        id: $('#task_breakerTaskId').val(),
        priority: $('select[name="task_breaker-task-edit-priority"]').val(),
        user_id_collection: $('select#task-user-assigned-edit').val()
    }

    $.ajax({

        url: ajaxurl,
        data: httpRequestParameters,

        method: 'post',

        success: function( response ) {

            var message = "<p class='task-breaker-message success'>Task successfully updated <a href='#tasks/view/" + response.id + "'>&#65515; View</a></p>";

            if ( 'fail' === response.message && 'no_changes' !== response.type ) {

                message = "<p class='task-breaker-message danger'>There was an error updating the task. All fields are required.</a></p>";

            }

            if ( 'fail' === response.message && 'unauthorized' === response.type ) {

                message = "<p class='task-breaker-message danger'>You are not allowed to modify this task. Only group project administrators and group projects moderators are allowed.</a></p>";

            }

            $('#task_breaker-edit-task-message').html( message ).show();

            element.attr('disabled', false);

            element.text('Update Task');

            return;

        },

        error: function() {

            // Todo: Better handling of http errors and timeouts.
            console.log('An Error Occured [task_breaker.js]#311');

            return;
        }
    });
}); // end $('#task_breaker-edit-btn').click()
