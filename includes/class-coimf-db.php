<?php
class Coimf_DB {
    public static $cTablePrefix = 'coimf_';
    public static $cDataTableName = 'Data';

    private static $instance = null;
    
    private function __construct() {
    }

    public static function get_instance() {
        if (!self::$instance) {
            return new Coimf_DB();
        }

        return self::$instance;
    }
}
