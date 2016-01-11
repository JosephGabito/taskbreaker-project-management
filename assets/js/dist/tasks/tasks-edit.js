$('#thrive-edit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var taskDescriptionObject = tinymce.get( 'thriveTaskEditDescription' );

    if ( taskDescriptionObject ) {
        taskDescription = taskDescriptionObject.getContent();
    } else {
        taskDescription = $('#thriveTaskEditDescription').val();
    }

    $.ajax({

        url: ajaxurl,
        data: {

            description: taskDescription,
            nonce: thriveProjectSettings.nonce,
            project_id: thriveTaskConfig.currentProjectId,
            user_id: thriveTaskConfig.currentUserId,

            action: 'thrive_transactions_request',
            method: 'thrive_transaction_edit_ticket',

            title: $('#thriveTaskEditTitle').val(),
            milestone_id: $('#thriveTaskMilestone').val(),
            id: $('#thriveTaskId').val(),
            priority: $('select[name="thrive-task-edit-priority"]').val()

        }, 

        method: 'post',

        success: function( httpResponse ) {

            var response = JSON.parse( httpResponse );

            var message = "<p class='success'>Task successfully updated <a href='#tasks/view/" + response.id + "'>&#65515; View</a></p>";

            if ('fail' === response.message && 'no_changes' !== response.type) {

                message = "<p class='error'>There was an error updating the task. All fields are required.</a></p>";

            }
 
            $('#thrive-edit-task-message').html(message).show();

            element.attr('disabled', false);

            element.text('Update Task');

            return;

        },
        
        error: function() {

            // Todo: Better handling of http errors and timeouts.
            console.log('An Error Occured [thrive.js]#311');

            return;
        }
    });
}); // end $('#thrive-edit-btn').click()
