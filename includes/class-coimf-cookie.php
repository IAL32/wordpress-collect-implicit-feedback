<?php

namespace Coimf {

class Cookie {

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
            "\Coimf\Cookie( %1\$s = %2\$s; %3\$s = %4\$s; %5\$s = %6\$s; %7\$s = %8\$s )",
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

    public static function getCookie() : \Coimf\Cookie {
        $vCookie = [];
        $vCurrentTime = TimeFunctions::now();
        $vNewSessionExpireTime = clone $vCurrentTime;
        \Coimf\TimeFunctions::addSecondsToDateTime( $vNewSessionExpireTime, self::cSessionExpireTime );

        if ( ! COIMF_COOKIE_FORCE && isset( $_COOKIE[self::cCookieName] ) && self::isCookieValid( $_COOKIE[self::cCookieName] ) ) {

            $vExistingCookie = $_COOKIE[self::cCookieName];

            $vCookieSessionTimeEnd = \DateTime::createFromFormat( self::cTimestampFormat, $vExistingCookie["time_end"] );

            if ( self::isSessionValid( $vCurrentTime, $vCookieSessionTimeEnd ) ) {
                $vSessionID = $vExistingCookie["session_id"];
                Logger::sLog( "Coimf_Cookie", 2, "Keeping old session ID: ", $vSessionID );
            } else {
                $vSessionID = self::generateGUID();
                Logger::sLog( "Coimf_Cookie", 2, "Generating new session ID: ", $vSessionID );
            }

            $vCookie = [
                "user_id"          => $vExistingCookie["user_id"],
                "session_id"       => $vSessionID,
                "time_start"    => $vExistingCookie["time_start"],
                "time_end"      => $vNewSessionExpireTime->format( self::cTimestampFormat ),
            ];

        } else {
            $vCookie = [
                "user_id"          => self::generateGUID(),
                "session_id"       => self::generateGUID(),
                "time_start"    => $vCurrentTime->format( self::cTimestampFormat ),
                "time_end"      => $vNewSessionExpireTime->format( self::cTimestampFormat ),
            ];
        }

        $vNewCookieExpireTime = clone $vCurrentTime;
        \Coimf\TimeFunctions::addSecondsToDateTime( $vNewCookieExpireTime, self::cCookieExpireTime );

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

    public function getTimeStart() : \DateTime {
        return $this->mTimeStart;
    }

    public function getTimEnd() : \DateTime {
        return $this->mTimeEnd;
    }

    private function __construct( $aCookieArgs ) {
        $this->mGUID = $aCookieArgs["user_id"];
        $this->mSession = $aCookieArgs["session_id"];
        $this->mTimeStart = \DateTime::createFromFormat( self::cTimestampFormat, $aCookieArgs["time_start"] );
        $this->mTimeEnd = \DateTime::createFromFormat( self::cTimestampFormat, $aCookieArgs["time_end"] );

        $this->mLogger = new \Coimf\Logger( "\Coimf\Cookie" );
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

    /**
     * A session is considered to be valid when the current time is in the past
     * with respect to the session end time.
     */
    private static function isSessionValid( \DateTime $aCurrentTime, \DateTime $aSessionEndTime ) : bool {
        $vValid = $aCurrentTime < $aSessionEndTime;
        if ( $aCurrentTime < $aSessionEndTime ) {
            Logger::sLog( "Coimf_Cookie", 2, "Session Valid: ");
            Logger::sLog( "Coimf_Cookie", 2, "Current Time ", $aCurrentTime->format( self::cTimestampFormat ), "Time End", $aSessionEndTime->format( self::cTimestampFormat ) );
        } else {
            Logger::sLog( "Coimf_Cookie", 2, "Session not valid");
            Logger::sLog( "Coimf_Cookie", 2, "Current Time ", $aCurrentTime->format( self::cTimestampFormat ), "Time End", $aSessionEndTime->format( self::cTimestampFormat ) );
        }
        return $vValid;
    }

    private string $mGUID;
    private string $mSession;
    private \DateTime $mTimeStart;
    private \DateTime $mTimeEnd;

    private const cCookieName = "coimf";

    /** The maximum session duration, in seconds (default: 30 minutes) */
    public const cSessionExpireTime = 30 * 60;

    /** When the cookie expires, in seconds (default: 1 day) */
    public const cCookieExpireTime = 24 * 60 * 60;

    private const cTimestampFormat = "Y-m-d H:i:s e";
}

}
