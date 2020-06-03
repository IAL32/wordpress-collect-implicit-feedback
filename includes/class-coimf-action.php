<?php

abstract class Coimf_Action_Type extends Coimf_Enum {
    const None = -1;
    const InternalLink = 0;
    const Click = 1;
}

class Coimf_Action {

    public function __construct( string $aPluginName ) {
        $this->mPluginName = $aPluginName;
    }

    public function registerEndpoint() : void {
        register_rest_route(
            $this->mPluginName . "/" . self::cAPIVersion,
            "/track/(?P<mouseX>\d+),(?P<mouseY>\d+)",
            [
                "methods" => WP_REST_Server::CREATABLE,
                "callback" => [ $this, "addClickPositionCallback" ]
            ]
        );
    }

    public function addClickPositionCallback( WP_REST_Request $aRequest ) {
        if ( is_admin() ) {
            return new WP_REST_Response( [ "message" => "Not logging admin clicks" ], 200 );
        }

        $vMouseX = $aRequest->get_param( "mouseX" );
        $vMouseY = $aRequest->get_param( "mouseY" );
        $vPageLocation = $aRequest->get_param( "pageLocation" );

        if ( $vMouseX < 0 || $vMouseY < 0 ) {
            return new WP_Error( "Coimf_Action::addClickPositionCallback()", "Mouse position not valid" );
        }

        $vCookie = Coimf_Cookie::getCookie();

        self::addClickPosition( $vCookie->getGUID(), $vMouseX, $vMouseY, $vPageLocation , time() );

        return new WP_REST_Response( [ "message" => "Click logged" ], 200 );
    }

    public static function getAction( $aActionID ) {
        $vDB = Coimf_DB::getInstance();

        $vTableName = $vDB->getDataTableName();
        $vQuery = $vDB->prepare(
            "SELECT * FROM %s WHERE id = %s", $vTableName, $aActionID
        );

        return $vDB->getRow( $vQuery );
    }

    public static function getAllActions( array $aArgs = [] ) {
        $vArgs = array_merge( $aArgs, [ "vLimitStart" => -1 ] );
        return self::getActions( $vArgs );
    }

    public static function getActions( array $aArgs = [] ) {

        $vDefaults = [
            "vLimitStart" => 20,
            "vLimitEnd" => -1,
            "vTimeStart" => 0,
            "vTimeEnd" => 0,
            "vSelect" => ["*"],
        ];

        $vArgs = array_merge( $vDefaults, $aArgs );
        extract( $vArgs );
        
        $vSelect = implode( ",", $vSelect );

        $vDB = Coimf_DB::getInstance();
        $vTableName = $vDB->getDataTableName();

        if ( !$vTimeStart && !$vTimeEnd ) {
            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
            ", $vTableName );
        } else if ( !$vTimeStart && $vTimeEnd > 0 ) {
            $vTimeEndDateTime = $vDB->timestampToMYSQLDateTime( $vTimeEnd );

            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
                WHERE time_end = %s
            ", $vTableName, $vTimeEndDateTime );
        } else if ( $vTimeStart > 0 && !$vTimeEnd ) {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $vTimeStart );

            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
                WHERE id = %s AND
                time_start = %s
            ", $vTableName, $vTimeStartDateTime );
        } else {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $vTimeStart );
            $vTimeEndDateTime = $vDB->timestampToMYSQLDateTime( $vTimeEnd );

            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
                WHERE time_start = %s AND
                time_end = %s
            ", $vTableName, $vTimeStartDateTime, $vTimeEndDateTime );
        }

        if ( $vLimitStart > -1 ) {
            $vQuery .= " LIMIT {$vLimitStart}";
            if ( $vLimitEnd > -1 ) {
                $vQuery .= ", {$vLimitEnd}";
            }
        }

        return $vDB->getResults( $vQuery );
    }

    public static function addAction( int $aActionType, string $aUserGUID, $aValue, int $aTimeStart, int $aTimeEnd ) {
        $vDB = Coimf_DB::getInstance();

        $vTableName = $vDB->getDataTableName();

        $vQuery = $vDB->prepare( "
            INSERT INTO %s
            COLUMNS ( user_id, action_type, value, time_start, time_end )
            VALUES ( %s, %s, %s, %s, %s )",
            $vTableName, $aUserGUID, $aActionType, $aValue, $aTimeStart, $aTimeEnd );

        return $vDB->query( $vQuery );
    }

    public static function addInternalLinkAction( string $aUserGUID, string $aFromLink, string $aToLink, int $aTime ) {
        $vValue = json_encode([
            "from" => $aFromLink,
            "to" => $aToLink,
        ]);

        return self::addAction( Coimf_Action_Type::InternalLink, $aUserGUID, $vValue, $aTime, $aTime );
    }

    public static function addClickPosition( string $aUserGUID, int $aMouseX, int $aMouseY, $aPageURL, $aTime ) {
        $vValue = json_encode([
            "mouseX" => $aMouseX,
            "mouseY" => $aMouseY,
            "location" => $aPageURL,
        ]);

        return self::addAction( Coimf_Action_Type::Click, $aUserGUID, $vValue, $aTime, $aTime );
    }

    private string $mPluginName;
    private const cAPIVersion = "v1";

}
