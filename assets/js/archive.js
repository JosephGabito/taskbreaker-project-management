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

});