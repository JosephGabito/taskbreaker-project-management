 $('body').on('click', '#task_breakerDeleteProjectBtn', function() {


     if ( !confirm('Are you sure you want to delete this project? All the tickets under this project will be deleted as well. This action cannot be undone.')) {
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

            alert('There was an error trying to delete this post. Try again later.');

         }
     });

 });
