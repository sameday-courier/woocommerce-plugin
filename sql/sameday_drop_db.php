<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

function samedaycourier_drop_db() {

	global $wpdb;
	$awbTable =  $wpdb->prefix . 'sameday_awb';
	$pickup_point = $wpdb->prefix . 'sameday_pickup_points';
	$service = $wpdb->prefix . 'sameday_services';
	$packageTable = $wpdb->prefix . 'sameday_package';
    $citiesTable = $wpdb->prefix . 'sameday_cities';

	$dropAwbTable = 'DROP TABLE IF EXISTS ' . $awbTable;
	$pickup_point = 'DROP TABLE IF EXISTS ' . $pickup_point;
	$service = 'DROP TABLE IF EXISTS ' . $service;
	$packageTable = 'DROP TABLE IF EXISTS ' . $packageTable;
    $citiesTable = 'DROP TABLE IF EXISTS ' . $citiesTable;

	dbDelta( $dropAwbTable );
	dbDelta( $pickup_point );
	dbDelta( $service );
	dbDelta( $packageTable );
    dbDelta( $citiesTable );
}
