// Delete Comment Event.
$('body').on('click', 'a.task_breaker-delete-comment', function(e) {

    e.preventDefault();

    // Ask the user to confirm if he/she really wanted to delete the task comment.
    var confirm_delete = confirm( taskbreaker_strings.comment_confirm_delete );

    // Exit if the user decided to cancel the task comment.
    if (!confirm_delete) {
        return false;
    }

    var $element = $(this);

    var comment_ticket = parseInt($(this).attr('data-comment-id'));

    var __http_params = {
        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transaction_delete_comment',
        comment_id: comment_ticket,
        nonce: task_breakerProjectSettings.nonce
    };

    // Send request to server to delete the comment.
    ThriveProjectView.progress(true);

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( response ) {

            ThriveProjectView.progress(false);

            if (response.message == 'success') {

                $element.parent().parent().parent().parent().fadeOut(function() {
                    $(this).remove();
                });

            } else {

                this.error();

            }
        },
        error: function() {
            ThriveProjectView.progress(false);
            $element.parent().append('<p class="error">'+taskbreaker_strings.comment_error+'</p>');
        }
    });
}); // end Delete Comment
