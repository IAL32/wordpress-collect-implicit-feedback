<?php

class Coimf_Activator {

	public static function activate() {
		self::createDataTable();
	}

	private static function createDataTable() {
		$vDB = Coimf_DB::getInstance();

		$vTableName = $vDB->getDataTableName();
		if( $vDB->get_var( "show tables like '{$vTableName}'" ) != $vTableName ) {
			$sql = "CREATE TABLE {$vTableName}
			( id         int NOT NULL GENERATED ALWAYS AS IDENTITY ( minvalue 1 start 1 ),
			 guid			text NOT NULL,
			 action_type int NOT NULL,
			 value       text NOT NULL,
			 time_start   time with time zone NOT NULL,
			 time_end   time with time zone NOT NULL,
			 CONSTRAINT PK_{$vTableName} PRIMARY KEY ( id )
			);";
			require_once( ABSPATH . "/wp-admin/includes/upgrade.php" );
			dbDelta( $sql );
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
