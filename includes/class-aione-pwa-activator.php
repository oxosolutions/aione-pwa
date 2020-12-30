<?php

/**
 * Fired during plugin activation
 *
 * @link       www.sgssandhu.com
 * @since      1.0.0
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 * @author     SGS Sandhu <sgs.sandhu@gmail.com>
 */
class Aione_Pwa_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! $network_active ) {		
			// Set transient for single site activation notice
			set_transient( 'aione_pwa_admin_notice_activation', true, 60 );			
			return;
		}			
		// If we are here, then plugin is network activated on a multisite. Set transient for activation notice on network admin.
		set_transient( 'aione_pwa_network_admin_notice_activation', true, 60 );

		add_action( 'activated_plugin', array($this,'aione_pwa_activation_redirect'), PHP_INT_MAX, 2 );
	}

	function aione_pwa_activation_redirect( $plugin, $network_wide ) {	
		// Return if not SuperPWA or if plugin is activated network wide.
		if ( $plugin !== plugin_basename( AIONE_PWA_PLUGIN_FILE ) || $network_wide === true ) {
			return false;
		}
		
		if ( ! class_exists( 'WP_Plugins_List_Table' ) ) {
			return false;
		}

		/**
		 * An instance of the WP_Plugins_List_Table class.
		 *
		 * @link https://core.trac.wordpress.org/browser/tags/4.9.8/src/wp-admin/plugins.php#L15
		 */
		$wp_list_table_instance = new WP_Plugins_List_Table();
		$current_action         = $wp_list_table_instance->current_action();

		// When only one plugin is activated, the current_action() method will return activate.
		if ( $current_action !== 'activate' ) {
			return false;
		}

		// Redirect to SuperPWA settings page. 
		exit( wp_redirect( admin_url( 'admin.php?page=aione-pwa' ) ) );
	}

}
