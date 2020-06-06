<?php
namespace Coimf {

class DB {

// Public

    /** Instance of Wordpress database connection */
    public $mWPDB;

    public $mPrefix;

    public static $cTablePrefix = "coimf_";
    public static $cDataTableName = "Actions";

    public static function getInstance() {
        if (self::$mInstance) {
            return self::$mInstance;
        }
        return new self();
    }

    public function getDataTableName() {
        return $this->mWPDB->prefix . self::$cTablePrefix . self::$cDataTableName;
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

    public static function whereQueryFromArgs( array $vFilter = [] ) {
        if ( ! empty( $vFilter ) ) {
            // Necessary 1=1 in order to have simpler AND concatenation of rules
            $vWhereQuery = " WHERE 1=1";
            $vWhereQueryPresent = false;
            foreach ( $vFilter as $vFilterColumn => $vColumnValue ) {
                if ( $vColumnValue === false ) {
                    continue;
                }

                $vWhereQuery .= " AND {$vFilterColumn} {$vColumnValue}";
                $vWhereQueryPresent = true;
            }

            if ( $vWhereQueryPresent ) {
                return $vWhereQuery;
            }
        }

        return "";
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

}
