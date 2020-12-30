<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.sgssandhu.com
 * @since             1.0.0
 * @package           Aione_Pwa
 *
 * @wordpress-plugin
 * Plugin Name:       Aione Progressive Web Application
 * Plugin URI:        www.oxosolutions.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0.0
 * Author:            SGS Sandhu
 * Author URI:        www.sgssandhu.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aione-pwa
 * Domain Path:       /languages
  * GitHub Plugin URI: https://github.com/oxosolutions/aione-pwa
 * GitHub Branch: main
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AIONE_PWA_VERSION', '1.0.0.0' );

if ( ! defined( 'AIONE_PWA_PLUGIN_FILE' ) ) {
	define( 'AIONE_PWA_PLUGIN_FILE', __FILE__ ); 
}
if ( ! defined( 'AIONE_PWA_PATH_ABS' ) ) {
	define( 'AIONE_PWA_PATH_ABS'	, plugin_dir_path( __FILE__ ) ); 
}
if ( ! defined( 'AIONE_PWA_PATH_SRC' ) ) {
	define( 'AIONE_PWA_PATH_SRC'	, plugin_dir_url( __FILE__ ) ); 
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-aione-pwa-activator.php
 */
function activate_aione_pwa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aione-pwa-activator.php';
	Aione_Pwa_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-aione-pwa-deactivator.php
 */
function deactivate_aione_pwa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aione-pwa-deactivator.php';
	Aione_Pwa_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_aione_pwa' );
register_deactivation_hook( __FILE__, 'deactivate_aione_pwa' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-aione-pwa.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_aione_pwa() {

	$plugin = new Aione_Pwa();
	$plugin->run();

}
run_aione_pwa();
