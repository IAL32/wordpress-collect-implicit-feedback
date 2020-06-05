<?php

// https://stackoverflow.com/a/254543
namespace Coimf {

abstract class Enum {

    private static $cConstCacheArray = NULL;

    private static function getConstants() {
        if ( self::$cConstCacheArray == NULL ) {
            self::$cConstCacheArray = [];
        }
        $vCalledClass = get_called_class();
        if ( !array_key_exists( $vCalledClass, self::$cConstCacheArray ) ) {
            $vReflect = new \ReflectionClass( $vCalledClass );
            self::$cConstCacheArray[$vCalledClass] = $vReflect->getConstants();
        }
        return self::$cConstCacheArray[$vCalledClass];
    }

    public static function isValidName( $aName, $aStrict = false ) {
        $vConstants = self::getConstants();

        if ( $aStrict ) {
            return array_key_exists( $aName, $vConstants );
        }

        $vKeys = array_map( "strtolower", array_keys( $vConstants ) );
        return in_array( strtolower( $aName ), $vKeys );
    }

    public static function isValidValue( $aValue, $aStrict = true ) {
        $vValues = array_values( self::getConstants() );
        return in_array( $aValue, $vValues, $aStrict );
    }
}

}
