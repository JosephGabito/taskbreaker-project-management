// Update Project
$('body').on('click', '#thriveUpdateProjectBtn', function() {

    var element = $(this);

    var projectContent = "";

    var __projectContentObj = tinymce.get( 'thriveProjectContent' );

        if ( __projectContentObj ) {

            projectContent = __projectContentObj.getContent();

        } else {

            projectContent = $('#thriveProjectContent').val();

        }

    var __http_params = {
        action: 'thrive_transactions_request',
        method: 'thrive_transactions_update_project',
        id: parseInt( $('#thrive-project-id').val() ),
        title: $( '#thrive-project-name' ).val(),
        content: projectContent,
        group_id: parseInt( $('select[name=thrive-project-assigned-group]').val() ),
        nonce: thriveProjectSettings.nonce
    };

    element.attr('disabled', true).text('Updating ...');

    ThriveProjectView.progress(true);

    $('.thrive-project-updated').remove();

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( httpResponse ) {

            var response = JSON.parse( httpResponse );

            ThriveProjectView.progress(false);

            element.attr('disabled', false).text('Update Project');

            if (response.message === 'success') {

                // Update the project title.
                $('article .entry-header > .entry-title').text($('#thrive-project-name').val());

                element.parent().parent().prepend(
                    '<div id="message" class="thrive-project-updated success updated">' +
                    '<p>Project details successfully updated.</p>' +
                    '</div>'
                );

            } else {

                element.parent().parent().prepend(
                    '<div id="message" class="thrive-project-updated success updated">' +
                    '<p>There was an error saving the project. All fields are required.</p>' +
                    '</div>'
                );

            }

            ThriveProjectView.progress(false);

            setTimeout(function() {

                $('.thrive-project-updated').fadeOut();

            }, 3000);

            return;

        },

        error: function() {

            alert('connection failure');
            return;

        }
    });
}); // Project Update End.
