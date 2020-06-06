<?php

namespace Coimf {

class BaseFunctions {

    

}

class TimeType extends Enum {
    public const Seconds    = 0;
    public const Minutes    = 1;
    public const Hours      = 2;
    public const Days       = 3;
    public const Weeks      = 4;
    public const Months     = 5;
    public const Years      = 6;
}

class TimeFunctions {

    public static function now() : \DateTime {
        return new \DateTime( "now" );
    }

    public static function addTimeToDateTime( int $aTimeType, \DateTime $aDateTime, int $aTime = 0 ) : void {
        if ( $aTime == 0 ) {
            return;
        }

        $vTimeToAddInSeconds = 0;

        switch( $aTimeType ) {
            case TimeType::Seconds:
                $vTimeToAddInSeconds = $aTime;
                break;
            case TimeType::Minutes:
                $vTimeToAddInSeconds = $aTime * MINUTE_IN_SECONDS;
                break;
            case TimeType::Hours:
                $vTimeToAddInSeconds = $aTime * HOUR_IN_SECONDS;
                break;
            case TimeType::Days:
                $vTimeToAddInSeconds = $aTime * DAY_IN_SECONDS;
                break;
            case TimeType::Weeks:
                $vTimeToAddInSeconds = $aTime * WEEK_IN_SECONDS;
                break;
            case TimeType::Months:
                $vTimeToAddInSeconds = $aTime * MONTH_IN_SECONDS;
                break;
            case TimeType::Years:
                $vTimeToAddInSeconds = $aTime * YEAR_IN_SECONDS;
                break;
            default:
                throw ("TimeFunctions::addTimeToDateTime(): invalid time type choice");
        }

        $aDateTime->add( new \DateInterval( "PT{$vTimeToAddInSeconds}S" ) );
    }

    public static function addSecondsToDateTime( \DateTime $aDateTime, int $aSeconds = 0 ) {
        self::addTimeToDateTime( TimeType::Seconds, $aDateTime, $aSeconds );
    }

    public static function addMinutesToDateTime( \DateTime $aDateTime, int $aMinutes = 0 ) {
        self::addTimeToDateTime( TimeType::Minutes, $aDateTime, $aMinutes );
    }

    public static function addHoursToDateTime( \DateTime $aDateTime, int $aHours = 0 ) {
        self::addTimeToDateTime( TimeType::Hours, $aDateTime, $aHours );
    }

    public static function addDaysToDateTime( \DateTime $aDateTime, int $aDays = 0 ) {
        self::addTimeToDateTime( TimeType::Days, $aDateTime, $aDays );
    }

    public static function addWeeksToDateTime( \DateTime $aDateTime, int $aWeeks = 0 ) {
        self::addTimeToDateTime( TimeType::Weeks, $aDateTime, $aWeeks );
    }

    public static function addMonthsToDateTime( \DateTime $aDateTime, int $aMonths = 0 ) {
        self::addTimeToDateTime( TimeType::Months, $aDateTime, $aMonths );
    }

}

}