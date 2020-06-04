<?php

class Coimf_Public {

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

	private $mCookie;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $aPluginName       The name of the plugin.
	 * @param      string    $aVersion    The version of this plugin.
	 */
	public function __construct( $aPluginName, $aVersion ) {

		$this->mPluginName = $aPluginName;
		$this->mVersion = $aVersion;
		$this->mCookie = Coimf_Cookie::getCookie();

	}

	public function handleSessionStart() {
		$this->refererAction();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles() {

		// wp_enqueue_style( $this->mPluginName, plugin_dir_url( __FILE__ ) . "css/coimf-public.css", array(), $this->mVersion, "all" );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts() : void {

		$vCoimf = [
			"mPluginName" => $this->mPluginName,
			"mVersion" => $this->mVersion,
			"mAPIVersion" => $this->mAPIVersion,
			"mIsUserAdmin" => is_admin(),
		];

		wp_enqueue_script(
			"coimf-public",
			plugin_dir_url( __FILE__ ) . "public/assets/js/coimf-public.js",
			[ "jquery" ],
			$this->mVersion,
			false
		);
		wp_localize_script( "coimf-public", "gCoimf", $vCoimf);

		// TODO: make this customizable
		wp_enqueue_script(
			"coimf-track-click",
			plugin_dir_url( __FILE__ ) . "public/assets/js/coimf-track-click.js",
			[ "jquery" ],
			$this->mVersion,
			false
		);
		wp_localize_script( "coimf-track-click", "gCoimf", $vCoimf);

	}

	private function refererAction() : void {
		global $wp;
		$vHTTPReferer = wp_get_referer();
		if ( !$vHTTPReferer ) {
			return;
		}

		$vCurrentSlug = "/" . add_query_arg( [], $wp->request );

		// FIXME: this should not be instantiated every time. Find a better way
		// to access members from Coimf class
		$vAction = new Coimf_Action( $this->mPluginName );
		$vAction->addInternalLinkAction( $this->mCookie->getGUID(), $this->mCookie->getSession(), $vHTTPReferer, $vCurrentSlug, new DateTime( "now" ) );
	}

}
