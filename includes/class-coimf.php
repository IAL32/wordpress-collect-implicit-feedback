<?php

class Coimf {

	/**
	 * The loader that"s responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Coimf_Loader    $mLoader    Maintains and registers all hooks for the plugin.
	 */
	protected $mLoader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $mCoimf    The string used to uniquely identify this plugin.
	 */
	protected $mCoimf;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $mVersion    The current version of the plugin.
	 */
	protected $mVersion;

	protected $mDB;

	protected $mAction;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( "COIMF_VERSION" ) ) {
			$this->mVersion = COIMF_VERSION;
		} else {
			$this->mVersion = "1.0.0";
		}

		if ( defined( "COIMF_API_VERSION" ) ) {
			$this->mAPIVersion = COIMF_API_VERSION;
		} else {
			$this->mAPIVersion = "v1";
		}

		$this->mCoimf = "coimf";

		$this->loadDependencies();
		$this->defineAdminHooks();
		$this->definePublicHooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Coimf_Loader. Orchestrates the hooks of the plugin.
	 * - Coimf_i18n. Defines internationalization functionality.
	 * - Coimf_Admin. Defines all hooks for the admin area.
	 * - Coimf_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function loadDependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-enum.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-activator.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-action.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-cookie.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-db.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-deactivator.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-loader.php";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-coimf-logger.php";

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . "admin/class-coimf-admin.php";

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . "public/class-coimf-public.php";

		$this->mLoader = new \Coimf\Loader();
		$this->mAction = new \Coimf\Action( $this->mCoimf );
		$this->mDB = \Coimf\DB::getInstance();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function defineAdminHooks() {

		$vPluginAdmin = new \Coimf\Admin_Handler( $this->getCoimf(), $this->getVersion() );

		$this->mLoader->addAction( "admin_enqueue_scripts", $vPluginAdmin, "enqueueStyles" );
		$this->mLoader->addAction( "admin_enqueue_scripts", $vPluginAdmin, "enqueueScripts" );
		$this->mLoader->addAction( "admin_init", $vPluginAdmin, "registerSettings" );
		$this->mLoader->addAction( "admin_menu", $vPluginAdmin, "addMenuPage" );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function definePublicHooks() {

		$vPluginPublic = new \Coimf\Public_Handler( $this->getCoimf(), $this->getVersion() );

		$this->mLoader->addAction( "init", $vPluginPublic, "handleSessionStart" );
		$this->mLoader->addAction( "rest_api_init", $this->mAction, "registerEndpoint" );
		$this->mLoader->addAction( "wp_enqueue_scripts", $vPluginPublic, "enqueueStyles" );
		$this->mLoader->addAction( "wp_enqueue_scripts", $vPluginPublic, "enqueueScripts" );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->mLoader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function getCoimf() {
		return $this->mCoimf;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Coimf_Loader    Orchestrates the hooks of the plugin.
	 */
	public function getLoader() {
		return $this->mLoader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function getVersion() {
		return $this->mVersion;
	}

}
