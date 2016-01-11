$('#thrive-submit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var __taskEditor = tinymce.get( 'thriveTaskDescription' );

    if ( __taskEditor ) {
       taskDescription =  __taskEditor.getContent();
    } else {
       taskDescription = $( '#thriveTaskDescription' ).val();
    }

    $.ajax({
        url: ajaxurl,
        data: {
            
            action: 'thrive_transactions_request',
            method: 'thrive_transaction_add_ticket',
            
            description: taskDescription,
            
            title: $('#thriveTaskTitle').val(),
            milestone_id: $('#thriveTaskMilestone').val(),
            priority: $('#thrive-task-priority-select').val(),

            nonce: thriveProjectSettings.nonce,

            project_id: thriveTaskConfig.currentProjectId,
            user_id: thriveTaskConfig.currentUserId
        },

        method: 'post',

        success: function( message ) {

            // Total tasks view.
            var total_tasks = parseInt( $('.thrive-total-tasks').text().trim() );

            // Remaining tasks view
            var remaining_tasks = parseInt( $('.thrive-remaining-tasks-count').text().trim() );

            message = JSON.parse( message );

           // console.log( message ); 

            if ( message.message === 'success' ) {

                element.text('Save Task');

                element.removeAttr('disabled');

                $('#thriveTaskDescription').val('');

                $('#thriveTaskTitle').val('');
                
                ThriveProjectView.updateStats( message.stats );

                location.href = "#tasks/view/" + message.response.id;


            } else {

                $('#thrive-add-task-message').html('<p class="error">'+message.response+'</p>').show().addClass('error');

              

                element.text('Save Task');
                
                element.removeAttr('disabled');

            }
        },
        error: function() {

        }
    }); // end $.ajax
}); // end $('#thrive-submit-btn').click()
