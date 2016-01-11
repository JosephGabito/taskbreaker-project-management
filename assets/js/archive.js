jQuery(document).ready(function($){
	// Modal
	$('#thrive-new-project-btn').on( 'click', function( e ) {
		e.preventDefault();
		$('#thrive-new-project-modal').show();
		$('#thrive-modal-content').toggleClass('active');

	});
	
	$('#thrive-new-project-modal').on( 'click', function( e ) {
		if ( e.target !== this ) {
			return;
		}
		$(this).hide();
		$('#thrive-modal-content').toggleClass('active');
	});

	$(document).keyup(function(e) {
	  	if ( e.keyCode == 27 ) {
			$('#thrive-new-project-modal').hide();
			$('#thrive-modal-content').toggleClass('active');
	  	}
	});

});