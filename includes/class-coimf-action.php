<?php

namespace Coimf {

abstract class Action_Type extends Enum {
    const None = -1;
    const InternalLink = 0;
    const Click = 1;
    const PageRead = 2;
}

class Action {

    public function __construct() {
        $this->mLogger = new \Coimf\Logger( "Coimf_Action" );
    }

    public function registerEndpoints() : void {
        register_rest_route(
            COIMF_NAME . "/" . self::cAPIVersion,
            "/track-click/",
            [
                "methods" => \WP_REST_Server::CREATABLE,
                "callback" => [ $this, "addClickPositionCallback" ]
            ]
        );
        register_rest_route(
            COIMF_NAME . "/" . self::cAPIVersion,
            "/track-page-time/",
            [
                "methods" => \WP_REST_Server::CREATABLE,
                "callback" => [ $this, "addPageTimeCallback" ]
            ]
        );
        register_rest_route(
            COIMF_NAME . "/" . self::cAPIVersion,
            "/delete/",
            [
                "methods" => \WP_REST_Server::DELETABLE,
                "callback" => [ $this, "deleteActionCallback" ],
                "permission_callback" => function( \WP_REST_Request $aRequest ) {
                    // FIXME: use dedicated function
                    // FIXME: customizable roles
                    return current_user_can( "administrator" );
                }
            ]
        );
        // FIXME: this has to be in an admin class
        // FIXME: better name for read action
        register_rest_route(
            COIMF_NAME . "/" . self::cAPIVersion . "/admin",
            "/get-actions/",
            [
                "methods" => \WP_REST_Server::READABLE,
                "callback" => [ $this, "getActionsCallback" ],
                "permission_callback" => function( \WP_REST_Request $aRequest ) {
                    // FIXME: use dedicated function
                    // FIXME: customizable roles
                    return current_user_can( "administrator" );
                }
            ]
        );
    }

    public function addClickPositionCallback( \WP_REST_Request $aRequest ) {
        if ( is_admin() || current_user_can( "administrator" ) || current_user_can( "editor" ) ) {
            return new \WP_REST_Response( [ "message" => "Not logging admin clicks" ], 200 );
        }

        $vMouseX = $aRequest->get_param( "mouseX" );
        $vMouseY = $aRequest->get_param( "mouseY" );
        $vResolutionX = $aRequest->get_param( "resolutionX" );
        $vResolutionY = $aRequest->get_param( "resolutionY" );
        $vPageLocation = $aRequest->get_param( "pageLocation" );

        if ( $vMouseX < 0 || $vMouseY < 0 ) {
            return new \WP_Error( "Coimf_Action::addClickPositionCallback()", "Mouse position not valid" );
        }

        $vCookie = \Coimf\Cookie::getCookie();

        $vQueryResult = $this->addClickPosition(
            $vCookie->getGUID(),
            $vCookie->getSession(),
            $vMouseX, $vMouseY,
            $vResolutionX, $vResolutionY,
            $vPageLocation,
            new \DateTime( "now" )
        );

        $this->mLogger->log( LogLevel::DEBUG, "::addClickPositionCallback()", var_export( $vQueryResult, true ) );

        return new \WP_REST_Response( [ "message" => "Click logged" ], 200 );
    }

    public function addPageTimeCallback( \WP_REST_Request $aRequest ) {
        // TODO: create function for BaseFunctions where we can set an array of user
        // roles
        if ( is_admin() || current_user_can( "administrator" ) || current_user_can( "editor" ) ) {
            return new \WP_REST_Response( [ "message" => "Not logging admin times" ], 200 );
        }

        $vPageTime = $aRequest->get_param( "pageTime" );
        $vPageLocation = $aRequest->get_param( "pageLocation" );

        $vCookie = \Coimf\Cookie::getCookie();

        $this->addPageTime(
            $vCookie->getGUID(),
            $vCookie->getSession(),
            $vPageTime,
            $vPageLocation,
            new \DateTime( "now" )
        );

        return new \WP_REST_Response( [ "message" => "Click logged" ], 200 );
    }

    public function deleteActionCallback( \WP_REST_Request $aRequest ) {
        // sanity check
        if ( ! current_user_can( "administrator" ) || ! current_user_can( "editor" ) ) {
            return new \WP_REST_Response( [ "message" => "User not allowed" ], 403 );
        }

        $vActionID = intval( $aRequest->get_param( "action_id" ) );

        self::deleteAction( $vActionID );

        return new \WP_REST_Response( [ "message" => "Item deleted" ], 200 );
    }

    public function getActionsCallback( \WP_REST_Request $aRequest ) {
        $vSelect = $aRequest->get_param( "select" );
        $vFilter = $aRequest->get_param( "filter" );
        $vGroupBy = $aRequest->get_param( "groupby" );
        $vLimit = $aRequest->get_param( "limit" );
        $vOffset = $aRequest->get_param( "offset" );

        $vActions = self::getActions([
            "vSelect"   => $vSelect,
            "vFilter"   => $vFilter,
            "vGroupBy"  => $vGroupBy,
            "vLimit"    => $vLimit,
            "vOffset"   => $vOffset,
        ]);

        return new \WP_REST_Response([
            "message"   => "Actions Retrieved",
            "data"      => $vActions,
        ], 200 );
    }

    public static function getAction( $aActionID ) {
        $vDB = \Coimf\DB::getInstance();

        $vTableName = $vDB->getDataTableName();
        $vQuery = $vDB->prepare(
            "SELECT * FROM {$vTableName} WHERE id = %s", $aActionID
        );

        return $vDB->getRow( $vQuery );
    }

    public static function getAllActions( array $aArgs = [] ) {
        $vArgs = array_merge( $aArgs, [ "vLimit" => -1 ] );
        return self::getActions( $vArgs );
    }

    public static function getActions( array $aArgs = [] ) {

        $vDefaults = [
            "vLimit"        => 20,
            "vOffset"       => -1,
            "vOrderBy"      => "time_end",
            "vOrder"        => "DESC",
            "vSelect"       => ["*"],
            "vFilter"       => [
                // FIXME: value needs to be an array, so we can set multiple
                // filters for each column
                // "action_type"   => false,
            ],
            "vGroupBy"      => [],
        ];

        $vArgs = array_merge( $vDefaults, $aArgs );
        extract( $vArgs );

        // select cannot be empty
        if ( empty( $vSelect ) ) {
            $vSelect = "*";
        }

        if ( is_array( $vSelect ) ) {
            $vSelect = implode( ",", $vSelect );
        }

        $vDB = \Coimf\DB::getInstance();
        $vTableName = $vDB->getDataTableName();

        $vQuery = "SELECT {$vSelect} from {$vTableName}";

        $vQuery .= \Coimf\DB::whereQueryFromArgs( $vFilter );

        if ( ! empty( $vGroupBy ) ) {
            if ( is_array( $vGroupBy ) ) {
                $vGroupBy = implode( ", ", $vGroupBy );
            }
            $vQuery .= " GROUP BY " . $vGroupBy;
        }

        $vQuery .= " ORDER BY ${vOrderBy} {$vOrder}";

        if ( $vLimit > -1 ) {
            $vQuery .= " LIMIT {$vLimit}";
        }
        
        if ( $vOffset > -1 ) {
            $vQuery .= " OFFSET {$vOffset}";
        }

        if ( $vSelect == "COUNT(*)" ) {
            return $vDB->getVar( $vQuery );
        }

        return $vDB->getResults( $vQuery, ARRAY_A );
    }

    public static function deleteAction( int $aActionID ) {
        return self::deleteActions([
            "vFilter" => [
                "id"    => "= {$aActionID}"
            ]
        ]);
    }

    public static function deleteActions( array $aArgs = [] ) {
        $vDefaults = [
            "vFilter"       => [
                // FIXME: value needs to be a value, so we can set multiple
                // filters for each column
                // "action_type"   => false,
            ],
        ];

        $vArgs = array_merge( $vDefaults, $aArgs );
        extract( $vArgs );

        $vDB = \Coimf\DB::getInstance();
        $vTableName = $vDB->getDataTableName();

        $vQuery = "DELETE FROM {$vTableName}";

        $vQuery .= \Coimf\DB::whereQueryFromArgs( $vFilter );

        if ( COIMF_DRY_UPDATE ) {
            Logger::sLog( "Coimf_Action", LogLevel::INFO, "::addAction()", $vQuery );
            return true;
        }

        return $vDB->query( $vQuery );
    }

    // FIXME: require a \Coimf\Cookie instead of userGUID and session
    public function addAction( int $aActionType, string $aUserGUID, string $aSession, $aValue, \DateTime $aTimeStart, \DateTime $aTimeEnd ) {
        $vDB = \Coimf\DB::getInstance();

        $vTableName = $vDB->getDataTableName();

        $vTimeStartFormat = $aTimeStart->format( \Coimf\TimeFunctions::cMYSQLDateTimeFormat );
        $vTimeEndFormat = $aTimeEnd->format( \Coimf\TimeFunctions::cMYSQLDateTimeFormat );

        $vQuery = $vDB->prepare( "
            INSERT INTO {$vTableName}
                   (  user_id, session_id, action_type, value, time_start, time_end )
            VALUES (  %s,       %s,        %d,          %s,    %s,         %s )",
            $aUserGUID, $aSession, $aActionType, $aValue, $vTimeStartFormat, $vTimeEndFormat );

        if ( COIMF_DRY_UPDATE ) {
            $this->mLogger->log( LogLevel::INFO, "::addAction()", $vQuery );
            return true;
        }

        return $vDB->query( $vQuery );
    }

    public function addInternalLinkAction( string $aUserGUID, string $aSession,
                                           string $aFromLink, string $aToLink,
                                           \DateTime $aTime ) {
        $vValue = json_encode([
            "from" => $aFromLink,
            "to" => $aToLink,
        ]);

        return $this->addAction( \Coimf\Action_Type::InternalLink, $aUserGUID, $aSession, $vValue, $aTime, $aTime );
    }

    public function addClickPosition( string $aUserGUID, string $aSession,
                                      int $aMouseX, int $aMouseY,
                                      int $aResolutionX, int $aResolutionY,
                                      string $aPageURL, \DateTime $aTime ) {
        $vValue = json_encode([
            "mouseX" => $aMouseX,
            "mouseY" => $aMouseY,
            "resolutionX" => $aResolutionX,
            "resolutionY" => $aResolutionY,
            "location" => $aPageURL,
        ]);

        return $this->addAction( \Coimf\Action_Type::Click, $aUserGUID, $aSession, $vValue, $aTime, $aTime );
    }

    public function addPageTime( string $aUserGUID, string $aSession,
                                 int $aPageTime,
                                 string $aPageURL, \DateTime $aTime ) {
        $vValue = json_encode([
            "pageTime" => $aPageTime,
            "location" => $aPageURL,
        ]);

        // adding the page time
        $vStartTime = clone $aTime;
        $vStartTime->modify( "-" . $aPageTime . " seconds" );

        return $this->addAction( \Coimf\Action_Type::PageRead, $aUserGUID, $aSession, $vValue, $vStartTime, $aTime );
    }

    public static function fromAction( \stdClass $aActionObject ) {
        $vNewObject = clone $aActionObject;
        $vNewObject->value = json_decode( $aActionObject->value );

        $vNewObject->time_start = \DateTime::createFromFormat( \Coimf\TimeFunctions::cMYSQLDateTimeFormat, $aActionObject->time_start );
        $vNewObject->time_end = \DateTime::createFromFormat( \Coimf\TimeFunctions::cMYSQLDateTimeFormat, $aActionObject->time_end );
        // switch( intval( $aActionObject->action_type ) ) {
        //     case \Coimf\Action_Type::Click: {
        //     }
        // }
        return $vNewObject;
    }

    private function isLinkLocal( string $aLink ) {
        $vComponents = parse_url( $aLink );
        // empty host will indicate url like '/relative.php'
        return !empty( $vComponents["host"] ) || strcasecmp( $vComponents["host"], $_SERVER["SERVER_NAME"] );
    }

    private \Coimf\Logger $mLogger;
    private const cAPIVersion = "v1";

}

}
