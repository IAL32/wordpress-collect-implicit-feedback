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

    public const cTrackedPagesGlue = ";";

}

}
