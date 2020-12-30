<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
function aione_pwa_multisite_filename_postfix() {
	
	// Return empty string if not a multisite
	if ( ! is_multisite() ) {
		return '';
	}
	
	return '-' . get_current_blog_id();
}
function aione_pwa_multisite_activation_status( $status ) {
	
	// Only for multisites
	if ( ! is_multisite() || ! isset( $status ) ) {
		return;
	}
	
	// Get current list of sites where aionePWA is activated.
	$aione_pwa_sites = get_site_option( 'aione_pwa_active_sites', array() );
	
	// Set the status for the current blog.
	$aione_pwa_sites[ get_current_blog_id() ] = $status;
	
	// Save it back to the database.
	update_site_option( 'aione_pwa_active_sites', $aione_pwa_sites );
}
function aione_pwa_multisite_network_deactivator() {
	
	// Do not run on large networks
	if ( wp_is_large_network() ) {
		return;
	}
	
	// Retrieve the list of blog ids where aionePWA is active. (saved with blog_id as $key and activation_status as $value)
	$aione_pwa_sites = get_site_option( 'aione_pwa_active_sites' );
	
	// Loop through each active site.
	foreach( $aione_pwa_sites as $blog_id => $actviation_status ) {
		
		// Switch to each blog
		switch_to_blog( $blog_id );

		// Delete manifest
		aione_pwa_delete_manifest();

		// Delete service worker
		aione_pwa_delete_sw();
		
		/**
		 * Delete AionePWA version info for current blog.
		 * 
		 * This is required so that aione_pwa_upgrader() will run and create the manifest and service worker on next activation.
		 * Known edge case: Database upgrade that relies on the version number will fail if user deactivates and later activates after AionePWA is updated.
		 */
		delete_option( 'aione_pwa_version' );
	
		// Save the de-activation status of current blog.
		aione_pwa_multisite_activation_status( false );
		
		// Return to main site
		restore_current_blog();
	}
}
function aione_pwa_get_start_url( $rel = false ) {	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	// Start Page
	$start_url = get_permalink( $settings['start_url'] ) ? get_permalink( $settings['start_url'] ) : aione_pwa_get_bloginfo( 'sw' );
	
	// Force HTTPS
	$start_url = aione_pwa_httpsify( $start_url );
	
	// Relative URL for manifest
	if ( $rel === true ) {
		
		// Make start_url relative for manifest
		$start_url = ( parse_url( $start_url, PHP_URL_PATH ) == '' ) ? '.' : parse_url( $start_url, PHP_URL_PATH );
		
		return apply_filters( 'aione_pwa_manifest_start_url', $start_url );
	}
	
	return $start_url;
}
function aione_pwa_httpsify( $url ) {
	return str_replace( 'http://', 'https://', $url );
}
function aione_pwa_is_pwa_ready() {	
	if ( 
		is_ssl() && 
		aione_pwa_file_exists( aione_pwa_manifest( 'src' ) ) && 
		aione_pwa_file_exists( aione_pwa_sw( 'src' ) ) 
	) {
		return apply_filters( 'aione_pwa_is_pwa_ready', true );
	}
	
	return false; 
}
function aione_pwa_file_exists( $file ) {
	
	$response 		= wp_remote_head( $file, array( 'sslverify' => false ) );
	$response_code 	= wp_remote_retrieve_response_code( $response );
	
	if ( 200 === $response_code ) {
		return true;
	}
	
	return false;
}
function aione_pwa_is_static( $file = 'manifest' ) {	
	// Get Settings
	$settings = aione_pwa_get_settings();
	
	switch ( $file ) {
		
		case 'sw':
			
			if ( $settings['is_static_sw'] === 1 ) {
				return true;
			}
			
			return false;
			break;
		
		case 'manifest':
		default: 
			
			if ( $settings['is_static_manifest'] === 1 ) {
				return true;
			}
		
			return false;
			break;
	}
}
function aione_pwa_get_bloginfo( $file = 'sw' ) {
	
	if ( aione_pwa_is_static( $file ) ) {
		return get_bloginfo( 'wpurl' );
	}
	
	return get_bloginfo( 'url' );
}
function aione_pwa_wp_filesystem_init() {
	
	global $wp_filesystem;
	
	if ( empty( $wp_filesystem ) ) {
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php' );
		WP_Filesystem();
	}
}
function aione_pwa_put_contents( $file, $content = null ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	aione_pwa_wp_filesystem_init();
	global $wp_filesystem;
	
	if( ! $wp_filesystem->put_contents( $file, $content, 0644) ) {
		return false;
	}
	
	return true;
}
function aione_pwa_get_contents( $file, $array = false ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	aione_pwa_wp_filesystem_init();
	global $wp_filesystem;
	
	// Reads entire file into a string
	if ( $array == false ) {
		return $wp_filesystem->get_contents( $file );
	}
	
	// Reads entire file into an array
	return $wp_filesystem->get_contents_array( $file );
}
function aione_pwa_delete( $file ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	aione_pwa_wp_filesystem_init();
	global $wp_filesystem;
	
	return $wp_filesystem->delete( $file );
}
function aione_pwa_get_settings() {
	$defaults = array(
				'app_name'			=> get_bloginfo( 'name' ),
				'app_short_name'	=> substr( get_bloginfo( 'name' ), 0, 15 ),
				'description'		=> get_bloginfo( 'description' ),
				'icon'				=> AIONE_PWA_PATH_SRC . 'public/images/logo.png',
				'splash_icon'		=> AIONE_PWA_PATH_SRC . 'public/images/logo-512x512.png',
				'background_color' 	=> '#D5E0EB',
				'theme_color' 		=> '#D5E0EB',
				'start_url' 		=> 0,
				'start_url_amp'		=> 0,
				'offline_page' 		=> 0,
				'orientation'		=> 1,
				'display'			=> 1,
				'is_static_manifest'=> 0,
				'is_static_sw'		=> 0,
			);

	$settings = get_option( 'aione_pwa_settings', $defaults );
	
	return $settings;
}