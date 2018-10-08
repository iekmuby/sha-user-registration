jQuery( document ).ready(function() {
	jQuery('.toggle-checkbox').on('click', function() {
		jQuery('.' + jQuery(this).data('group')).toggleClass('hided');
	});
});
