<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.sgssandhu.com
 * @since      1.0.0
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/public
 * @author     SGS Sandhu <sgs.sandhu@gmail.com>
 */
class Aione_Pwa_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'wp_head', array($this,'aione_pwa_add_manifest_to_wp_head' ));

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Aione_Pwa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aione_Pwa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/aione-pwa-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Aione_Pwa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aione_Pwa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/aione-pwa-public.js', array( 'jquery' ), $this->version, false );

		

		wp_enqueue_script( 'aione-pwa-register-sw', AIONE_PWA_PATH_SRC . 'public/js/register-sw.js', array(), null, true );
		wp_localize_script( 'aione-pwa-register-sw', 'aione_pwa_sw', array(
				'url' => parse_url( aione_pwa_sw( 'src' ), PHP_URL_PATH ),
			)
		);
		

	}

	function aione_pwa_add_manifest_to_wp_head() {	
		$tags  = '<!-- Manifest added by AionePWA - Progressive Web Apps Plugin For WordPress -->' . PHP_EOL; 
		$tags .= '<link rel="manifest" href="'. parse_url( aione_pwa_manifest( 'src' ), PHP_URL_PATH ) . '">' . PHP_EOL;
		
		// theme-color meta tag 
		if ( apply_filters( 'aione_pwa_add_theme_color', true ) ) {
			
			// Get Settings
			$settings = aione_pwa_get_settings();
			$tags .= '<meta name="theme-color" content="'. $settings['theme_color'] .'">' . PHP_EOL;
		}
		
		$tags  = apply_filters( 'aione_pwa_wp_head_tags', $tags );
		
		$tags .= '<!-- /  -->' . PHP_EOL; 
		
		echo $tags;
	}

}
