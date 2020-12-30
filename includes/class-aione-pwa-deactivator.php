<?php

/**
 * Fired during plugin deactivation
 *
 * @link       www.sgssandhu.com
 * @since      1.0.0
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 * @author     SGS Sandhu <sgs.sandhu@gmail.com>
 */
class Aione_Pwa_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Delete manifest
		aione_pwa_delete_manifest();

		// Delete service worker
		aione_pwa_delete_sw();
		
		// For multisites, save the de-activation status of current blog.
		aione_pwa_multisite_activation_status( false );
		
		// Run the network deactivator during network deactivation
		if ( $network_active === true ) {
			aione_pwa_multisite_network_deactivator();
		}
	}

}
