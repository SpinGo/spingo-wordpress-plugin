jQuery(document).ready(function() {
	jQuery('.color-field input').wpColorPicker();	
});

jQuery('#insert-calendar-button').on('click', function(evt) {
	window.send_to_editor( "[spingo_calendar]" );
})