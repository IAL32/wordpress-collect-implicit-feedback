<?php
class Coimf_DB {

// Public

    /** Instance of Wordpress database connection */
    public $mWPDB;

    public $mPrefix;

    public static $cTablePrefix = 'coimf_';
    public static $cDataTableName = 'Actions';

    public static function get_instance() {
        if (self::$mInstance) {
            return self::$mInstance;
        }
        return new Coimf_DB();
    }

    public function timestampToMYSQLDateTime( $aTime ) {
        return date( "Y-m-d H:i:s", $aTime );
    }

    public function getDataTableName() {
        return $this->mWPDB->prefix . Coimf_DB::$cTablePrefix . Coimf_DB::$cDataTableName;
    }

    public function query() {
        return $this->mWPDB->query( ...func_get_args() );
    }

    public function getResults() {
        return $this->mWPDB->get_results( ...func_get_args() );
    }

    public function prepare() {
        return $this->mWPDB->prepare( ...func_get_args() );
    }

    public function getVar() {
        return $this->mWPDB->get_var( ...func_get_args() );
    }

    public function getRow() {
        return $this->mWPDB->get_row( ...func_get_args() );
    }

// Private
    
    private function __construct() {
        if (self::$mInstance) {
            return self::$mInstance;
        }

        global $wpdb;

        $this->mWPDB = $wpdb;
    }

    private static $mInstance = null;
}
