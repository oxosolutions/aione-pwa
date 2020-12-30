<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.sgssandhu.com
 * @since      1.0.0
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/includes
 * @author     SGS Sandhu <sgs.sandhu@gmail.com>
 */
class Aione_Pwa_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'aione-pwa',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
