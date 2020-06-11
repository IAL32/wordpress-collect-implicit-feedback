<?php

namespace Coimf {

class Admin_Handler {

	private \Coimf\Action_Table $mActionsTable;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $aPluginName       The name of this plugin.
	 * @param      string    $aVersion    The version of this plugin.
	 */
	public function __construct() {
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
		add_thickbox();
		wp_enqueue_style( "thickbox", "/" . WPINC . "/js/thickbox/thickbox.css", null, "1.0" );
		wp_enqueue_style( "jquery-ui-style", COIMF_ROOT_URL . "assets/css/jquery-ui-base.css", null, "1.12.0" );
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

		wp_enqueue_script( "jquery-ui-datepicker", null, [ "jquery" ] );
		wp_enqueue_script( "thickbox", null, [ "jquery" ] );
		wp_enqueue_script( "d3js-v4", plugin_dir_url( __FILE__ ) . "partials/assets/js/vendor/d3js.v4.min.js", [ "jquery" ], "4.0.0", false );
		wp_enqueue_script( "d3-legend", plugin_dir_url( __FILE__ ) . "partials/assets/js/vendor/d3-legend.min.js", [ "jquery", "d3js-v4" ], "2.25.6", false );
		wp_enqueue_script( "html2canvas", plugin_dir_url( __FILE__ ) . "partials/assets/js/vendor/html2canvas.min.js", [ "jquery" ], "1.0.0-rc5", false );
		wp_enqueue_script( "moment", plugin_dir_url( __FILE__ ) . "partials/assets/js/vendor/moment.min.js", [ "jquery" ], "2.26.0", false );

	}

	public function registerSettings() {
		register_setting( "coimf-settings-group", "coimf-track-page-selector" );
		register_setting( "coimf-settings-group", "coimf-track-user-clicks" );
		register_setting( "coimf-settings-group", "coimf-track-slug" );
		register_setting( "coimf-settings-group", "coimf-track-min-read-time-seconds" );
		register_setting( "coimf-settings-group", "coimf-track-max-read-time-seconds" );

		add_option( "coimf-track-page-selector", ".post .entry-content" );
		add_option( "coimf-track-user-clicks", "1" );
		add_option( "coimf-track-slug", "/" );
		add_option( "coimf-track-min-read-time-seconds", 2 );
		add_option( "coimf-track-max-read-time-seconds", HOUR_IN_SECONDS );
	}

	public function addMenuPage() {
		$vMenuPageHook = add_menu_page(
			COIMF_NAME,
			"Track User Data",
			"manage_options",
			"coimf-admin-display",
			[ $this, "mainPage" ],
			COIMF_ROOT_URL . "icon.ico"
		);

		add_action( "load-${vMenuPageHook}", [ $this, "initActionsTable" ] );

		add_submenu_page(
			"coimf-admin-display",
			"Scroll Time Statistics",
			"Scroll Time Statistics",
			"manage_options",
			"coimf-admin-display/scroll-time-statitics",
			[ $this, "scrollTimeStatisticsPage" ]
		);

		add_submenu_page(
			"coimf-admin-display",
			"Session Statistics",
			"Session Statistics",
			"manage_options",
			"coimf-admin-display/session-statitics",
			[ $this, "sessionStatisticsPage" ]
		);

		add_submenu_page(
			"coimf-admin-display",
			"Settings",
			"Settings",
			"manage_options",
			"coimf-admin-display/settings",
			[ $this, "settingsPagePage" ]
		);
	}

	public function initActionsTable() {
		$this->mActionsTable = new \Coimf\Action_Table();
	}

	public function mainPage() {		
		add_screen_option( "per_page", [
			"label"		=> __( "Actions per page", "coimf" ),
			"default"	=> 5,
			"option"	=> "actions_per_page"
		]);

		$this->mActionsTable->prepare_items();

		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-display.php" );
	}

	public function scrollTimeStatisticsPage() {
		wp_enqueue_script( "scroll-time-heatmap", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-read-time-heatmap.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "scroll-time-heatmap", "cHeatmapNonce", wp_create_nonce( "wp_rest" ) );

		wp_enqueue_script( "scroll-time-barplot", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-read-time-barplot.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "scroll-time-barplot", "cBarplotNonce", wp_create_nonce( "wp_rest" ) );

		wp_enqueue_script( "scroll-time-lineplot", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-read-time-lineplot.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "scroll-time-lineplot", "cLineplotNonce", wp_create_nonce( "wp_rest" ) );

		wp_enqueue_script( "scroll-time-histogram", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-read-time-histogram.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "scroll-time-histogram", "cHistogramNonce", wp_create_nonce( "wp_rest" ) );

		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-page-read-time-statistics.php" );
	}

	public function sessionStatisticsPage() {
		wp_enqueue_script( "session-heatmap", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-session-heatmap.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "session-heatmap", "cHeatmapNonce", wp_create_nonce( "wp_rest" ) );

		wp_enqueue_script( "session-histogram", plugin_dir_url( __FILE__ ) . "partials/assets/js/coimf-admin-page-session-histogram.js", [ "jquery", "coimf-custom-prototypes" ], COIMF_VERSION, false );
		wp_localize_script( "session-histogram", "cHistogramNonce", wp_create_nonce( "wp_rest" ) );

		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-page-session-statistics.php" );
	}

	public function settingsPagePage() {
		echo '<p>These settings apply to all Coimf functionality.</p>';
		include_once( plugin_dir_path( __FILE__ ) . "partials/coimf-admin-settings.php" );
	}

}

}
