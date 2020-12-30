<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function aione_pwa_get_assertlinks_filename() {
	return 'aione-pwa-assertlinks' . aione_pwa_multisite_filename_postfix() . '.json';
}
function aione_pwa_assertlinks( $arg = 'src' ) {
	$assertlinks_filename = aione_pwa_get_assertlinks_filename();
	switch ( $arg ) {
		// TODO: Case `filename` can be deprecated in favor of @see aione_pwa_get_assertlinks_filename().
		case 'filename':
			return $assertlinks_filename;
			break;

		
		case 'abs':
			$filepath = trailingslashit( ABSPATH ) . $assertlinks_filename;
			if(!file_exists($filepath)){
				$filepath = trailingslashit( get_home_path() ). $assertlinks_filename;
			}
			return $filepath;
			break;

		// Link to assertlinks
		case 'src':
		default:
		
			// Get Settings
			$settings = aione_pwa_get_settings();
			
			
			if ( $settings['is_static_assertlinks'] === 1 ) {
				return trailingslashit( network_site_url() ) . $assertlinks_filename;
			}
			
			// For dynamic files, return the home_url
			return home_url( '/' ) . $assertlinks_filename;
			
			break;
	}
}
function aione_pwa_assertlinks_template() {	
	// Get Settings
	$settings = aione_pwa_get_settings();

	$assertlinks               = array();
	if ( isset( $settings['assertlinks'] ) && ! empty( $settings['assertlinks'] ) ) {
		$assertlinks['assertlinks'] = $settings['assertlinks'];
	}
	
	return apply_filters( 'aione_pwa_assertlinks', $assertlinks );
}
function aione_pwa_generate_assertlinks() {	
	// Delete assertlinks if it exists.
	aione_pwa_delete_assertlinks();
	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	// Return true if dynamic file returns a 200 response.
	if ( aione_pwa_file_exists( home_url( '/' ) . aione_pwa_get_assertlinks_filename() ) && defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		
		// set file status as dynamic file in database.
		$settings['is_static_assertlinks'] = 0;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	// Write the manfiest to disk.
	if ( aione_pwa_put_contents( aione_pwa_assertlinks( 'abs' ), json_encode( aione_pwa_assertlinks_template() ) ) ) {
		
		// set file status as satic file in database.
		$settings['is_static_assertlinks'] = 1;
		
		// Write settings back to database.
		update_option( 'aione_pwa_settings', $settings );
		
		return true;
	}
	
	return false;
}
function aione_pwa_delete_assertlinks() {
	return aione_pwa_delete( aione_pwa_assertlinks( 'abs' ) );
}

