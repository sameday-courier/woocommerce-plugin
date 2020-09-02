<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

class SamedayCourierQueryDb
{
	/**
	 * @param $is_testing
	 *
	 * @return int|null
	 */
	static function getDefaultPickupPointId($is_testing)
	{
		global $wpdb;

		$query = "SELECT sameday_id FROM {$wpdb->prefix}sameday_pickup_point WHERE default_pickup_point = 1 AND is_testing = '{$is_testing}'";
		$result = $wpdb->get_row($query);

		if (empty($result)) {
			return null;
		}

		return $result->sameday_id;
	}

	/**
	 * @param $is_testing
	 *
	 * @return array|object|null
	 */
	static function getAvailableServices($is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE is_testing = '{$is_testing}' AND status > 0";
		$result = $wpdb->get_results($query);

		return $result;
	}

	/**
	 * @param $is_testing
	 *
	 * @return array|object|null
	 */
	static function getServices($is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE is_testing = '{$is_testing}'";
		$result = $wpdb->get_results($query);

		return $result;
	}

    /**
     * @param $is_testing
     *
     * @return array|object|null
     */
    static function getServicesWithOptionalTaxes($is_testing)
    {
        global $wpdb;

        $query = "SELECT sameday_code FROM {$wpdb->prefix}sameday_service WHERE is_testing = '{$is_testing}' AND service_optional_taxes IS NOT NULL";

        return array_map(function ($services) {
                return $services->sameday_code;
            }, $wpdb->get_results($query)
        );
    }

    /**
     * @param int $samedayServiceId
     * @param bool $is_testing
     *
     * @return \Sameday\Objects\Service\OptionalTaxObject[]
     */
    static function getServiceIdOptionalTaxes($samedayServiceId, $is_testing)
    {
        global $wpdb;

        $query = "SELECT service_optional_taxes FROM {$wpdb->prefix}sameday_service WHERE is_testing = '{$is_testing}' AND sameday_id = '{$samedayServiceId}' ";
        /** @var \Sameday\Objects\Service\OptionalTaxObject[]|false $result */
        $result = unserialize($wpdb->get_results($query)[0]->service_optional_taxes);

        return is_array($result) ? $result : array();
    }

	/**
	 * @param $id
	 *
	 * @return array|object|void|null
	 */
	static function getService($id)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE id = '{$id}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	/**
	 * @param $samedayId
	 * @param $is_testing
	 *
	 * @return array|object|void|null
	 */
	static function getServiceSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE sameday_id = '{$samedayId}'  AND is_testing = '{$is_testing}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

    /**
     * @param string $code
     * @param bool $is_testing
     *
     * @return array|object|void|null
     */
    static function getServiceSamedayCode($samedayCode, $is_testing)
    {
        global $wpdb;


        return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sameday_service WHERE sameday_code = '{$samedayCode}'  AND is_testing = '{$is_testing}'");
    }

    /**
	 * @param \Sameday\Objects\Service\ServiceObject $service
	 *
	 * @param int $is_testing
	 */
	static function addService(\Sameday\Objects\Service\ServiceObject $service, $is_testing)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_service';

		$data = array(
			'sameday_id' => $service->getId(),
			'sameday_name' => $service->getName(),
			'sameday_code' => $service->getCode(),
			'is_testing' => $is_testing,
			'status' => 0,
            'service_optional_taxes' => !empty($service->getOptionalTaxes()) ? serialize($service->getOptionalTaxes()) : null
		);
		$format = array('%d','%s','%s','%d','%d','%s');

		$wpdb->insert($table, $data, $format);
	}

	/**
	 * @param array $service
	 */
	static function updateService($service)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_service';
		$wpdb->update($table, $service, array('id' => $service['id']));
	}

	/**
	 * @param \Sameday\Objects\Service\ServiceObject $serviceObject
	 *
	 * @param int $id
	 */
	static function updateServiceCode(\Sameday\Objects\Service\ServiceObject $serviceObject, $id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_service';

		$service = array(
			'sameday_code' => $serviceObject->getCode(),
            'service_optional_taxes' => !empty($serviceObject->getOptionalTaxes()) ? serialize($serviceObject->getOptionalTaxes()) : null
		);

		$wpdb->update($table, $service, array('id' => $id));
	}

	/**
	 * @param int $id
	 */
	static function deleteService($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_service';
		$wpdb->delete($table, array('id' => $id));
	}

	/**
	 * @param $is_testing
	 *
	 * @return array|object|null
	 */
	static function getPickupPoints($is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_pickup_point WHERE is_testing = '{$is_testing}'";
		$result = $wpdb->get_results($query);

		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return array|object|void|null
	 */
	static function getPickupPoint($id)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_pickup_point WHERE id = '{$id}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	/**
	 * @param $samedayId
	 * @param $is_testing
	 *
	 * @return array|object|void|null
	 */
	static function getPickupPointSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_pickup_point WHERE sameday_id='{$samedayId}'  AND is_testing = '{$is_testing}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	static function getLockers($is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE is_testing = '{$is_testing}'";
		$result = $wpdb->get_results($query);

		return $result;
	}

	/**
	 * @param \Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject
	 * @param $is_testing
	 */
	static function addPickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $is_testing)
	{
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

	/**
	 * @param \Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject
	 *
	 * @param int $id
	 */
	static function updatePickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $id)
	{
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
			'id' => $id
		);

		$wpdb->update($table, $data, $where);
	}

	/**
	 * @param int $id
	 */
	static function deletePickupPoint($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_pickup_point';
		$wpdb->delete($table, array('id' => $id));
	}

	/**
	 * @param int $samedayId
	 *
	 * @param int $testing
	 */
	static function getLockerSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE locker_id='{$samedayId}'  AND is_testing = '{$is_testing}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	/**
	 * @param \Sameday\Objects\Locker\LockerObject $lockerObject
	 * @param bool $is_testing
	 */
	static function addLocker(\Sameday\Objects\Locker\LockerObject $lockerObject, $is_testing)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_locker';

		$data = array(
			'locker_id' => $lockerObject->getId(),
			'name' => $lockerObject->getName(),
			'city' => $lockerObject->getCity(),
			'county' => $lockerObject->getCounty(),
			'address' => $lockerObject->getAddress(),
			'lat' => $lockerObject->getLat(),
			'lng' => $lockerObject->getLong(),
			'postal_code' => $lockerObject->getPostalCode(),
			'boxes' => serialize($lockerObject->getBoxes()),
			'is_testing' => $is_testing
		);

		$format = array('%d','%s','%s','%s','%s','%s','%s','%s','%s','%d');

		$wpdb->insert($table, $data, $format);
	}

	/**
	 * @param \Sameday\Objects\Locker\LockerObject $lockerObject
	 *
	 * @param int $id
	 */
	static function updateLocker(\Sameday\Objects\Locker\LockerObject $lockerObject, $id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_locker';

		$data = array(
			'locker_id' => $lockerObject->getId(),
			'name' => $lockerObject->getName(),
			'city' => $lockerObject->getCity(),
			'county' => $lockerObject->getCounty(),
			'address' => $lockerObject->getAddress(),
			'lat' => $lockerObject->getLat(),
			'lng' => $lockerObject->getLong(),
			'postal_code' => $lockerObject->getPostalCode(),
			'boxes' => serialize($lockerObject->getBoxes())
		);

		$where = array(
			'id' => $id
		);

		$wpdb->update($table, $data, $where);
	}

	/**
	 * @param int $id
	 */
	static function deleteLocker($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_locker';
		$wpdb->delete($table, array('id' => $id));
	}

	/**
	 * @param $orderId
	 *
	 * @return array|object|void|null
	 */
	static function getAwbForOrderId($orderId) {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_awb WHERE order_id = '{$orderId}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	/**
	 * @param array $awb
	 */
	static function saveAwb($awb) {
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_awb';

		$format = array('%d','%s','%s','%d');

		$wpdb->insert($table, $awb, $format);
	}

	/**
	 * @param int $id
	 */
	static function deleteAwb($id) {
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_awb';
		$wpdb->delete($table, array('id' => $id));
	}

	/**
	 * @param $orderId
	 * @param $awbParcel
	 * @param \Sameday\Objects\ParcelStatusHistory\SummaryObject $summary
	 * @param array $history
	 * @param \Sameday\Objects\ParcelStatusHistory\ExpeditionObject $expedition
	 */
	static function refreshPackageHistory(
			$orderId,
			$awbParcel,
			\Sameday\Objects\ParcelStatusHistory\SummaryObject $summary,
			array $history,
			\Sameday\Objects\ParcelStatusHistory\ExpeditionObject $expedition
		)
	{
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

	/**
	 * @param $orderId
	 *
	 * @return array|object|null
	 */
	static function getPackagesForOrderId($orderId)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_package WHERE order_id = '{$orderId}'";
		$result = $wpdb->get_results($query);

		return $result;
	}

	/**
	 * @param int $orderId
	 *
	 * @param array $parcels
	 */
	static function updateParcels($orderId, $parcels)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_awb';

		$updateColumns = array(
			'parcels' => $parcels
		);

		$wpdb->update($table, $updateColumns, array('order_id' => $orderId));
	}
}

