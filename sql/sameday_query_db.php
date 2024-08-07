<?php

use Sameday\Objects\Service\ServiceObject;

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SamedayCourierQueryDb
 */
class SamedayCourierQueryDb
{
	/**
	 * @param $is_testing
	 *
	 * @return int|null
	 */
	public static function getDefaultPickupPointId($is_testing)
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
	public static function getAvailableServices($is_testing)
	{
		global $wpdb;

        $query = sprintf(
            "SELECT * FROM %s WHERE is_testing='%s' AND status > 0",
            $wpdb->prefix . 'sameday_service',
            $is_testing
        );

        return $wpdb->get_results($query);
	}

	/**
	 * @param $is_testing
	 *
	 * @return array|object|null
	 */
	public static function getServices($is_testing)
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
    public static function getServicesWithOptionalTaxes($is_testing)
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
    public static function getServiceIdOptionalTaxes($samedayServiceId, $is_testing)
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
	public static function getService($id)
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
	 * @return array|object|stdClass|null
	 */
	public static function getServiceSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE sameday_id = '{$samedayId}'  AND is_testing = '{$is_testing}'";

		return $wpdb->get_row($query);
	}

    /**
     * @param $samedayCode
     *
     * @param $is_testing
     *
     * @return array|object|stdClass|null
     */
    public static function getServiceSamedayByCode($samedayCode, $is_testing)
    {
        global $wpdb;

        return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sameday_service WHERE sameday_code = '{$samedayCode}'  AND is_testing = '{$is_testing}'");
    }

    /**
     * @param ServiceObject $service
     * @param int $is_testing
     *
     * @return void
     */
	public static function addService(ServiceObject $service, int $is_testing): void
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
	public static function updateService(array $service): void
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_service';
		$wpdb->update($table, $service, array('id' => $service['id']));
	}

    public static function updateWcOrderAddress(int $oderId, array $address): void
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'wc_order_addresses',
            $address,
            [
                'order_id' => $oderId,
                'address_type' => 'shipping'
            ]
        );
    }

    /**
     * @param ServiceObject $serviceObject
     * @param int $id
     *
     * @return void
     */
	public static function updateServiceCode(ServiceObject $serviceObject, int $id): void
	{
		global $wpdb;

        $serviceName = $serviceObject->getName();
        if ($serviceObject->getCode() === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE) {
            $serviceName = SamedayCourierHelperClass::OOH_SERVICES_LABELS[SamedayCourierHelperClass::getHostCountry()];
        }

        $updatedService = array(
            'sameday_code' => $serviceObject->getCode(),
            'name' => $serviceName,
            'service_optional_taxes' => !empty($serviceObject->getOptionalTaxes())
                ? serialize($serviceObject->getOptionalTaxes())
                : null
        );

		$wpdb->update(
            $wpdb->prefix . 'sameday_service',
            $updatedService,
            array('id' => $id)
        );
	}

	/**
	 * @param int $id
	 */
	public static function deleteService($id)
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
	public static function getPickupPoints($is_testing)
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
	public static function getPickupPoint($id)
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
	public static function getPickupPointSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_pickup_point WHERE sameday_id='{$samedayId}'  AND is_testing = '{$is_testing}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

    /**
     * @param $is_testing
     *
     * @return array|object|void|null
     */
	public static function getCities($is_testing)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'sameday_locker';

        $query = "SELECT city, county FROM {$tableName} WHERE is_testing={$is_testing} GROUP BY city";

        return $wpdb->get_results($query);
    }

    /**
     * @param $is_testing
     *
     * @return array|object|null
     */
	public static function getLockers($is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE is_testing = '$is_testing'";

		return $wpdb->get_results($query);
	}

    /**
     * @param $city
     * @param $is_testing
     *
     * @return array|object|null
     */
	public static function getLockersByCity($city, $is_testing)
    {
        global $wpdb;

        $query = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE city='{$city}' AND is_testing = '{$is_testing}'";
        $result = $wpdb->get_results($query);

        return $result;
    }

	/**
	 * @param \Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject
	 * @param $is_testing
	 */
	public static function addPickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $is_testing)
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
	public static function updatePickupPoint(\Sameday\Objects\PickupPoint\PickupPointObject $pickupPointObject, $id)
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
	public static function deletePickupPoint($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_pickup_point';
		$wpdb->delete($table, array('id' => $id));
	}

	/**
	 * @param $samedayId
	 * @param $is_testing
	 *
	 * @return array|object|void|null
	 */
	public static function getLockerSameday($samedayId, $is_testing)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE locker_id='{$samedayId}'  AND is_testing = '{$is_testing}'";

		return $wpdb->get_row($query);
	}

	/**
	 * @param \Sameday\Objects\Locker\LockerObject $lockerObject
	 * @param bool $is_testing
	 */
	public static function addLocker(\Sameday\Objects\Locker\LockerObject $lockerObject, $is_testing)
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
	public static function updateLocker(\Sameday\Objects\Locker\LockerObject $lockerObject, $id)
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
	public static function deleteLocker($id)
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
	public static function getAwbForOrderId($orderId) {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_awb WHERE order_id = '{$orderId}'";
		$result = $wpdb->get_row($query);

		return $result;
	}

	/**
	 * @param array $awb
	 */
	public static function saveAwb($awb)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_awb';

		$format = array('%d','%s','%s','%d');

		$wpdb->insert($table, $awb, $format);
	}

	/**
	 * @param object $awb
	 */
	public static function deleteAwbAndParcels($awb)
	{
		global $wpdb;

		$awbTable = $wpdb->prefix . 'sameday_awb';
		$awbParcels = $wpdb->prefix . 'sameday_package';

		$wpdb->delete($awbTable, array('id' => $awb->id));
		$wpdb->delete($awbParcels, array('order_id' => $awb->order_id));
	}

	/**
	 * @param $orderId
	 * @param $awbParcel
	 * @param \Sameday\Objects\ParcelStatusHistory\SummaryObject $summary
	 * @param array $history
	 * @param \Sameday\Objects\ParcelStatusHistory\ExpeditionObject $expedition
	 */
	public static function refreshPackageHistory(
			$orderId,
			$awbParcel,
			\Sameday\Objects\ParcelStatusHistory\SummaryObject $summary,
			array $history,
			\Sameday\Objects\ParcelStatusHistory\ExpeditionObject $expedition
		)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_package';

		$newPackage = self::preparePackage($orderId, $awbParcel, $summary, $history, $expedition);

		$format = array('%d','%s','%s','%s','%s');

		$wpdb->insert($table, $newPackage, $format);
	}

	/**
	 * @param $orderId
	 * @param $awbParcel
	 * @param $summary
	 * @param $history
	 * @param $expedition
	 *
	 * @return array
	 */
	private static function preparePackage(
		$orderId,
		$awbParcel,
		$summary,
		$history,
		$expedition
	)
	{
		return [
			'order_id' => $orderId,
			'awb_parcel' => $awbParcel,
			'summary' => serialize($summary),
			'history' => serialize($history),
			'expedition_status' => serialize($expedition),
			'sync' => null
		];
	}

	/**
	 * @param $orderId
	 *
	 * @return array|object|null
	 */
	public static function getPackagesForOrderId($orderId)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}sameday_package WHERE order_id = '{$orderId}'";

		return $wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * @param $orderId
	 * @param $parcels
	 *
	 * @return void
	 */
	public static function updateParcels($orderId, $parcels)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'sameday_awb';

		$updateColumns = array(
			'parcels' => $parcels
		);

		$wpdb->update($table, $updateColumns, array('order_id' => $orderId));
	}
}

