<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function aione_pwa_get_manifest_filename() {
	return 'aione-pwa-manifest' . aione_pwa_multisite_filename_postfix() . '.json';
}
function aione_pwa_manifest( $arg = 'src' ) {
	$manifest_filename = aione_pwa_get_manifest_filename();
	switch ( $arg ) {
		// TODO: Case `filename` can be deprecated in favor of @see aione_pwa_get_manifest_filename().
		// Name of Manifest file
		case 'filename':
			return $manifest_filename;
			break;

		
		case 'abs':
			$filepath = trailingslashit( ABSPATH ) . $manifest_filename;
			if(!file_exists($filepath)){
				$filepath = trailingslashit( get_home_path() ). $manifest_filename;
			}
			return $filepath;
			break;

		// Link to manifest
		case 'src':
		default:
		
			// Get Settings
			$settings = aione_pwa_get_settings();
			
			
			if ( $settings['is_static_manifest'] === 1 ) {
				return trailingslashit( network_site_url() ) . $manifest_filename;
			}
			
			// For dynamic files, return the home_url
			return home_url( '/' ) . $manifest_filename;
			
			break;
	}
}
function aione_pwa_manifest_template() {	
	// Get Settings
	$settings = aione_pwa_get_settings();

	$manifest               = array();
	$manifest['name']       = $settings['app_name'];
	$manifest['short_name'] = $settings['app_short_name'];

	// Description
	if ( isset( $settings['description'] ) && ! empty( $settings['description'] ) ) {
		$manifest['description'] = $settings['description'];
	}

	$manifest['icons']            = aione_pwa_get_pwa_icons();
	$manifest['background_color'] = $settings['background_color'];
	$manifest['theme_color']      = $settings['theme_color'];
	$manifest['display']          = aione_pwa_get_display();
	$manifest['orientation']      = aione_pwa_get_orientation();
	$manifest['start_url']        = aione_pwa_get_start_url( true );
	$manifest['scope']            = aione_pwa_get_scope();

	/**
	 * Values that go in to Manifest JSON.
	 *
	 * The Web app manifest is a simple JSON file that tells the browser about your web application.
	 *
	 * @param array $manifest
	 */
	return apply_filters( 'aione_pwa_manifest', $manifest );
}
function aione_pwa_generate_manifest() {	
	// Delete manifest if it exists.
	aione_pwa_delete_manifest();
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	// Return true if dynamic file returns a 200 response.
	if ( aione_pwa_file_exists( home_url( '/' ) . aione_pwa_get_manifest_filename() ) && defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		
		// set file status as dynamic file in database.
		$settings['is_static_manifest'] = 0;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	// Write the manfiest to disk.
	if ( aione_pwa_put_contents( aione_pwa_manifest( 'abs' ), json_encode( aione_pwa_manifest_template() ) ) ) {
		
		// set file status as satic file in database.
		$settings['is_static_manifest'] = 1;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	return false;
}
function aione_pwa_delete_manifest() {
	return aione_pwa_delete( aione_pwa_manifest( 'abs' ) );
}
function aione_pwa_get_pwa_icons() {	
	// Get settings
	$settings = aione_pwa_get_settings();
	
	// Application icon
	$icons_array[] = array(
							'src' 	=> $settings['icon'],
							'sizes'	=> '192x192', // must be 192x192. Todo: use getimagesize($settings['icon'])[0].'x'.getimagesize($settings['icon'])[1] in the future
							'type'	=> 'image/png', // must be image/png. Todo: use getimagesize($settings['icon'])['mime']
							'purpose'=> 'any maskable', // any maskable to support adaptive icons
						);
	
	// Splash screen icon - Added since 1.3
	if ( @$settings['splash_icon'] != '' ) {
		
		$icons_array[] = array(
							'src' 	=> $settings['splash_icon'],
							'sizes'	=> '512x512', // must be 512x512.
							'type'	=> 'image/png', // must be image/png
						);
	}
	
	return $icons_array;
}
function aione_pwa_get_scope() {
	return parse_url( trailingslashit( aione_pwa_get_bloginfo( 'sw' ) ), PHP_URL_PATH );
}
function aione_pwa_get_orientation() {
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	$orientation = isset( $settings['orientation'] ) ? $settings['orientation'] : 0;
	
	switch ( $orientation ) {
		
		case 0:
			return 'any';
			break;
			
		case 1:
			return 'portrait';
			break;
			
		case 2:
			return 'landscape';
			break;
			
		default: 
			return 'any';
	}
}
function aione_pwa_get_display() {
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	$display = isset( $settings['display'] ) ? $settings['display'] : 1;
	
	switch ( $display ) {
		
		case 0:
			return 'fullscreen';
			break;
			
		case 1:
			return 'standalone';
			break;
			
		case 2:
			return 'minimal-ui';
			break;

		case 3:
			return 'browser';
			break;
			
		default: 
			return 'standalone';
	}
}