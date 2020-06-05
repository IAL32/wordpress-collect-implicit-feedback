<?php

class Coimf_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $mPluginName    The ID of this plugin.
	 */
	private $mPluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $mVersion    The current version of this plugin.
	 */
	private $mVersion;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $aPluginName       The name of this plugin.
	 * @param      string    $aVersion    The version of this plugin.
	 */
	public function __construct( $aPluginName, $aVersion ) {

		$this->mPluginName = $aPluginName;
		$this->mVersion = $aVersion;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Coimf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Coimf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->mPluginName, plugin_dir_url( __FILE__ ) . "css/coimf-admin.css", array(), $this->mVersion, "all" );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Coimf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Coimf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->mPluginName, plugin_dir_url( __FILE__ ) . "assets/js/vendor/html2canvas.js", array( "jquery" ), $this->mVersion, false );

	}

	public function registerSettings() {
		register_setting("coimf-settings-group", "coimf_track_page_selector");
	}

	public function addMenuPage() {
		add_menu_page(
			$this->mPluginName,
			"Track User Data",
			"manage_options",
			"coimf-admin-display",
			[ $this, "mainPage" ],
			COIMF_ROOT_URL . "icon.ico"
		);

		add_submenu_page(
			"coimf-admin-display",
			"Settings",
			"Settings",
			"manage_options",
			"coimf-admin-display/settings",
			[ $this, "settingsPage" ]
		);
	}

	public function mainPage() {
		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-display.php" );
	}

	public function settingsPage() {
		echo '<p>These settings apply to all Coimf functionality.</p>';
		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-settings.php" );
	}

}
