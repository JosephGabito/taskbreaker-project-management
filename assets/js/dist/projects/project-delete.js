 $('body').on('click', '#task_breakerDeleteProjectBtn', function() {

     if ( ! confirm( taskbreaker_strings.project_confirm_delete ) ) 
     {
         return;
     }

     var project_id = $('#task_breaker-project-id').val();

     var __http_params = {
         action: 'task_breaker_transactions_request',
         method: 'task_breaker_transactions_delete_project',
         id: project_id,
         nonce: task_breakerProjectSettings.nonce
     };

     $(this).text('Deleting...');

     $.ajax({

         url: ajaxurl,

         method: 'post',

         data: __http_params,

         success: function( response ) {

             if (response.message == 'success') {

                 window.location = response.redirect;

             } else {

                this.error();

             }

             return;

         },

         error: function() {

            alert(taskbreaker_strings.project_error);

         }
     });

 });
