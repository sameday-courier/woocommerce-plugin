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
	protected $samedayOptions;

	public function __construct()
	{
		$this->samedayOptions = get_option('woocommerce_samedaycourier_settings');
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

		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
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
				$service = getServiceSameday($serviceObject->getId(), $is_testing);
				if (! $service) {
					// Service not found, add it.
					addService($serviceObject, $is_testing);
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
			getServices($is_testing)
		);

		// Delete local services that aren't present in remote services anymore.
		foreach ($localServices as $localService) {
			if (!in_array($localService['sameday_id'], $remoteServices)) {
				deleteService($localService['id']);
			}
		}

		wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
	}

	public function refreshPickupPoints()
	{
		if (empty($this->samedayOptions) ) {
			wp_redirect(admin_url() . 'admin.php?page=sameday_pickup_points');
		}

		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
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
				$pickupPoint = getPickupPointSameday($pickupPointObject->getId(), $is_testing);
				if (!$pickupPoint) {
					// Pickup point not found, add it.
					addPickupPoint($pickupPointObject, $is_testing);
				} else {
					updatePickupPoint($pickupPointObject, $is_testing);
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
			getPickupPoints($is_testing)
		);

		// Delete local pickup points that aren't present in remote pickup points anymore.
		foreach ($localPickupPoints as $localPickupPoint) {
			if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints)) {
				deletePickupPoint($localPickupPoint['id']);
			}
		}

		wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
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
			$days = \HelperClass::getDays();

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

			updateService($service);

			return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
		}

		wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services&action=edit&id=' . $post_fields['id']['value']);
	}

	public function postAwb($params)
	{
		if (empty($this->samedayOptions) ) {
			wp_redirect(admin_url() . "post.php?post={$params['samedaycourier-order-id']}&action=edit");
		}

		$serviceId = null;
		foreach ($params['shipping_lines'] as $array) {
			$index = array_search($array, $params['shipping_lines']);
			$serviceId = $params['shipping_lines'][$index]->get_meta_data()[0]->get_data()['value'];
		}

		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
		));

		$parcelDimensions[] = new \Sameday\Objects\ParcelDimensionsObject(
			$params['samedaycourier-package-weight'],
			$params['samedaycourier-package-length'],
			$params['samedaycourier-package-height'],
			$params['samedaycourier-package-width']
		);

		$companyObject = null;
		if (strlen($params['company'])) {
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
				$params['shipping']['city'],
				\HelperClass::convertStateCodeToName($params['shipping']['country'], $params['shipping']['state']),
				ltrim($params['shipping']['address_1']) . " " . $params['shipping']['address_2'],
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
			$params['samedaycourier-package-observation']
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

		saveAwb($awbDetails);

		return wp_redirect(add_query_arg('add-awb', 'success', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
	}

	public function removeAwb($awb)
	{
		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
		));

		try {
			$sameday->deleteAwb(new Sameday\Requests\SamedayDeleteAwbRequest($awb->awb_number));
			deleteAwb($awb->id);
		} catch (\Exception $e) {
			$errors = $e->getMessage();
		}

		if (isset($errors)) {
			return wp_redirect(add_query_arg('remove-awb', 'error', "post.php?post={$awb->order_id}&action=edit"));
		}

		return wp_redirect(add_query_arg('remove-awb', 'success', "post.php?post={$awb->order_id}&action=edit"));
	}

	public function showAwbAsPdf($orderId)
	{
		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
		));

		$awb = getAwbForOrderId($orderId);

		try {
			$content = $sameday->getAwbPdf(
				new Sameday\Requests\SamedayGetAwbPdfRequest(
					$awb->awb_number,
					new Sameday\Objects\Types\AwbPdfType(Sameday\Objects\Types\AwbPdfType::A4)
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
		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
		));

		$parcels = unserialize(getAwbForOrderId($orderId)->parcels);

		if (empty($parcels)) {
			return;
		}

		foreach ($parcels as $parcel) {
			$parcelStatus = $sameday->getParcelStatusHistory(new \Sameday\Requests\SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber()));
			refreshPackageHistory(
				$orderId,
				$parcel->getAwbNumber(),
				$parcelStatus->getSummary(),
				$parcelStatus->getHistory(),
				$parcelStatus->getExpeditionStatus()
			);
		}

		$packages = getPackagesForOrderId($orderId);

		return createAwbHistoryTable($packages);
	}

	public function addNewParcel($params)
	{
		$is_testing = $this->samedayOptions['is_testing'] === 'yes' ? 1 : 0;

		$sameday = new \Sameday\Sameday(Api::initClient(
			$this->samedayOptions['user'],
			$this->samedayOptions['password'],
			$is_testing
		));

		$awb = getAwbForOrderId($params['samedaycourier-order-id']);

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

		updateParcels($awb->order_id, serialize($parcels));

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
