<?php
class Coimf_DB {
    public static $cTablePrefix = 'coimf_';
    public static $cDataTableName = 'Data';

    private static $mInstance = null;
    
    private function __construct() {
        if (self::$mInstance) {
            return self::$mInstance;
        }
    }

    public static function get_instance() {
        if (self::$mInstance) {
            return self::$mInstance;
        }
        return new Coimf_DB();
    }
}