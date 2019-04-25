<?php

function getServices($is_testing) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE is_testing = {$is_testing}";
	$result = $wpdb->get_results($query);

	return $result;
}

function getService($id) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE id={$id}";
	$result = $wpdb->get_row($query);

	return $result;
}

function getServiceSameday($samedayId, $is_testing) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE sameday_id={$samedayId}  AND is_testing = {$is_testing}";
	$result = $wpdb->get_row($query);

	return $result;
}

function addService(\Sameday\Objects\Service\ServiceObject $service, $is_testing) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_service';
	$data = array(
		'sameday_id' => $service->getId(),
		'sameday_name' => $service->getName(),
		'is_testing' => $is_testing,
		'status' => 0
	);
	$format = array('%d','%s','%d','%d');

	$wpdb->insert($table, $data, $format);
}

function deleteService($id) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_service';
	$wpdb->delete($table, array('id' => $id));
}