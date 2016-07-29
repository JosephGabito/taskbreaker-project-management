  $('body').on('click', '#updateTaskBtn', function() {

      var updateTaskBtn = $(this);

      updateTaskBtn.attr('disabled', 'disabled');

      var comment_ticket_id = ThriveProjectModel.id,
          comment_details = $('#task-comment-content').val(),
          task_priority = $('#task_breaker-task-priority-update-select').val(),
          comment_completed = $('input[name=task_commment_completed]:checked').val(),
          task_project_id = parseInt( ThriveProjectModel.project_id );

      if (0 === comment_ticket_id) {
          return;
      }

      // notify the user when submitting the comment form
      ThriveProjectView.progress(true);

      var __http_params = {
          action: 'task_breaker_transactions_request',
          method: 'task_breaker_transaction_add_comment_to_ticket',
          ticket_id: comment_ticket_id,
          priority: task_priority,
          details: comment_details,
          completed: comment_completed,
          project_id: task_project_id,
          nonce: task_breakerProjectSettings.nonce
      };

      $.ajax({
          url: ajaxurl,
          data: __http_params,
          method: 'post',
          success: function( response ) {

              updateTaskBtn.attr('disabled', false);
              ThriveProjectView.progress( false );

              $('#task-comment-content').val('');
              $('#task-lists').append(response.result);


              if ("yes" === comment_completed) {

                  // disable old radios
                  $('#ticketStatusInProgress').attr('disabled', true).attr('checked', false);
                  $('#ticketStatusComplete').attr('disabled', true).attr('checked', false);
                  $('#comment-completed-radio').addClass('hide');
                  // enable new radios
                  $('#ticketStatusCompleteUpdate').attr('disabled', false).attr('checked', true);
                  $('#ticketStatusReOpenUpdate').attr('disabled', false);
                  $('#task_breaker-comment-completed-radio').removeClass('hide');

              }

              if ( "reopen" === comment_completed ) {

                  // Enable old radios
                  $('#ticketStatusInProgress').attr('disabled', false).attr('checked', true);
                  $('#ticketStatusComplete').attr('disabled', false).attr('checked', false);
                  $('#comment-completed-radio').removeClass('hide');
                  // Disable new radios
                  $('#ticketStatusCompleteUpdate').attr('disabled', true).attr('checked', false);
                  $('#ticketStatusReOpenUpdate').attr('disabled', true);
                  $('#task_breaker-comment-completed-radio').addClass('hide');

              }
              // console.log(response.stats);
              ThriveProjectView.updateStats( response.stats );
          },
          error: function() {
              updateTaskBtn.attr('disabled', false);
              ThriveProjectView.progress(false);
          }
      });
  }); // end UpdateTask
