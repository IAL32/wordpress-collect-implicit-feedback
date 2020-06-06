<?php

namespace Coimf {

class Activator {

	public static function activate() {
		self::createDataTable();
		self::initializeOptions();
	}

	private static function initializeOptions() {
		add_option( "coimf-track-page-selector", ".post .entry-content" );
		add_option( "coimf-track-user-clicks", "1" );
		add_option( "coimf-track-slug", "/" );
	}

	private static function createDataTable() {
		$vDB = \Coimf\DB::getInstance();
		$vLogger = new \Coimf\Logger( "Coimf_Activator" );

		$vTableName = $vDB->getDataTableName();
		if( $vDB->getVar( "show tables like '{$vTableName}'" ) != $vTableName ) {
			$vSql = "CREATE TABLE {$vTableName} (
				id INT unsigned NOT NULL AUTO_INCREMENT,
				user_id VARCHAR(36) COMMENT 'GUID',
				session_id VARCHAR(36) COMMENT 'GUID',
				action_type INT unsigned NOT NULL,
				value TEXT NOT NULL,
				time_start DateTime NOT NULL,
				time_end DateTime NOT NULL,
				PRIMARY KEY  (id)
			);";
			require_once( ABSPATH . "/wp-admin/includes/upgrade.php" );
			if ( COIMF_DRY_UPDATE ) {
				$vLogger->log( 2, "::createDataTable()", $vSql );
			} else {
				$vExecResult = dbDelta( $vSql );
				$vLogger->log( 2, "::createDataTable()", var_export( $vExecResult, true ) );
			}
		}
	}

	public static function dropTables() {
		global $wpdb;
		$vTableNames[] = $wpdb->prefix . \Coimf\DB::$cTablePrefix . \Coimf\DB::$cDataTableName;
		foreach( $vTableNames as $vTableName ) {
			$vQuery = "DROP TABLE IF EXISTS {$vTableName}";
			if ( COIMF_DRY_UPDATE ) {
				$vLogger = new \Coimf\Logger( "Coimf_Activator" );
				$vLogger->log( 2, "::dropTables()", $vQuery );
			} else {
				$wpdb->query( $vQuery );
			}
		}
	}
}

}
