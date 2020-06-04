<?php

class Coimf_Activator {

	public static function activate() {
		self::createDataTable();
	}

	private static function createDataTable() {
		$vDB = Coimf_DB::getInstance();

		$vTableName = $vDB->getDataTableName();
		if( $vDB->getVar( "show tables like '{$vTableName}'" ) != $vTableName ) {
			$vSql = "CREATE TABLE {$vTableName}
				`id` INT unsigned NOT NULL AUTO_INCREMENT,
				`user_id` VARCHAR(36) COMMENT 'GUID',
				`session_id` VARCHAR(28) COMMENT 'GUID',
				`action_type` INT unsigned NOT NULL,
				`value` TEXT NOT NULL,
				`time_start` DATETIME NOT NULL,
				`time_end` DATETIME NOT NULL,
				PRIMARY KEY (`id`)
			);";
			require_once( ABSPATH . "/wp-admin/includes/upgrade.php" );
			if ( COIMF_DEBUG ) {
				$vLogger = new Coimf_Logger( "Coimf_Activator" );
				$vLogger->log( 2, $vSql );
			} else {
				dbDelta( $vSql );
			}
		}
	}

	public static function drop_tables() {
		global $wpdb;
		$vTableNames[] = $wpdb->prefix . Coimf_DB::$cTablePrefix . Coimf_DB::$cDataTableName;
		foreach( $vTableNames as $vTableName ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$vTableName}" );
		}
	}
}
