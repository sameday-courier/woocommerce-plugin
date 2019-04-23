<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

function samedaycourier_create_db() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$awbTable =  $wpdb->prefix . 'sameday_awb';
	$pickup_point = $wpdb->prefix . 'sameday_pickup_points';
	$service = $wpdb->prefix . 'sameday_services';
	$packageTable = $wpdb->prefix . 'sameday_package';

	$createAwbTable = "CREATE TABLE IF NOT EXISTS $awbTable (
		id INT(11) NOT NULL AUTO_INCREMENT,
        order_id INT(11) NOT NULL,
        awb_number VARCHAR(255),
        parcels TEXT,
        awb_cost DOUBLE(10, 2),
        PRIMARY KEY (id),
		UNIQUE KEY id (id)
	) $charset_collate;";

	$createPickUpPointTable = "CREATE TABLE IF NOT EXISTS $pickup_point (
		id INT(11) NOT NULL AUTO_INCREMENT,
        sameday_id INT(11) NOT NULL,
        sameday_alias VARCHAR(255),
        testing TINYINT(1),
        city VARCHAR(255),
        county VARCHAR(255),
        address VARCHAR(255),
        contactPersons TEXT,
        default_pickup_point TINYINT(1),
        PRIMARY KEY (id),
		UNIQUE KEY id (id)
	) $charset_collate;";

	$createServiceTable = "CREATE TABLE IF NOT EXISTS $service (
		id INT(11) NOT NULL AUTO_INCREMENT,
        sameday_id INT(11) NOT NULL,
        sameday_name VARCHAR(255),
        testing TINYINT(1),
        name VARCHAR(255),
        price DOUBLE(10, 2),
        price_free DOUBLE(10, 2),
        status INT(11),
        working_days TEXT,
        PRIMARY KEY (id),
		UNIQUE KEY id (id)
	) $charset_collate;";

	$createPackageTable = "CREATE TABLE IF NOT EXISTS $packageTable (
		order_id INT(11) NOT NULL,
        awb_parcel VARCHAR(255),
        summary TEXT,
        history TEXT,
        expedition_status TEXT,
        sync TEXT,
        PRIMARY KEY (order_id, awb_parcel)
	) $charset_collate;";

	dbDelta( $createAwbTable );
	dbDelta( $createPickUpPointTable );
	dbDelta( $createServiceTable );
	dbDelta( $createPackageTable );
}

