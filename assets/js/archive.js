jQuery(document).ready(function($){
	// Modal
	$('#task_breaker-new-project-btn').on( 'click', function( e ) {
		e.preventDefault();
		$('#task_breaker-new-project-modal').show();
		$('#task_breaker-modal-content').toggleClass('active');

	});

	$('#task_breaker-new-project-modal').on( 'click', function( e ) {
		if ( e.target !== this ) {
			return;
		}
		$(this).hide();
		$('#task_breaker-modal-content').toggleClass('active');
	});

	$(document).keyup(function(e) {
	  	if ( e.keyCode == 27 ) {
			$('#task_breaker-new-project-modal').hide();
			$('#task_breaker-modal-content').toggleClass('active');
	  	}
	});


	// Add project
	$('#task_breakerSaveProjectBtn').on( 'click', function(e){

		e.preventDefault();

		$('#project-add-modal-js-message').html("");

		var form_error_count = 0;

		var project_details = {
			title: $('#task_breaker-project-name').val(),
			content: $('#task_breaker-project-content').val(),
			group_id: $('#task_breaker-project-assigned-group').val(),

			method: $('#task_breaker-project-add-new-form input[name=method]').val(),
			action: $('#task_breaker-project-add-new-form input[name=action]').val(),
			_wp_http_referer: $('#task_breaker-project-add-new-form input[name=_wp_http_referer]').val(),
			nonce: $('#nonce').val()
		};

		console.log(project_details);

		for ( key in project_details ) {

			var field_value = project_details[key].trim();
			var field_length = field_value.length;

			if ( 0 === field_length ) {
				form_error_count++;
			}
		}

		if ( form_error_count >= 1 ) {
			$('#project-add-modal-js-message').html(
				"<p id='message' class='error'>All fields are required</p>"
			).removeClass('hide');
		} else {

			console.log('Requesting Project...');

			$.ajax({
			  type: "POST",
			  dataType: 'json',
			  url: $('#task_breaker-project-add-new-form-form').attr('action'),
			  data: project_details,
			  success: function( response ) {
				  $('#project-add-modal-js-message').html(
					  "<p id='message' class='success'>Project successfully added. Redirecting you in few seconds...</p>"
				  ).removeClass('hide');
				  location.href = response.project_permalink;
			  },
			  error: function(err, message) {
				  console.log(err);
				  console.log(message);
			  }
			});

		}

	});
});
