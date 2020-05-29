<?php

class Coimf_Logger {
    private $mLogPath;

    /**
     * @param integer $aVerbosity Describes the level of verbosity of this logger instance
     */
    public function __construct( $aLogGroup, $aVerbosity = 10 ) {
        $this->mLogPath = wp_get_upload_dir();
    }
}
