<?php

function getServices() {
	global $wpdb;

	$sql = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE is_testing = 1");

	return $sql;
}
