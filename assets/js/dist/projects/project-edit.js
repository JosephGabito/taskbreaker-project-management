// Update Project
$('body').on('click', '#task_breakerUpdateProjectBtn', function() {

    var element = $(this);

    var projectContent = "";

    var __projectContentObj = tinymce.get( 'task_breakerProjectContent' );

        if ( __projectContentObj ) {

            projectContent = __projectContentObj.getContent();

        } else {

            projectContent = $('#task_breakerProjectContent').val();

        }

    var __http_params = {
        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transactions_update_project',
        id: parseInt( $('#task_breaker-project-id').val() ),
        title: $( '#task_breaker-project-name' ).val(),
        content: projectContent,
        group_id: parseInt( $('select[name=task_breaker-project-assigned-group]').val() ),
        nonce: task_breakerProjectSettings.nonce
    };

    element.attr('disabled', true).text('Updating ...');

    ThriveProjectView.progress(true);

    $('.task_breaker-project-updated').remove();

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( response ) {

            ThriveProjectView.progress(false);

            element.attr('disabled', false).text('Update Project');

            if (response.message === 'success') {

                // Update the project title.
                $('article .entry-header > .entry-title').text($('#task_breaker-project-name').val());

                element.parent().parent().prepend(
                    '<div id="message" class="task_breaker-project-updated success updated">' +
                    '<p>Project details successfully updated.</p>' +
                    '</div>'
                );

                location.reload();

            } else {

                if ("authentication_error" === response.type ) {

                    element.parent().parent().prepend(
                        '<div id="message" class="task_breaker-project-updated error updated">' +
                        '<p>Only group administrators and moderators can update the project settings.</p>' +
                        '</div>'
                    );

                } else {

                    element.parent().parent().prepend(
                        '<div id="message" class="task_breaker-project-updated success updated">' +
                        '<p>There was an error saving the project. All fields are required.</p>' +
                        '</div>'
                    );

                }

            }

            ThriveProjectView.progress(false);

            setTimeout(function() {

                $('.task_breaker-project-updated').fadeOut();

            }, 3000);

            return;

        },

        error: function() {

            alert('connection failure');
            return;

        }
    });
}); // Project Update End.
