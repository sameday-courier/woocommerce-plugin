<?php

function getDefaultPickupPointId($is_testing) {
	global $wpdb;

	$query = "SELECT sameday_id FROM " . $wpdb->prefix . 'sameday_pickup_point' . " WHERE default_pickup_point = 1 AND is_testing = {$is_testing}";
	$result = $wpdb->get_row($query);

	if (empty($result)) {
		return null;
	}

	return $result->sameday_id;
}

function getAvailableServices($is_testing) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_service' . " WHERE is_testing = {$is_testing} AND status>0";
	$result = $wpdb->get_results($query);

	return $result;
}

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

function updateService($service) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_service';
	$wpdb->update($table, $service, array('id' => $service['id']));
}

function deleteService($id) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_service';
	$wpdb->delete($table, array('id' => $id));
}

function getPickupPoints($is_testing) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_pickup_point' . " WHERE is_testing = {$is_testing}";
	$result = $wpdb->get_results($query);

	return $result;
}

function getPickupPoint($id) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_pickup_point' . " WHERE id = {$id}";
	$result = $wpdb->get_row($query);

	return $result;
}

function getPickupPointSameday($samedayId, $is_testing) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_pickup_point' . " WHERE sameday_id={$samedayId}  AND is_testing = {$is_testing}";
	$result = $wpdb->get_row($query);

	return $result;
}

function addPickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $is_testing) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_pickup_point';

	$data = array(
		'sameday_id' => $pickupPointObject->getId(),
		'sameday_alias' => $pickupPointObject->getAlias(),
		'is_testing' => $is_testing,
		'city' => $pickupPointObject->getCity()->getName(),
		'county' => $pickupPointObject->getCounty()->getName(),
		'address' => $pickupPointObject->getAddress(),
		'default_pickup_point' => $pickupPointObject->isDefault(),
		'contactPersons' => serialize($pickupPointObject->getContactPersons())
	);

	$format = array('%d','%s','%d','%s','%s','%s','%s','%s');

	$wpdb->insert($table, $data, $format);
}

function updatePickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $is_testing) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_pickup_point';

	$data = array(
		'sameday_alias' => $pickupPointObject->getAlias(),
		'city' => $pickupPointObject->getCity()->getName(),
		'county' => $pickupPointObject->getCounty()->getName(),
		'address' => $pickupPointObject->getAddress(),
		'default_pickup_point' => $pickupPointObject->isDefault(),
		'contactPersons' => serialize($pickupPointObject->getContactPersons())
	);

	$where = array(
		'sameday_id' => $pickupPointObject->getId()
	);

	$wpdb->update($table, $data, $where);
}

function deletePickupPoint($id) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_pickup_point';
	$wpdb->delete($table, array('id' => $id));
}

function getAwbForOrderId($orderId) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_awb' . " WHERE order_id={$orderId}";
	$result = $wpdb->get_row($query);

	return $result;
}

function saveAwb($awb) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_awb';

	$format = array('%d','%s','%s','%d');

	$wpdb->insert($table, $awb, $format);
}

function deleteAwb($id) {
	global $wpdb;

	$table = $wpdb->prefix . 'sameday_awb';
	$wpdb->delete($table, array('id' => $id));
}

function refreshPackageHistory(
		$orderId,
		$awbParcel,
		\Sameday\Objects\ParcelStatusHistory\SummaryObject $summary,
		array $history,
		\Sameday\Objects\ParcelStatusHistory\ExpeditionObject $expedition
	) {

	global $wpdb;

	$table = $wpdb->prefix . 'sameday_package';

	$package = array(
		'order_id' => $orderId,
		'awb_parcel' => $awbParcel,
		'summary' => serialize($summary),
		'history' => serialize($history),
		'expedition_status' => serialize($expedition)
	);

	$format = array('%d','%s','%s','%s','%s');

	$wpdb->insert($table, $package, $format);
}

function getPackagesForOrderId($orderId) {
	global $wpdb;

	$query = "SELECT * FROM " . $wpdb->prefix . 'sameday_package' . " WHERE order_id={$orderId}";
	$result = $wpdb->get_results($query);

	return $result;
}

function updateParcels($orderId, $parcels) {

	global $wpdb;

	$table = $wpdb->prefix . 'sameday_awb';

	$updateColumns = array(
		'parcels' => $parcels
	);

	$wpdb->update($table, $updateColumns, array('order_id' => $orderId));
}