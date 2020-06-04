<?php

class Coimf_Cookie {

    public function __serialize() : array {
        return [
            "user_id"          => $this->mGUID,
            "session_id"       => $this->mSession,
            "time_start"    => $this->mTimeStart,
            "time_end"      => $this->mTimeEnd,
        ];
    }

    public function __unserialize( array $aData ): void {
        $this->mGUID = $aData["user_id"];
        $this->mSession = $aData["session_id"];
        $this->mTimeStart = $aData["time_start"];
        $this->mTimeEnd = $aData["time_end"];
    }

    public function __toString() {
        return sprintf(
            "Coimf_Cookie( %1\$s = %2\$s; %3\$s = %4\$s; %5\$s = %6\$s; %7\$s = %8\$s )",
            "mGUID", $this->mGUID,
            "mSession", $this->mSession,
            "mTimeStart", $this->mTimeStart->format( self::cTimestampFormat ),
            "mTimeEnd", $this->mTimeEnd->format( self::cTimestampFormat )
        );
    }

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
        $vCurrentTime = new DateTime( "now" );

        if ( ! COIMF_COOKIE_FORCE && isset( $_COOKIE[self::cCookieName] ) &&
            self::isCookieValid( $_COOKIE[self::cCookieName] ) ) {

            $vExistingCookie = $_COOKIE[self::cCookieName];

            $vTimeEndTime = DateTime::createFromFormat( self::cTimestampFormat, $vExistingCookie["time_end"] );
            
            if ( $vCurrentTime >= $vTimeEndTime ) {
                $vSessionID = self::generateGUID();
                $vNewSessionExpireTime = clone $vTimeEndTime;
            } else {
                $vSessionID = $vExistingCookie["session_id"];
                $vNewSessionExpireTime = clone $vCurrentTime;
                $vNewSessionExpireTime->add( new DateInterval( "PT" . self::cCookieExpireTime . "S" ) );
            }

            $vCookie = [
                "user_id"          => $vExistingCookie["user_id"],
                "session_id"       => $vSessionID,
                "time_start"    => $vExistingCookie["time_start"],
                "time_end"      => $vNewSessionExpireTime->format( self::cTimestampFormat ),
            ];
        } else {
            $vNewSessionExpireTime = clone $vCurrentTime;
            $vNewSessionExpireTime->add( new DateInterval( "PT" . self::cCookieExpireTime . "S" ) );
            $vCookie = [
                "user_id"          => self::generateGUID(),
                "session_id"       => self::generateGUID(),
                "time_start"    => $vCurrentTime->format( self::cTimestampFormat ),
                "time_end"      => $vNewSessionExpireTime->format( self::cTimestampFormat ),
            ];
        }
        $vNewCookieExpireTime = clone $vCurrentTime;
        $vNewCookieExpireTime->add( new DateInterval( "PT" . self::cCookieExpireTime . "S" ) );
        foreach( $vCookie as $vKey => $vValue ) {
            setcookie(
                self::cCookieName . "[" . $vKey . "]",
                $vValue,
                $vNewCookieExpireTime->getTimestamp()
            );
        }

        return new self( $vCookie );
    }

    public function getGUID() : string {
        return $this->mGUID;
    }

    public function getSession() : string {
        return $this->mSession;
    }

    public function getTimeStart() : DateTime {
        return $this->mTimeStart;
    }

    public function getTimEnd() : DateTime {
        return $this->mTimeEnd;
    }

    private function __construct( $aCookieArgs ) {
        $this->mGUID = $aCookieArgs["user_id"];
        $this->mSession = $aCookieArgs["session_id"];
        $this->mTimeStart = DateTime::createFromFormat( self::cTimestampFormat, $aCookieArgs["time_start"] );
        $this->mTimeEnd = DateTime::createFromFormat( self::cTimestampFormat, $aCookieArgs["time_end"] );

        $this->mLogger = new Coimf_Logger( "Coimf_Cookie" );
    }

    private static function isCookieValid( array $aCookie ) {
        $vCount = 0;
        $vKeys = [ "user_id", "session_id", "time_start", "time_end" ];
        foreach ( $vKeys as $key ) {
            if ( isset( $aCookie[$key] ) || array_key_exists( $key, $aCookie ) ) {
                $vCount ++;
            }
        }
        return count( $vKeys ) === $vCount;
    }

    private string $mGUID;
    private string $mSession;
    private DateTime $mTimeStart;
    private DateTime $mTimeEnd;

    private Coimf_Logger $mLogger;

    private const cCookieName = "coimf";

    /** The maximum session duration, in seconds (default: 30 minutes) */
    public const cSessionLength = 30 * 60;

    /** When the cookie expires, in seconds (default: 1 day) */
    public const cCookieExpireTime = 24 * 60 * 60;

    private const cTimestampFormat = "Y-m-d H:i:s e";
}
