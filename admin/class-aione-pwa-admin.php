<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.sgssandhu.com
 * @since      1.0.0
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Aione_Pwa
 * @subpackage Aione_Pwa/admin
 * @author     SGS Sandhu <sgs.sandhu@gmail.com>
 */
class Aione_Pwa_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'init', array($this,'aione_pwa_add_rewrite_rules' ));

		add_action( 'parse_request', array($this,'aione_pwa_generate_sw_and_manifest_on_fly' ));

		add_action( 'admin_menu', array($this,'aione_pwa_add_menu_links' ));
		add_action( 'admin_init', array($this,'aione_pwa_register_settings' ));


	}
	

	/**
	 * Register the stylesheets for the admin area.
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

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/aione-pwa-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/aione-pwa-admin.js', array( 'jquery' ), $this->version, false );

		/*

		wp_enqueue_script( 'aione-pwa-register-sw', AIONE_PWA_PATH_SRC . 'public/js/register-sw.js', array(), null, true );
		wp_localize_script( 'aione-pwa-register-sw', 'aione_pwa_sw', array(
				'url' => parse_url( aione_pwa_sw( 'src' ), PHP_URL_PATH ),
			)
		);
		*/

		wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
		
		// Main JS
	    wp_enqueue_script( 'aione-pwa-main-js', AIONE_PWA_PATH_SRC . 'admin/js/main.js', array( 'wp-color-picker' ), AIONE_PWA_VERSION, true );

	}

	function aione_pwa_add_rewrite_rules() {
		$sw_filename = aione_pwa_get_sw_filename();
		add_rewrite_rule( "^/{$sw_filename}$",
			"index.php?{$sw_filename}=1"
		);

		$manifest_filename = aione_pwa_get_manifest_filename();
		add_rewrite_rule( "^/{$manifest_filename}$",
			"index.php?{$manifest_filename}=1"
		);

		$assertlinks_filename = aione_pwa_get_assertlinks_filename();
		add_rewrite_rule( "^/{$assertlinks_filename}$",
			"index.php?{$assertlinks_filename}=1"
		);
	}

	function aione_pwa_generate_sw_and_manifest_on_fly( $query ) {
		if ( ! property_exists( $query, 'query_vars' ) || ! is_array( $query->query_vars ) ) {
			return;
		}
		$query_vars_as_string = http_build_query( $query->query_vars );
		$manifest_filename    = aione_pwa_get_manifest_filename();
		$sw_filename          = aione_pwa_get_sw_filename();
		$assertlinks_filename          = aione_pwa_get_assertlinks_filename();

		if ( strpos( $query_vars_as_string, $manifest_filename ) !== false ) {
			// Generate manifest from Settings and send the response w/ header.
			header( 'Content-Type: application/json' );
			echo json_encode( aione_pwa_manifest_template() );
			exit();
		}
		if ( strpos( $query_vars_as_string, $assertlinks_filename ) !== false ) {
			header( 'Content-Type: application/json' );
			echo json_encode( aione_pwa_assertlinks_template() );
			exit();
		}
		if ( strpos( $query_vars_as_string, $sw_filename ) !== false ) {
			header( 'Content-Type: text/javascript' );
			echo aione_pwa_sw_template();
			exit();
		}
	}

	function aione_pwa_add_menu_links() {			
		$page = array();
		$hook = add_submenu_page( 'aione-dashboard', __( 'Aione Progressive Web Apps', 'aione-pwa' ), __( 'Aione PWA', 'aione-pwa' ), 'manage_options', 'aione-pwa', array($this,'aione_pwa_admin_interface_render') , 60);
		add_action( 'load-' . $hook, $page );
		
	}

	function aione_pwa_admin_interface_render() {	
		// Authentication
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Handing save settings
		if ( isset( $_GET['settings-updated'] ) ) {
			
			// Add settings saved message with the class of "updated"
			add_settings_error( 'aione_pwa_settings_group', 'aione_pwa_settings_saved_message', __( 'Settings saved.', 'aione-pwa' ), 'updated' );
			
			// Show Settings Saved Message
			settings_errors( 'aione_pwa_settings_group' );
		}
		
		?>
		
		<div class="wrap">	
			<h1>Aione Progressive Web Apps</h1>
			
			<form action="options.php" method="post" enctype="multipart/form-data">		
				<?php
				// Output nonce, action, and option_page fields for a settings page.
				settings_fields( 'aione_pwa_settings_group' );
				
				// Basic Application Settings
				do_settings_sections( 'aione_pwa_basic_settings_section' );	// Page slug
				
				// Status
				do_settings_sections( 'aione_pwa_pwa_status_section' );	// Page slug
				
				// Output save settings button
				 echo '<style>.submit{float:left;}</style>';
				submit_button( __('Save Settings', 'aione-pwa') );
				
				?>
			</form>
		</div>
		<?php
	}

	function aione_pwa_register_settings() {
		// Register Setting
		register_setting( 
			'aione_pwa_settings_group', 			// Group name
			'aione_pwa_settings', 				// Setting name = html form <input> name on settings form
			array($this,'aione_pwa_validater_and_sanitizer')	// Input sanitizer
		);
		
		// Basic Application Settings
	    add_settings_section(
	        'aione_pwa_basic_settings_section',					// ID
	        __return_false(),									// Title
	        '__return_false',									// Callback Function
	        'aione_pwa_basic_settings_section'					// Page slug
	    );
		
			// Application Name
			add_settings_field(
				'aione_pwa_app_name',									// ID
				__('Application Name', 'aione-pwa'),	// Title
				array($this,'aione_pwa_app_name_cb'),									// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Application Short Name
			add_settings_field(
				'aione_pwa_app_short_name',								// ID
				__('Application Short Name', 'aione-pwa'),	// Title
				array($this,'aione_pwa_app_short_name_cb'),							// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Description
			add_settings_field(
				'aione_pwa_description',									// ID
				__( 'Description', 'aione-pwa' ),		// Title
				array($this,'aione_pwa_description_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Application Icon
			add_settings_field(
				'aione_pwa_icons',										// ID
				__('Application Icon', 'aione-pwa'),	// Title
				array($this,'aione_pwa_app_icon_cb'),									// Callback function
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Splash Screen Icon
			add_settings_field(
				'aione_pwa_splash_icon',									// ID
				__('Splash Screen Icon', 'aione-pwa'),	// Title
				array($this,'aione_pwa_splash_icon_cb'),								// Callback function
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Splash Screen Background Color
			add_settings_field(
				'aione_pwa_background_color',							// ID
				__('Background Color', 'aione-pwa'),	// Title
				array($this,'aione_pwa_background_color_cb'),							// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Theme Color
			add_settings_field(
				'aione_pwa_theme_color',									// ID
				__('Theme Color', 'aione-pwa'),		// Title
				array($this,'aione_pwa_theme_color_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Start URL
			add_settings_field(
				'aione_pwa_start_url',									// ID
				__('Start Page', 'aione-pwa'),			// Title
				array($this,'aione_pwa_start_url_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Offline Page
			add_settings_field(
				'aione_pwa_offline_page',								// ID
				__('Offline Page', 'aione-pwa'),		// Title
				array($this,'aione_pwa_offline_page_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
			// Orientation
			add_settings_field(
				'aione_pwa_orientation',									// ID
				__('Orientation', 'aione-pwa'),		// Title
				array($this,'aione_pwa_orientation_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
		
			// Display
			add_settings_field(
				'aione_pwa_display',									// ID
				__('Display', 'aione-pwa'),		// Title
				array($this,'aione_pwa_display_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);

			// AssertLink Json
			add_settings_field(
				'aione_pwa_assertlinks',									// ID
				__('Assert Links', 'aione-pwa'),		// Title
				array($this,'aione_pwa_assertlinks_cb'),								// CB
				'aione_pwa_basic_settings_section',						// Page slug
				'aione_pwa_basic_settings_section'						// Settings Section ID
			);
			
		// PWA Status
	    add_settings_section(
	        'aione_pwa_pwa_status_section',					// ID
	        __('Status', 'aione-pwa'),		// Title
	        '__return_false',								// Callback Function
	        'aione_pwa_pwa_status_section'					// Page slug
	    );
		
			// Manifest status
			add_settings_field(
				'aione_pwa_manifest_status',								// ID
				__('Manifest', 'aione-pwa'),			// Title
				array($this,'aione_pwa_manifest_status_cb'),							// CB
				'aione_pwa_pwa_status_section',							// Page slug
				'aione_pwa_pwa_status_section'							// Settings Section ID
			);
			
			// Service Worker status
			add_settings_field(
				'aione_pwa_sw_status',									// ID
				__('Service Worker', 'aione-pwa'),		// Title
				array($this,'aione_pwa_sw_status_cb'),								// CB
				'aione_pwa_pwa_status_section',							// Page slug
				'aione_pwa_pwa_status_section'							// Settings Section ID
			);

			// AssertLinks status
			add_settings_field(
				'aione_pwa_assertlinks_status',									// ID
				__('Assert Links', 'aione-pwa'),		// Title
				array($this,'aione_pwa_assertlinks_status_cb'),								// CB
				'aione_pwa_pwa_status_section',							// Page slug
				'aione_pwa_pwa_status_section'							// Settings Section ID
			);	
			
			// HTTPS status
			add_settings_field(
				'aione_pwa_https_status',								// ID
				__('HTTPS', 'aione-pwa'),				// Title
				array($this,'aione_pwa_https_status_cb'),								// CB
				'aione_pwa_pwa_status_section',							// Page slug
				'aione_pwa_pwa_status_section'							// Settings Section ID
			);	
	}
	function aione_pwa_validater_and_sanitizer( $settings ) {	
		// Sanitize Application Name
		$settings['app_name'] = sanitize_text_field( $settings['app_name'] ) == '' ? get_bloginfo( 'name' ) : sanitize_text_field( $settings['app_name'] );
		
		// Sanitize Application Short Name
		$settings['app_short_name'] = substr( sanitize_text_field( $settings['app_short_name'] ) == '' ? get_bloginfo( 'name' ) : sanitize_text_field( $settings['app_short_name'] ), 0, 15 );
		
		// Sanitize description
		$settings['description'] = sanitize_text_field( $settings['description'] );
		
		// Sanitize hex color input for background_color
		$settings['background_color'] = preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $settings['background_color'] ) ? sanitize_text_field( $settings['background_color'] ) : '#D5E0EB';
		
		// Sanitize hex color input for theme_color
		$settings['theme_color'] = preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $settings['theme_color'] ) ? sanitize_text_field( $settings['theme_color'] ) : '#D5E0EB';
		
		// Sanitize application icon
		$settings['icon'] = sanitize_text_field( $settings['icon'] ) == '' ? aione_pwa_httpsify( AIONE_DIR_URL . 'public/images/logo.png' ) : sanitize_text_field( aione_pwa_httpsify( $settings['icon'] ) );
		
		// Sanitize splash screen icon
		$settings['splash_icon'] = sanitize_text_field( aione_pwa_httpsify( $settings['splash_icon'] ) );
		
		/**
		 * Get current settings already saved in the database.
		 * 
		 * When the SuperPWA > Settings page is saved, the form does not have the values for
		 * is_static_sw or is_static_manifest. So this is added here to match the already saved 
		 * values in the database. 
		 */
		$current_settings = $this->aione_pwa_get_settings();
		
		if ( ! isset( $settings['is_static_sw'] ) ) {
			$settings['is_static_sw'] = $current_settings['is_static_sw'];
		}
		
		if ( ! isset( $settings['is_static_manifest'] ) ) {
			$settings['is_static_manifest'] = $current_settings['is_static_manifest'];
		}
		
		return $settings;
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

	function aione_pwa_app_name_cb() { 
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); 
		?>
		<fieldset>
			
			<input type="text" name="aione_pwa_settings[app_name]" class="regular-text" value="<?php if ( isset( $settings['app_name'] ) && ( ! empty($settings['app_name']) ) ) echo esc_attr($settings['app_name']); ?>"/>
			
		</fieldset>

		<?php
	}
	function aione_pwa_app_short_name_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<fieldset>
			
			<input type="text" name="aione_pwa_settings[app_short_name]" class="regular-text aione-pwa-app-short-name" value="<?php if ( isset( $settings['app_short_name'] ) && ( ! empty($settings['app_short_name']) ) ) echo esc_attr($settings['app_short_name']); ?>"/>
			
			<p class="description">
				<?php _e('Used when there is insufficient space to display the full name of the application. <span id="aione-pwa-app-short-name-limit"><code>15</code> characters or less.</span>', 'aione-pwa'); ?>
			</p>
			
		</fieldset>

		<?php
	}
	function aione_pwa_description_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<fieldset>
			
			<input type="text" name="aione_pwa_settings[description]" class="regular-text" value="<?php if ( isset( $settings['description'] ) && ( ! empty( $settings['description'] ) ) ) echo esc_attr( $settings['description'] ); ?>"/>
			
			<p class="description">
				<?php _e( 'A brief description of what your app is about.', 'aione-pwa' ); ?>
			</p>
			
		</fieldset>

		<?php
	}
	function aione_pwa_app_icon_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Application Icon -->
		<input type="text" name="aione_pwa_settings[icon]" id="aione_pwa_settings[icon]" class="aione-pwa-icon regular-text" size="50" value="<?php echo isset( $settings['icon'] ) ? esc_attr( $settings['icon']) : ''; ?>">
		<button type="button" class="button aione-pwa-icon-upload" data-editor="content">
			<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php _e( 'Choose Icon', 'aione-pwa' ); ?>
		</button>
		
		<p class="description">
			<?php _e('This will be the icon of your app when installed on the phone. Must be a <code>PNG</code> image exactly <code>192x192</code> in size.', 'aione-pwa'); ?>
		</p>

		<?php
	}
	function aione_pwa_splash_icon_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Splash Screen Icon -->
		<input type="text" name="aione_pwa_settings[splash_icon]" id="aione_pwa_settings[splash_icon]" class="aione-pwa-splash-icon regular-text" size="50" value="<?php echo isset( $settings['splash_icon'] ) ? esc_attr( $settings['splash_icon']) : ''; ?>">
		<button type="button" class="button aione-pwa-splash-icon-upload" data-editor="content">
			<span class="dashicons dashicons-format-image" style="margin-top: 4px;"></span> <?php _e( 'Choose Icon', 'aione-pwa' ); ?>
		</button>
		
		<p class="description">
			<?php _e('This icon will be displayed on the splash screen of your app on supported devices. Must be a <code>PNG</code> image exactly <code>512x512</code> in size.', 'aione-pwa'); ?>
		</p>

		<?php
	}
	function aione_pwa_background_color_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Background Color -->
		<input type="text" name="aione_pwa_settings[background_color]" id="aione_pwa_settings[background_color]" class="aione-pwa-colorpicker" value="<?php echo isset( $settings['background_color'] ) ? esc_attr( $settings['background_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
		
		<p class="description">
			<?php _e('Background color of the splash screen.', 'aione-pwa'); ?>
		</p>

		<?php
	}
	function aione_pwa_theme_color_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Theme Color -->
		<input type="text" name="aione_pwa_settings[theme_color]" id="aione_pwa_settings[theme_color]" class="aione-pwa-colorpicker" value="<?php echo isset( $settings['theme_color'] ) ? esc_attr( $settings['theme_color']) : '#D5E0EB'; ?>" data-default-color="#D5E0EB">
		
		<p class="description">
			<?php _e('Theme color is used on supported devices to tint the UI elements of the browser and app switcher. When in doubt, use the same color as <code>Background Color</code>.', 'aione-pwa'); ?>
		</p>

		<?php
	}
	function aione_pwa_start_url_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<fieldset>
		
			<!-- WordPress Pages Dropdown -->
			<label for="aione_pwa_settings[start_url]">
			<?php echo wp_dropdown_pages( array( 
					'name' => 'aione_pwa_settings[start_url]', 
					'echo' => 0, 
					'show_option_none' => __( '&mdash; Homepage &mdash;' ), 
					'option_none_value' => '0', 
					'selected' =>  isset($settings['start_url']) ? $settings['start_url'] : '',
				)); ?>
			</label>	
		
		</fieldset>

		<?php
	}
	function aione_pwa_offline_page_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- WordPress Pages Dropdown -->
		<label for="aione_pwa_settings[offline_page]">
		<?php echo wp_dropdown_pages( array( 
				'name' => 'aione_pwa_settings[offline_page]', 
				'echo' => 0, 
				'show_option_none' => __( '&mdash; Default &mdash;' ), 
				'option_none_value' => '0', 
				'selected' =>  isset($settings['offline_page']) ? $settings['offline_page'] : '',
			)); ?>
		</label>

		<?php
	}
	function aione_pwa_orientation_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Orientation Dropdown -->
		<label for="aione_pwa_settings[orientation]">
			<select name="aione_pwa_settings[orientation]" id="aione_pwa_settings[orientation]">
				<option value="0" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 0 ); } ?>>
					<?php _e( 'Follow Device Orientation', 'aione-pwa' ); ?>
				</option>
				<option value="1" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 1 ); } ?>>
					<?php _e( 'Portrait', 'aione-pwa' ); ?>
				</option>
				<option value="2" <?php if ( isset( $settings['orientation'] ) ) { selected( $settings['orientation'], 2 ); } ?>>
					<?php _e( 'Landscape', 'aione-pwa' ); ?>
				</option>
			</select>
		</label>
		
		<p class="description">
			<?php _e( 'Set the orientation of your app on devices. When set to <code>Follow Device Orientation</code> your app will rotate as the device is rotated.', 'aione-pwa' ); ?>
		</p>

		<?php
	}
	function aione_pwa_display_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Display Dropdown -->
		<label for="aione_pwa_settings[display]">
			<select name="aione_pwa_settings[display]" id="aione_pwa_settings[display]">
				<option value="0" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 0 ); } ?>>
					<?php _e( 'Full Screen', 'aione-pwa' ); ?>
				</option>
				<option value="1" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 1 ); } ?>>
					<?php _e( 'Standalone', 'aione-pwa' ); ?>
				</option>
				<option value="2" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 2 ); } ?>>
					<?php _e( 'Minimal UI', 'aione-pwa' ); ?>
				</option>
				<option value="3" <?php if ( isset( $settings['display'] ) ) { selected( $settings['display'], 3 ); } ?>>
					<?php _e( 'Browser', 'aione-pwa' ); ?>
				</option>
			</select>
		</label>
		
		<p class="description">
			<?php printf( __( 'Display mode decides what browser UI is shown when your app is launched. <code>Standalone</code> is default. <a href="%s" target="_blank">What\'s the difference? &rarr;</a>', 'aione-pwa' ) . '</p>', 'https://oxosolutions.com/' ); ?>
		</p>

		<?php
	}
	function aione_pwa_assertlinks_cb() {
		// Get Settings
		$settings = $this->aione_pwa_get_settings(); ?>
		
		<!-- Display Textarea -->
		<fieldset>

			<textarea name="aione_pwa_settings[assertlinks]" class="regular-text"><?php if ( isset( $settings['assertlinks'] ) && ( ! empty( $settings['assertlinks'] ) ) ) echo esc_attr( $settings['assertlinks'] ); ?></textarea>
			
			<p class="description">
				<?php _e( 'Assert Links Json.', 'aione-pwa' ); ?>
			</p>
			
		</fieldset>

		<?php
	}
	function aione_pwa_manifest_status_cb() {
		if ( aione_pwa_file_exists( aione_pwa_manifest( 'src' ) ) || aione_pwa_generate_manifest() ) {
			
			printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Manifest generated successfully. You can <a href="%s" target="_blank">See it here &rarr;</a>', 'aione-pwa' ) . '</p>', aione_pwa_manifest( 'src' ) );
		} else {
			
			printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Manifest generation failed. <a href="%s" target="_blank">Fix it &rarr;</a>', 'aione-pwa' ) . '</p>', 'https://oxosolutions.com/' );
		}
	}
	function aione_pwa_sw_status_cb() {
		if ( aione_pwa_file_exists( aione_pwa_sw( 'src' ) ) || aione_pwa_generate_sw() ) {
			
			printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Service worker generated successfully. <a href="%s" target="_blank">See it here &rarr;</a>', 'aione-pwa' ) . '</p>', aione_pwa_sw( 'src' ) );
		} else {
			
			printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Service worker generation failed. <a href="%s" target="_blank">Fix it &rarr;</a>', 'aione-pwa' ) . '</p>', 'https://oxosolutions.com/' );
		}
	}
	function aione_pwa_assertlinks_status_cb() {
		if ( aione_pwa_file_exists( aione_pwa_assertlinks( 'src' ) ) || aione_pwa_generate_assertlinks() ) {
			
			printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Assertlinks generated successfully. You can <a href="%s" target="_blank">See it here &rarr;</a>', 'aione-pwa' ) . '</p>', aione_pwa_assertlinks( 'src' ) );
		} else {
			
			printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Assertlinks generation failed. <a href="%s" target="_blank">Fix it &rarr;</a>', 'aione-pwa' ) . '</p>', 'https://oxosolutions.com/' );
		}
	}
	function aione_pwa_https_status_cb() {
		if ( is_ssl() ) {
			
			printf( '<p><span class="dashicons dashicons-yes" style="color: #46b450;"></span> ' . __( 'Your website is served over HTTPS.', 'aione-pwa' ) . '</p>' );
		} else {
			
			printf( '<p><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Progressive Web Apps require that your website is served over HTTPS. Please contact your host to add a SSL certificate to your domain.', 'aione-pwa' ) . '</p>' );
		}
	}

}
