<?php

namespace Coimf {

class Tracker {
    protected static $mInstance;

    public function __construct() {
        if (self::$mInstance) {
            return self::$mInstance;
        }
    }

    public static function getInstance() {
        if (self::$mInstance) {
            return self::$mInstance;
        }

        return new self();
    }
}

}
