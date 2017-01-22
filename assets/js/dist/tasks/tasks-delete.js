 // Delete Task Single
 $('body').on('click', '#task_breaker-delete-btn', function() {

    var _delete_confirm = confirm("Are you sure you want to delete this task? This action is irreversible");

    if (!_delete_confirm) {
       return;
    }

    var $element = $(this);

    var task_id = parseInt( ThriveProjectModel.id );

    var task_project_id = parseInt( ThriveProjectModel.project_id );

    var __http_params = {

       action: 'task_breaker_transactions_request',
       method: 'task_breaker_transaction_delete_ticket',
       id: task_id,
       project_id: task_project_id,
       nonce: task_breakerProjectSettings.nonce

   };

   ThriveProjectView.progress(true);

   $element.text('Deleting ...');

   $.ajax({

       url: ajaxurl,
       data: __http_params,
       method: 'post',
       success: function( response ) {

            ThriveProjectView.progress( false );

            ThriveProjectView.updateStats( response.stats );

            if ( 'fail' === response.message) {

                var message = "<p class='task-breaker-message danger'>"+response.message_text+"</p>";
                
                $('#task_breaker-edit-task-message').html( message ).show();

                return false;

            } else {

                location.href = "#tasks";

                ThriveProjectView.switchView(null, '#task_breaker-project-tasks-context');
                
            }

            $element.text('Delete');

       },

       error: function() {

           ThriveProjectView.progress(false);

           $element.text('Delete');

       }
   });
 }); // End Delete Task
