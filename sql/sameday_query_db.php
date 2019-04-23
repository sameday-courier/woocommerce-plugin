<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

function getServices() {
	global $wpdb;

	$sql = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE is_testing = 1");

	return $sql;
}

function getPickupPoints() {
	global $wpdb;

	$sql = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . 'sameday_pickup_point' . " WHERE is_testing = 1");

	return $sql;
}
