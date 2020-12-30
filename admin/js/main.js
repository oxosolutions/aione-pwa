jQuery(document).ready(function($){ 
    $('.aione-pwa-colorpicker').wpColorPicker();	// Color picker
	$('.aione-pwa-icon-upload').click(function(e) {	// Application Icon upload
		e.preventDefault();
		var aione_pwa_meda_uploader = wp.media({
			title: 'Application Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = aione_pwa_meda_uploader.state().get('selection').first().toJSON();
			$('.aione-pwa-icon').val(attachment.url);
		})
		.open();
	});
	$('.aione-pwa-splash-icon-upload').click(function(e) {	// Splash Screen Icon upload
		e.preventDefault();
		var aione_pwa_meda_uploader = wp.media({
			title: 'Splash Screen Icon',
			button: {
				text: 'Select Icon'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = aione_pwa_meda_uploader.state().get('selection').first().toJSON();
			$('.aione-pwa-splash-icon').val(attachment.url);
		})
		.open();
	});
	$('.aione-pwa-app-short-name').on('input', function(e) {	// Warn when app_short_name exceeds 12 characters.
		if ( $('.aione-pwa-app-short-name').val().length > 15 ) {
			$('.aione-pwa-app-short-name').css({'color': '#dc3232'});
			$('#aione-pwa-app-short-name-limit').css({'color': '#dc3232'});
		} else {
			$('.aione-pwa-app-short-name').css({'color': 'inherit'});
			$('#aione-pwa-app-short-name-limit').css({'color': 'inherit'});
		}
	});
});