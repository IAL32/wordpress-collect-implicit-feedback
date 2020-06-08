<?php

namespace Coimf {

class Options {

    // FIXME: this has to be an array of options, not a string to explode
    public static function getTrackedPages() : array {
        $vTrackedPagesOption = get_option( "coimf-track-slug" );

        if ( is_array( $vTrackedPagesOption ) ) {
            return $vTrackedPagesOption;
        }

        return explode( self::cTrackedPagesGlue, $vTrackedPagesOption );
    }
    
    // FIXME: this has to be an array of options changeable by the user
    public static function getForbiddenToTrackPages() : array {
        return [
            "wp-cron.php",
            "wp-json",
            "wp-admin",
            "/feed",
            "gbjson"
        ];
    }

    public static function getGlobalCoimfOptions() {
        return [
			"mPluginName" => COIMF_NAME,
			"mVersion" => COIMF_VERSION,
			"mIsUserAdmin" => is_admin() ? "true" : "false",
			"mSettings" => [
				"mPageTrackSelector" => get_option( "coimf-track-page-selector" ),
            ],
            // FIXME: use functions to have dynamically loaded constants for base functions
            "cMYSQLDateTimeFormat" => \Coimf\TimeFunctions::cMYSQLDateTimeFormat,
            "cJsMYSQLDateTimeFormat" => \Coimf\TimeFunctions::cJsMYSQLDateTimeFormat,
            "mSiteURL"  => get_site_url(),
		];
    }

    public const cTrackedPagesGlue = ";";

}

}
