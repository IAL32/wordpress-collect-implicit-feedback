<?php

namespace Coimf {

class Action_Table extends External\WP_List_Table {

    public function __construct( $aArgs = [] ) {
        parent::__construct( $aArgs );
    }

    function get_columns() {

        $vTableColumns = [
            "cb"          => "<input type=\"checkbox\" />",
            "action_type"       => __( "Action Type", "coimf" ),
            "user_id"           => __( "User GUID", "coimf" ),
            "session_id"        => __( "Session GUID", "coimf" ),
            "time_start"        => __( "Time Start", "coimf" ),
            "time_end"          => __( "Time End", "coimf" ),
            "item_actions"      => __( "Item Actions", "coimf" ),
        ];

        return $vTableColumns;
    }

    protected function get_sortable_columns() {
        $vSortableColumns = [
            "action_type" => "action_type",
            "time_start" => "time_start",
            "time_end"  => [ "time_end", true ],
        ];

        return $vSortableColumns;
    }

    function column_default( $aItem, $aColumn ) {
        switch( $aColumn ) {
            case "action_type": {
                return $aItem[$aColumn];
            }
            case "user_id":
            case "session_id":
                return substr( $aItem[$aColumn], 0, 6 ) . "&hellip;";
            case "item_actions":
                return "";
            default:
                return $aItem[$aColumn];
        }
    }

    function column_action_type( $aActionItem ) {
        $aActionId = $aActionItem["id"];
        $aActionValue = $aActionItem["value"];
        $vActions["view_action"] =  "
            <div id=\"action-tb-{$aActionId}\" style=\"display:none\">
                <p>
                " . $aActionValue . "
                </p>
            </div>
            <a href=\"#TB_inline?width=200&height=200&inlineId=action-tb-{$aActionId}\" class=\"thickbox\">See Action Value</a>";
        $vActionType = $aActionItem["action_type"];
        return $vActionType . $this->row_actions( $vActions );
    }

    function prepare_items() {

        // FIXME: action type has to be filtered by its name, not by its value
        $vActionTypeSearchKey = isset( $_REQUEST["s"] ) ? wp_unslash( trim( $_REQUEST["s"] ) ) : "";

        $vUsersPerPage = $this->get_items_per_page( "actions_per_page" );
        $vTablePage = $this->get_pagenum();

        if ( $vActionTypeSearchKey !== "" ) {
            $vFilter = [
                "action_type"   => " = $vActionTypeSearchKey",
            ];
        } else {
            $vFilter = [];
        }

        $vActions = \Coimf\Action::getActions([
            "vLimit" => $vUsersPerPage,
            "vOffset" => ( $vTablePage - 1 ) * $vUsersPerPage,
            "vOrderBy"   => ( isset( $_GET["orderby"] ) ) ? esc_sql( $_GET["orderby"] ) : "time_end",
            "vOrder"   => ( isset( $_GET["order"] ) ) ? esc_sql( $_GET["order"] ) : "DESC",
            "vFilter"   => $vFilter,
        ]);

        $vTotalUsers = intval( \Coimf\Action::getAllActions([
            "vSelect"   => "COUNT(*)",
            "vFilter"   => $vFilter,
        ]));

        $this->set_pagination_args([
            "total_items"   => $vTotalUsers,
            "per_page"      => $vUsersPerPage,
            "total_pages"   => ceil( $vTotalUsers / $vUsersPerPage ),
        ]);

        $this->items = $vActions;
    }
}

}
