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

        if ( isset( $_COOKIE[self::cCookieName] ) && self::isCookieValid( $_COOKIE[self::cCookieName] ) ) {
            $vExistingCookie = $_COOKIE[self::cCookieName];
            if ( $vCurrentTime >= $vExistingCookie["session"] ) {
                $vSessionID = self::generateGUID();
            } else {
                $vSessionID = $vExistingCookie["session"];
            }

            $vNewSessionExpireTime = DateTime::createFromFormat( self::cTimestampFormat, $vExistingCookie["time_end"] );
            $vNewSessionExpireTime->add( new DateInterval( "PT" . self::cCookieExpireTime . "S" ) );
            $vCookie = [
                "user_id"          => $vExistingCookie["user_id"],
                "session_id"       => $vSessionID,
                "time_start"    => DateTime::createFromFormat( self::cTimestampFormat, $vExistingCookie["time_start"] ),
                "time_end"      => $vNewSessionExpireTime,
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
                $vNewCookieExpireTime->format( self::cTimestampFormat )
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
    }

    private static function isCookieValid( array $aCookie ) {
        return !array_diff_key(array_flip( array_keys( $_COOKIE[self::cCookieName] ) ), [
            "user_id", "session_id", "time_start", "time_end" ]);
    }

    private string $mGUID;
    private string $mSession;
    private DateTime $mTimeStart;
    private DateTime $mTimeEnd;

    private const cCookieName = "coimf";

    /** The maximum session duration, in seconds (default: 30 minutes) */
    public const cSessionLength = 30 * 60;

    /** When the cookie expires, in seconds (default: 60 minutes) */
    public const cCookieExpireTime = 60 * 60;

    private const cTimestampFormat = "Y-m-d H:i:s";
}
