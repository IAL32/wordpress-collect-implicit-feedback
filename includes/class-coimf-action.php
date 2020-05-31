<?php

class Comf_Action {

// FIXME: add pagination!!

    public static function getAllActions( $aTimeStart = 0, $aTimeEnd = 0 ) {
        return self::getActions( -1, $aTimeStart, $aTimeEnd );
    }

    public static function getActions( $aLimit = -1, $aTimeStart = 0, $aTimeEnd = 0 ) {
        $vDB = Coimf_DB::get_instance();
        $vTableName = $vDB->getDataTableName();

        if ( !$aTimeStart && !$aTimeEnd ) {
            $vQuery = $vDB->prepare( "
                SELECT * from %s
            ", $vTableName );
        } else if ( !$aTimeStart && $aTimeEnd > 0 ) {
            $vTimeEndDateTime = $vDB->timestampToMYSQLDateTime( $aTimeEnd );

            $vQuery = $vDB->prepare( "
                SELECT * from %s
                WHERE time_end = %s
            ", $vTableName, $vTimeEndDateTime );
        } else if ( $aTimeStart > 0 && !$aTimeEnd ) {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $aTimeStart );

            $vQuery = $vDB->prepare( "
                SELECT * from %s
                WHERE id = %s AND
                time_start = %s
            ", $vTableName, $vTimeStartDateTime );
        } else {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $aTimeStart );
            $vTimeEndDateTime = $vDB->timestampToMYSQLDateTime( $aTimeEnd );

            $vQuery = $vDB->prepare( "
                SELECT * from %s
                WHERE time_start = %s AND
                time_end = %s
            ", $vTableName, $vTimeStartDateTime, $vTimeEndDateTime );
        }

        if ( $aLimit > -1 ) {
            $vQuery .= " LIMIT {$aLimit}";
        }

        return $vDB->getResult( $vQuery );
    }

    public static function addAction( $aUserID, $aActionType, $aValue, $aTimeStart, $aTimeEnd ) {
        $vDB = Coimf_DB::get_instance();

        $vTableName = $vDB->getDataTableName();

        $vQuery = $vDB->prepare( "
            INSERT INTO %s
            COLUMNS ( user_id, action_type, value, time_start, time_end )
            VALUES ( %s, %s, %s, %s, %s )",
            $vTableName, $aUserID, $aActionType, $aValue, $aTimeStart, $aTimeEnd );

        $vDB->query( $vQuery );
    }
}
