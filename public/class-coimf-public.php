<?php

namespace Coimf {

class Public_Handler {

	private \Coimf\Logger $mLogger;

	private \Coimf\Loader $mLoader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->mLogger = new \Coimf\Logger( "Coimf_Public" );
		$this->mLoader = new \Coimf\Loader();
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

		if ( is_admin() ) {
			return;
		}

		if( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) { 
			return;
		}

		wp_enqueue_script(
			"coimf-public",
			plugin_dir_url( __FILE__ ) . "assets/js/coimf-public.js",
			[ "jquery" ],
			COIMF_VERSION,
			false
		);
		wp_localize_script( "coimf-public", "gCoimf", \Coimf\Options::getGlobalCoimfOptions());

		// TODO: make this customizable
		wp_enqueue_script(
			"coimf-track-click",
			plugin_dir_url( __FILE__ ) . "assets/js/coimf-track-click.js",
			[ "jquery" ],
			COIMF_VERSION,
			false
		);
		wp_localize_script( "coimf-track-click", "gCoimf", \Coimf\Options::getGlobalCoimfOptions());

		// only tracking page time on articles
		if ( is_single() ) {
			wp_enqueue_script(
				"coimf-track-page-time",
				plugin_dir_url( __FILE__ ) . "assets/js/coimf-track-page-time.js",
				[ "jquery" ],
				COIMF_VERSION,
				false
			);
			wp_localize_script( "coimf-track-page-time", "gCoimf", \Coimf\Options::getGlobalCoimfOptions());
		}

	}

	private function refererAction() : void {

		// not loggin in admin area
		if ( is_admin() ) {
			return;
		}

		// not loggin users that can edit posts or ar administrator
		if( current_user_can('editor') || current_user_can('administrator') ) { 
			return;
		}

		// page was refreshed
		if ( $this->isPageRefresh() ) {
			return;
		}

		// we do not log ajax requests
		if ( $this->isRequestAjax() ) {
			return;
		}

		$vHTTPReferer = $this->getHTTPReferrer();
		$vCurrentSlug = $_SERVER["REQUEST_URI"];

		$this->mLogger->log( LogLevel::INFO, $vHTTPReferer, ";", $vCurrentSlug );
		$this->mLogger->log( LogLevel::INFO, "Is being tracked:", var_export( $this->isPageBeingTracked( $vCurrentSlug ), true ) );

		if ( ! $this->isPageBeingTracked( $vCurrentSlug ) ) {
			return;
		}

		// not logging self-referring navigation
		if ( strcasecmp( $vHTTPReferer, $vCurrentSlug ) == 0 ) {
			return;
		}

		// FIXME: Action should not be instantiated every time. Find a better way
		// to access this
		// Maybe with $mLoader?
		$vAction = new \Coimf\Action();
		$vCookie = \Coimf\Cookie::getCookie();
		$vAction->addInternalLinkAction( $vCookie->getGUID(), $vCookie->getSession(), $vHTTPReferer, $vCurrentSlug, new \DateTime( "now" ) );
	}

	/**
	 * @return string HTTP Referrer, domain if external, or path if internal
	 */
	private function getHTTPReferrer() : string {
		$vHTTPReferer = $_SERVER["HTTP_REFERER"];

		if ( $vHTTPReferer ) {
			$vParsedURL = parse_url( $vHTTPReferer );

			if ( $this->isURLExternal( $vHTTPReferer ) ) {
				// the url is external, so we only log the domain
				$vHTTPReferer = $vParsedURL["host"];
			} else {
				// the url is local, so we will only get the path
				$vHTTPReferer = $vParsedURL["path"];

				// no path, but still local, so we just assume a slash (root dir)
				if ( ! $vHTTPReferer ) {
					$vHTTPReferer = "/";
				}
			}
		} else {
			// no referer detected, might as well say that its empty
			$vHTTPReferer = "";
		}

		return $vHTTPReferer;
	}

	private function isURLExternal( string $aURL ) : bool {
		$vURLComponents = parse_url( $aURL );
		// empty host will indicate url like '/relative.php'
		return ! empty( $vURLComponents["host"] )
				&& strcasecmp( $vURLComponents["host"], $_SERVER["SERVER_NAME"] ) != 0;
	}

	private function isPageRefresh() : bool {
		return isset($_SERVER["HTTP_CACHE_CONTROL"]) && $_SERVER["HTTP_CACHE_CONTROL"] == "max-age=0";
	}

	private function isRequestAjax() : bool {
		return !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 
				strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
	}

	private function isBot() : bool {
		return (
			isset( $_SERVER["HTTP_USER_AGENT"] )
			&& preg_match( "/bot|crawl|slurp|spider|mediapartners/i", $_SERVER["HTTP_USER_AGENT"] )
		);
	}

	private function isPageBeingTracked( string $aURL ) : bool {

		$vForbiddenToTrackPages = \Coimf\Options::getForbiddenToTrackPages();

		foreach ( $vForbiddenToTrackPages as $vForbiddenPage ) {
			if ( strstr( $aURL, $vForbiddenPage ) ) {
				return false;
			}
		}

		$vTrackedPages = \Coimf\Options::getTrackedPages();

		foreach ( $vTrackedPages as $vTrackedPage ) {
			if ( substr( $aURL, 0, strlen( $vTrackedPage ) ) == $vTrackedPage ) {
				return true;
			}
		}

		return false;
	}

}

}
