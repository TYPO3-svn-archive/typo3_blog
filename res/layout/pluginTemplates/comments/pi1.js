
jQuery(document).ready(function() {

	// Set error classes on load
	jQuery('.tx-comments-pi1-error').parents('.control-group').addClass('error');

	// Load cookie values on load
	jQuery('input[data-cookie="true"]').each(function() {
		if ($(this).val() == '') {
			$(this).val(jQuery.cookie(this.id));
		}
	});

	// Save cookies on submit
	jQuery('.tx-comments-pi1 form').bind("submit", function() {
		jQuery('input[data-cookie="true"]').each(function() {
			jQuery.cookie(this.id, $(this).val());
		});
	});

});
