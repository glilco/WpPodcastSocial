
jQuery( 'document' ).ready(function() {
	
	jQuery('#opml_loading').hide();
	jQuery('#opml_form').submit(function() {
		valid = true;

		 if(jQuery("#opml_file").val() == ''){
			 // your validation error action
			valid = false;

		 } else {
			 jQuery('#opml_loading').show();
			 jQuery('#opml_send_button').hide();
		 }

		return valid ;
		
	});
	
});

