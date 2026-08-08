/**
 * Dynamic Island Navbar Admin Script
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		// Color Picker Initialization
		if ($.fn.wpColorPicker) {
			$('.din-color-picker').wpColorPicker();
		}

		// WP Media Logo Uploader
		var mediaUploader;

		$('#din-upload-logo-btn').on('click', function (e) {
			e.preventDefault();

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media({
				title: 'Select or Upload Logo',
				button: { text: 'Use this Logo' },
				multiple: false
			});

			mediaUploader.on('select', function () {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#din_logo_url').val(attachment.url);
				$('#din-logo-preview').attr('src', attachment.url).show();
				$('#din-remove-logo-btn').show();
			});

			mediaUploader.open();
		});

		$('#din-remove-logo-btn').on('click', function (e) {
			e.preventDefault();
			$('#din_logo_url').val('');
			$('#din-logo-preview').attr('src', '').hide();
			$(this).hide();
		});
	});
})(jQuery);
