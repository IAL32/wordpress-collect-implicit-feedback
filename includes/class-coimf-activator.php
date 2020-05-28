<?php

class Coimf_Activator {

	public static function activate() {
		self::createDataTable();
	}

	private static function createDataTable() {
		global $wpdb;
		$vTableName = $wpdb->prefix . Coimf_DB::$cTablePrefix . Coimf_DB::$cDataTableName;
		if($wpdb->get_var( "show tables like '{$vTableName}'" ) != $vTableName) {
			$sql = "CREATE TABLE {$vTableName}
			( id         int NOT NULL GENERATED ALWAYS AS IDENTITY ( minvalue 1 start 1 ),
			 user_id     int NOT NULL,
			 action_type int NOT NULL,
			 value       text NOT NULL,
			 timestamp   time with time zone NOT NULL,
			 CONSTRAINT PK_{$vTableName} PRIMARY KEY ( id )
			);";
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
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
