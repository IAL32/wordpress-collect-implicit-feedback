<?php

class Coimf_Cookie {

    /** Generates a globally unique identifier .*/
    public static function generateGUID() : string {
        $vRand = [];
        for ( $i = 0; $i < 8; $i++ ) {
            $vRand[] = mt_rand(0, 65535);
        }

        return sprintf( "%04X%04X-%04X-%04X-%04X-%04X%04X%04X", ...$vRand);
    }

    public static function getCookie() : Coimf_Cookie {
        $vCookie = [];

        if ( isset( $_COOKIE[self::cCookieName] ) ) {
            foreach( $_COOKIE[self::cCookieName] as $vKey => $vValue ) {
                $vCookie[$vKey] = $vValue;
                setcookie(
                    self::cCookieName . "[" . $vKey . "]",
                    $vValue,
                    // postpones cookie expiration
                    time() + self::cSessionLength
                );
            }
        } else {
            $vCookie = [
                "guid"          => self::generateGUID(),
                "time_start"    => time(),
                "time_end"      => time() + self::cSessionLength,
            ];
        }

        return new self( $vCookie );
    }

    public function getGUID() : string {
        return $this->mGUID;
    }

    public function getTimeStart() : int {
        return $this->mTimeStart;
    }

    public function getTimEnd() : int {
        return $this->mTimeEnd;
    }

    private function __construct( $aCookieArgs ) {
        $this->mGUID = $aCookieArgs["guid"];
        $this->mTimeStart = $aCookieArgs["time_start"];
        $this->mTimeEnd = $aCookieArgs["time_end"];
    }

    private string $mGUID;
    private int $mTimeStart;
    private int $mTimeEnd;

    private const cCookieName = "coimf";

    /** The maximum session duration, in seconds (default: 30 minutes) */
    public const cSessionLength = 30 * 60;
}
