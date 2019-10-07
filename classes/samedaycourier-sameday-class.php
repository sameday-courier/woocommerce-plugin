<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sameday
 */
class Sameday
{
	/**
	 * @var mixed|void
	 */
	private $samedayOptions;

	public function __construct()
	{
		$this->samedayOptions = get_option('woocommerce_samedaycourier_settings');
	}

	private function isTesting()
	{
		return $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;
	}

	/**
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
	public function refreshServices()
	{
		if (empty($this->samedayOptions)) {
			wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
		}

		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$remoteServices = [];
		$page = 1;

		do {
			$request = new \Sameday\Requests\SamedayGetServicesRequest();
			$request->setPage($page++);

			try {
				$services = $sameday->getServices($request);
			} catch (\Sameday\Exceptions\SamedayAuthenticationException $e) {
				wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
			}

			foreach ($services->getServices() as $serviceObject) {
				$service = SamedayCourierQueryDb::getServiceSameday($serviceObject->getId(), $this->isTesting());
				if (! $service) {
					// Service not found, add it.
					SamedayCourierQueryDb::addService($serviceObject, $this->isTesting());
				} else {
					SamedayCourierQueryDb::updateServiceCode($serviceObject, $service->id);
				}

				// Save as current sameday service.
				$remoteServices[] = $serviceObject->getId();
			}

		} while ($page <= $services->getPages());

		// Build array of local services.
		$localServices = array_map(
			function ($service) {
				return array(
					'id' => $service->id,
					'sameday_id' => $service->sameday_id
				);
			},

			SamedayCourierQueryDb::getServices($this->isTesting())
		);

		// Delete local services that aren't present in remote services anymore.
		foreach ($localServices as $localService) {
			if (!in_array($localService['sameday_id'], $remoteServices)) {
				SamedayCourierQueryDb::deleteService($localService['id']);
			}
		}

		return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
	}

	public function refreshPickupPoints()
	{
		if (empty($this->samedayOptions) ) {
			wp_redirect(admin_url() . 'admin.php?page=sameday_pickup_points');
		}

		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$remotePickupPoints = [];
		$page = 1;
		do {
			$request = new Sameday\Requests\SamedayGetPickupPointsRequest();
			$request->setPage($page++);
			try {
				$pickUpPoints = $sameday->getPickupPoints($request);
			} catch (\Sameday\Exceptions\SamedayAuthenticationException $e) {
				wp_redirect(admin_url() . 'admin.php?page=sameday_pickup_points');
			}

			foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
				$pickupPoint = SamedayCourierQueryDb::getPickupPointSameday($pickupPointObject->getId(), $this->isTesting());
				if (!$pickupPoint) {
					// Pickup point not found, add it.
					SamedayCourierQueryDb::addPickupPoint($pickupPointObject, $this->isTesting());
				} else {
					SamedayCourierQueryDb::updatePickupPoint($pickupPointObject, $pickupPoint->id);
				}

				// Save as current pickup points.
				$remotePickupPoints[] = $pickupPointObject->getId();
			}
		} while ($page <= $pickUpPoints->getPages());

		// Build array of local pickup points.
		$localPickupPoints = array_map(
			function ($pickupPoint) {
				return array(
					'id' => $pickupPoint->id,
					'sameday_id' => $pickupPoint->sameday_id
				);
			},

			SamedayCourierQueryDb::getPickupPoints($this->isTesting())
		);

		// Delete local pickup points that aren't present in remote pickup points anymore.
		foreach ($localPickupPoints as $localPickupPoint) {
			if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints)) {
				SamedayCourierQueryDb::deletePickupPoint($localPickupPoint['id']);
			}
		}

		return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
	}

	/**
	 * @return bool
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedayBadRequestException
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
	public function refreshLockers()
	{
		if (empty($this->samedayOptions) ) {
			wp_redirect(admin_url() . 'admin.php?page=sameday_lockers');
		}

		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$request = new Sameday\Requests\SamedayGetLockersRequest();

		try {
			$lockers = $sameday->getLockers($request);
		} catch (\Sameday\Exceptions\SamedayAuthenticationException $e) {
			wp_redirect(admin_url() . 'admin.php?page=sameday_lockers');
		}

		$remoteLockers = [];
		foreach ($lockers->getLockers() as $lockerObject) {
			$locker = SamedayCourierQueryDb::getLockerSameday($lockerObject->getId(), $this->isTesting());
			if (!$locker) {
				// Pickup point not found, add it.
				SamedayCourierQueryDb::addLocker($lockerObject, $this->isTesting());
			} else {
				SamedayCourierQueryDb::updateLocker($lockerObject, $locker->id);
			}

			// Save as current pickup points.
			$remoteLockers[] = $lockerObject->getId();
		}

		// Build array of local lockers.
		$localLockers = array_map(
			function ($locker) {
				return array(
					'id' => $locker->id,
					'locker_id' => $locker->locker_id
				);
			},

			SamedayCourierQueryDb::getLockers($this->isTesting())
		);

		// Delete local lockers that aren't present in remote lockers anymore.
		foreach ($localLockers as $localLocker) {
			if (!in_array($localLocker['locker_id'], $remoteLockers)) {
				SamedayCourierQueryDb::deleteLocker($localLocker['id']);
			}
		}

		$this->updateLastSyncTimestamp();

		return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_lockers');
	}

	/**
	 * @return void
	 */
	private function updateLastSyncTimestamp()
	{
		$time = time();

		$this->samedayOptions['sameday_sync_lockers_ts'] = $time;
		update_option('woocommerce_samedaycourier_settings', $this->samedayOptions);
	}

	/**
	 * @return bool
	 */
	public function editService()
	{
		if (! $_POST['action'] === 'edit_service') {
			return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
		}

		$post_fields = array(
			'id' => array(
				'required' => true,
				'value' => $_POST['samedaycourier-service-id']
			),
			'name' => array(
				'required' => true,
				'value' =>  $_POST['samedaycourier-service-name']
			),
			'price' => array(
				'required' => true,
				'value' => $_POST['samedaycourier-price']
			),
			'price_free' => array(
				'required' => false,
				'value' => $_POST['samedaycourier-free-delivery-price'] ?: null
			),
			'status' => array(
				'required' => false,
				'value' => $_POST['samedaycourier-status']
			),
			'working_days' => array(
				'required' => false,
				'value' => $_POST['samedaycourier-working_days']
			)
		);

		$errors = array();

		foreach ($post_fields as $field => $field_value) {
			if ($field_value['required'] && !strlen($field_value['value'])) {
				$errors[] = __("The {$field} must not be empty");
			}
		}

		if ($post_fields['status']['value'] === '2') {
			$days = \SamedayCourierHelperClass::getDays();

			$workingDays = $post_fields['working_days']['value'];

			$enabledDays = array();
			foreach ($days as $day) {
				if (isset($workingDays["order_date_{$day['text']}_enabled"])) {
					$enabledDays[] = $workingDays["order_date_{$day['text']}_enabled"];
				}
			}

			foreach ($enabledDays as $day) {
				if ((int) $workingDays["order_date_{$day}_h_from"] > (int) $workingDays["order_date_{$day}_h_until"]) {
					$errors[] = __("Until hour must be greater than from hour");
				}

				if ( ((int) $workingDays["order_date_{$day}_h_from"] > 23) || ((int) $workingDays["order_date_{$day}_h_until"] > 23)) {
					$errors[] = __("Hours must be less than 23");
				}

				if ( ((int) $workingDays["order_date_{$day}_m_from"] > 59) || ((int) $workingDays["order_date_{$day}_m_until"] > 59)) {
					$errors[] = __("Minutes must be less than 59");
				}

				if ( ((int) $workingDays["order_date_{$day}_s_from"] > 59) || ((int) $workingDays["order_date_{$day}_s_until"] > 59)) {
					$errors[] = __("Seconds be less than 59");
				}
			}
		}
		// End of Validation check.

		if (empty($errors)) {
			$service = array(
				'id' => (int) $post_fields['id']['value'],
				'name' => $post_fields['name']['value'],
				'price' => $post_fields['price']['value'],
				'price_free' => $post_fields['price_free']['value'],
				'status' => $post_fields['status']['value'],
				'working_days' => serialize($post_fields['working_days']['value'])
			);

			SamedayCourierQueryDb::updateService($service);

			return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
		}

		return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services&action=edit&id=' . $post_fields['id']['value']);
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 * @throws \Sameday\Exceptions\SamedayAuthenticationException
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedayNotFoundException
	 * @throws \Sameday\Exceptions\SamedayOtherException
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
	public function postAwb($params)
	{
		if (empty($this->samedayOptions) ) {
            wp_redirect(admin_url() . "post.php?post={$params['samedaycourier-order-id']}&action=edit");
        }

        if (empty($params['shipping_lines'])) {
            return wp_redirect(add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
        }

        $serviceId = $params['samedaycourier-service'];

		$lockerId = get_post_meta($params['samedaycourier-order-id'], '_sameday_shipping_locker_id', true );

		if (isset($lockerId)) {
			$locker = SamedayCourierQueryDb::getLockerSameday($lockerId, $this->isTesting());
		}

		$city = isset($locker) ? $locker->city :$params['shipping']['city'];
		$county = isset($locker)  ? $locker->county : SamedayCourierHelperClass::convertStateCodeToName($params['shipping']['country'], $params['shipping']['state']);
		$address = isset($locker) ? $locker->address : ltrim($params['shipping']['address_1']) . " " . $params['shipping']['address_2'];

		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$parcelDimensions[] = new \Sameday\Objects\ParcelDimensionsObject(
			$params['samedaycourier-package-weight'] <= 0 ? 1 : $params['samedaycourier-package-weight'],
			$params['samedaycourier-package-length'],
			$params['samedaycourier-package-height'],
			$params['samedaycourier-package-width']
		);

		$companyObject = null;
		if (strlen($params['shipping']['company'])) {
			$companyObject = new \Sameday\Objects\PostAwb\Request\CompanyEntityObject(
				$params['shipping']['company'],
				'',
				'',
				'',
				''
			);
		}

		$request = new \Sameday\Requests\SamedayPostAwbRequest(
			$params['samedaycourier-package-pickup-point'],
			null,
			new \Sameday\Objects\Types\PackageType($params['samedaycourier-package-type']),
			$parcelDimensions,
			$serviceId,
			new \Sameday\Objects\Types\AwbPaymentType($params['samedaycourier-package-awb-payment']),
			new \Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject(
				$city,
				$county,
				$address,
				ltrim($params['shipping']['first_name']) . " " . $params['shipping']['last_name'],
				isset($params['billing']['phone']) ? $params['billing']['phone'] : "",
				isset($params['billing']['phone']) ? $params['billing']['email'] : "",
				$companyObject
			),
			$params['samedaycourier-package-insurance-value'],
			$params['samedaycourier-package-repayment'],
			new \Sameday\Objects\Types\CodCollectorType(\Sameday\Objects\Types\CodCollectorType::CLIENT),
			null,
			array(),
			null,
			null,
			$params['samedaycourier-package-observation'],
			'',
			'',
			$lockerId
		);

		try {
			// No errors, post AWB.
			$awb = $sameday->postAwb($request);
		} catch (\Sameday\Exceptions\SamedayBadRequestException $e) {
			$errors = $e->getErrors();
		}

		if (isset($errors)) {
			return wp_redirect(add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
		}

		$awbDetails = array(
			'order_id' => $params['samedaycourier-order-id'],
			'awb_number' => $awb->getAwbNumber(),
			'parcels' => serialize($awb->getParcels()),
			'awb_cost' => $awb->getCost()
		);

		SamedayCourierQueryDb::saveAwb($awbDetails);

        $samedayOrderItemId = null;
        foreach ($params['shipping_lines'] as $id => $shippingLine) {
            $samedayOrderItemId = $id;
            break;
        }

        $service = SamedayCourierQueryDb::getServiceSameday($serviceId, $this->isTesting());
        $metas = array(
            'service_id' => $serviceId,
            'service_code' => $service->sameday_code
        );

        // Add/update sameday metadata.
        foreach ($metas as $key => $value) {
            $shippingLine->update_meta_data($key, $value);
        }
        $shippingLine->save_meta_data();

        // Set sameday shipping method.
        $shippingLine->set_method_id('samedaycourier');
        $shippingLine->save();
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'woocommerce_order_items', array('order_item_name' => $service->name), array('order_item_id' => $samedayOrderItemId));

		return wp_redirect(add_query_arg('add-awb', 'success', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
	}

	public function removeAwb($awb)
	{
		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		try {
			$sameday->deleteAwb(new Sameday\Requests\SamedayDeleteAwbRequest($awb->awb_number));
			SamedayCourierQueryDb::deleteAwb($awb->id);
		} catch (\Exception $e) {
			$errors = $e->getMessage();
		}

		if (isset($errors)) {
			return wp_redirect(add_query_arg('remove-awb', 'error', "post.php?post={$awb->order_id}&action=edit"));
		}

		return wp_redirect(add_query_arg('remove-awb', 'success', "post.php?post={$awb->order_id}&action=edit"));
	}

	/**
	 * @param $orderId
	 *
	 * @return bool
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 */
	public function showAwbAsPdf($orderId)
	{
		$defaultLabelFormat = $this->samedayOptions['default_label_format'];

		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$awb = SamedayCourierQueryDb::getAwbForOrderId($orderId);

		try {
			$content = $sameday->getAwbPdf(
				new Sameday\Requests\SamedayGetAwbPdfRequest(
					$awb->awb_number,
					new Sameday\Objects\Types\AwbPdfType($defaultLabelFormat)
				)
			);

			$pdf = $content->getPdf();
		} catch (Exception $e) {
			$errors = $e->getMessage();
		}

		if (isset($errors)) {
			return wp_redirect(add_query_arg('show-awb', 'error', "post.php?post={$awb->order_id}&action=edit"));
		}

		header('Content-type: application/pdf');
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");

		echo $pdf;

		exit;
	}

	/**
	 * @param $orderId
	 *
	 * @return string|void
	 * @throws \Sameday\Exceptions\SamedayAuthenticationException
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedayNotFoundException
	 * @throws \Sameday\Exceptions\SamedayOtherException
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
	public function showAwbHistory($orderId)
	{
		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$this->isTesting()
		));

		$awb = SamedayCourierQueryDb::getAwbForOrderId($orderId);
        if (empty($awb)) {
            return;
        }

        $parcels = unserialize($awb->parcels);
		foreach ($parcels as $parcel) {
			$parcelStatus = $sameday->getParcelStatusHistory(new \Sameday\Requests\SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber()));
			SamedayCourierQueryDb::refreshPackageHistory(
				$orderId,
				$parcel->getAwbNumber(),
				$parcelStatus->getSummary(),
				$parcelStatus->getHistory(),
				$parcelStatus->getExpeditionStatus()
			);
		}

		$packages = SamedayCourierQueryDb::getPackagesForOrderId($orderId);

		return samedaycourierCreateAwbHistoryTable($packages);
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 * @throws \Sameday\Exceptions\SamedayAuthenticationException
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedayNotFoundException
	 * @throws \Sameday\Exceptions\SamedayOtherException
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
	public function addNewParcel($params)
	{
		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
				$this->samedayOptions['user'],
				$this->samedayOptions['password'],
				$this->isTesting()
			)
		);

		$awb = SamedayCourierQueryDb::getAwbForOrderId($params['samedaycourier-order-id']);

		$position = $this->getPosition($awb->parcels);

		$request = new \Sameday\Requests\SamedayPostParcelRequest(
			$awb->awb_number,
			new Sameday\Objects\ParcelDimensionsObject(
				round($params['samedaycourier-parcel-weight'], 2),
				round($params['samedaycourier-parcel-length'], 2),
				round($params['samedaycourier-parcel-height'],2),
				round($params['samedaycourier-parcel-width'], 2)
			),
			$position,
			$params['samedaycourier-parcel-observation'],
			null,
			$params['samedaycourier-parcel-is-last']
		);

		try {
			$parcel = $sameday->postParcel($request);
		} catch (\Sameday\Exceptions\SamedayBadRequestException $e) {
			$errors = $e->getErrors();
		}

		if (isset($errors)) {
			return wp_redirect(add_query_arg('add-new-parcel', 'error', "post.php?post={$awb->order_id}&action=edit"));
		}

		$parcels = array_merge(unserialize($awb->parcels), array(new \Sameday\Objects\PostAwb\ParcelObject(
					$position,
					$parcel->getParcelAwbNumber()
				)
			)
		);

		SamedayCourierQueryDb::updateParcels($awb->order_id, serialize($parcels));

		return wp_redirect(add_query_arg('add-new-parcel', 'success', "post.php?post={$awb->order_id}&action=edit"));
	}

	/**
	 * @param $parcels
	 *
	 * @return int
	 */
	private function getPosition($parcels)
	{
		$nrOfParcels = count(unserialize($parcels));

		return $nrOfParcels + 1;
	}
}
