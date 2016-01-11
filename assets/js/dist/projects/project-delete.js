 $('body').on('click', '#thriveDeleteProjectBtn', function() {


     if ( !confirm('Are you sure you want to delete this project? All the tickets under this project will be deleted as well. This action cannot be undone.')) {
         return;
     }

     var project_id = $('#thrive-project-id').val();

     var __http_params = {
         action: 'thrive_transactions_request',
         method: 'thrive_transactions_delete_project',
         id: project_id,
         nonce: thriveProjectSettings.nonce
     };

     $(this).text('Deleting...');

     $.ajax({
         
         url: ajaxurl,
         
         method: 'post',
         
         data: __http_params,

         success: function( httpResponse ) {

             var response = JSON.parse( httpResponse );

             if (response.message == 'success') {

                 window.location = response.redirect;

             } else {
                 console.log('__success_callback');

                 this.error();

             }

             return;

         },

         error: function() {

            alert('There was an error trying to delete this post. Try again later.');

         }
     });

 });
