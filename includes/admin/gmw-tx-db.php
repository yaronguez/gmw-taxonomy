<?php
if ( !defined( 'ABSPATH' ) )
    exit;

//check if table exists
global $wpdb;
$txTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}taxonomy_locator'", ARRAY_A );

//create or update database
if ( get_option( "gmw_tx_db_version" ) == '' || get_option( "gmw_tx_db_version" ) != GMW_TX_DB_VERSION || count( $txTable ) == 0 ) {

    if ( count( $txTable ) == 0 ) {
        gmw_tx_db_installation();
        update_option( "gmw_tx_db_version", GMW_TX_DB_VERSION );

    } elseif ( count( $txTable ) == 1 ) {
        // Do a DB update if necessary.  See posts add on for sample code
    }
}

function gmw_tx_db_installation() {
    global $wpdb;

    $gmw_sql = "CREATE TABLE {$wpdb->prefix}taxonomy_locator (
	`term_taxonomy_id`           bigint(30) NOT NULL,
    `name`              varchar(200) NOT NULL,
	`description`       TEXT,
	`lat`               float(10,6) NOT NULL ,
	`long`              float(10,6) NOT NULL ,
	`street_number` 	varchar(60) NOT NULL,
	`street_name` 		varchar(128) NOT NULL,
	`street`            varchar(128) NOT NULL ,
	`apt`               varchar(50) NOT NULL ,
	`city`              varchar(128) NOT NULL ,
	`state`             varchar(50) NOT NULL ,
	`state_long`        varchar(128) NOT NULL ,
	`zipcode`           varchar(40) NOT NULL ,
	`country`           varchar(50) NOT NULL ,
	`country_long`      varchar(128) NOT NULL ,
	`address`           varchar(255) NOT NULL ,
	`formatted_address` varchar(255) NOT NULL ,
	`phone`             varchar(50) NOT NULL ,
	`fax`               varchar(50) NOT NULL ,
	`email`             varchar(255) NOT NULL ,
	`website`           varchar(255) NOT NULL ,
	`map_icon`          varchar(50) NOT NULL ,
	UNIQUE KEY id (term_taxonomy_id)

	)	DEFAULT CHARSET=utf8;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $gmw_sql );
}

?>