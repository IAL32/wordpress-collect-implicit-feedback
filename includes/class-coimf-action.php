<?php

abstract class Coimf_Action_Type extends Coimf_Enum {
    const None = -1;
    const InternalLink = 0;
    const Click = 1;
}

class Coimf_Action {

    public function __construct( string $aPluginName ) {
        $this->mPluginName = $aPluginName;
        $this->mLogger = new Coimf_Logger( "Coimf_Action" );
    }

    public function registerEndpoint() : void {
        register_rest_route(
            $this->mPluginName . "/" . self::cAPIVersion,
            "/track/(?P<mouseX>\\d+),(?P<mouseY>\\d+)",
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

        $this->addClickPosition( $vCookie->getGUID(), $vCookie->getSession(), $vMouseX, $vMouseY, $vPageLocation , new DateTime( "now" ) );

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
                WHERE time_end <= %s
            ", $vTableName, $vTimeEndDateTime );
        } else if ( $vTimeStart > 0 && !$vTimeEnd ) {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $vTimeStart );

            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
                WHERE id = %s AND
                time_start >= %s
            ", $vTableName, $vTimeStartDateTime );
        } else {
            $vTimeStartDateTime = $vDB->timestampToMYSQLDateTime( $vTimeStart );
            $vTimeEndDateTime = $vDB->timestampToMYSQLDateTime( $vTimeEnd );

            $vQuery = $vDB->prepare( "
                SELECT {$vSelect} from %s
                WHERE time_start >= %s AND
                time_end <= %s
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

    // FIXME: require a Coimf_Cookie instead of userGUID and session
    public function addAction( int $aActionType, string $aUserGUID, string $aSession, $aValue, DateTime $aTimeStart, DateTime $aTimeEnd ) {
        $vDB = Coimf_DB::getInstance();

        $vTableName = $vDB->getDataTableName();

        $vTimeStartFormat = $aTimeStart->format( "Y-m-d H:i:s" );
        $vTimeEndFormat = $aTimeEnd->format( "Y-m-d H:i:s" );

        $vQuery = $vDB->prepare( "
            INSERT INTO %s
            COLUMNS ( user_id, session_id, action_type, value, time_start, time_end )
            VALUES (  %s,       %s,        %s,          %s,    %s,         %s )",
            $vTableName, $aUserGUID, $aSession, $aActionType, $aValue, $vTimeStartFormat, $vTimeEndFormat );

        if ( COIMF_DEBUG ) {
            $this->mLogger->log( 2, "::addAction()", $vQuery );
            return true;
        }

        return $vDB->query( $vQuery );
    }

    public function addInternalLinkAction( string $aUserGUID, string $aSession, string $aFromLink, string $aToLink, DateTime $aTime ) {
        if ((isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0')) {

        }

        $vValue = json_encode([
            "from" => $aFromLink,
            "to" => $aToLink,
        ]);


        return $this->addAction( Coimf_Action_Type::InternalLink, $aUserGUID, $aSession, $vValue, $aTime, $aTime );
    }

    public function addClickPosition( string $aUserGUID, string $aSession, int $aMouseX, int $aMouseY, $aPageURL, DateTime $aTime ) {
        $vValue = json_encode([
            "mouseX" => $aMouseX,
            "mouseY" => $aMouseY,
            "location" => $aPageURL,
        ]);

        return $this->addAction( Coimf_Action_Type::Click, $aUserGUID, $aSession, $vValue, $aTime, $aTime );
    }

    private function isLinkLocal( string $aLink ) {
        $vComponents = parse_url( $aLink );
        // empty host will indicate url like '/relative.php'
        return !empty( $vComponents['host'] ) || strcasecmp( $vComponents['host'], $_SERVER["SERVER_NAME"] );
    }

    private string $mPluginName;
    private Coimf_Logger $mLogger;
    private const cAPIVersion = "v1";

}
