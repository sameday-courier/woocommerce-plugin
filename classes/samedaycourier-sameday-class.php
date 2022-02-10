<?php

use Sameday\Exceptions\SamedaySDKException;

if (! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Sameday
 */
class Sameday
{
	/**
	 * @return bool
	 * @throws SamedaySDKException
	 * @throws \Sameday\Exceptions\SamedayAuthorizationException
	 * @throws \Sameday\Exceptions\SamedayServerException
	 */
    public function refreshServices()
    {
        if (empty(SamedayCourierHelperClass::getSamedaySettings())) {
            wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
        }

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
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
                $service = SamedayCourierQueryDb::getServiceSameday($serviceObject->getId(), SamedayCourierHelperClass::isTesting());
                if (! $service) {
                    // Service not found, add it.
                    SamedayCourierQueryDb::addService($serviceObject, SamedayCourierHelperClass::isTesting());
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

            SamedayCourierQueryDb::getServices(SamedayCourierHelperClass::isTesting())
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
        if (empty(SamedayCourierHelperClass::getSamedaySettings()) ) {
            wp_redirect(admin_url() . 'admin.php?page=sameday_pickup_points');
        }

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
	        SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
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
                $pickupPoint = SamedayCourierQueryDb::getPickupPointSameday($pickupPointObject->getId(), SamedayCourierHelperClass::isTesting());
                if (!$pickupPoint) {
                    // Pickup point not found, add it.
                    SamedayCourierQueryDb::addPickupPoint($pickupPointObject, SamedayCourierHelperClass::isTesting());
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

            SamedayCourierQueryDb::getPickupPoints(SamedayCourierHelperClass::isTesting())
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
     * @throws SamedaySDKException
     * @throws \Sameday\Exceptions\SamedayServerException
     */
    public function refreshLockers()
    {
        if (empty(SamedayCourierHelperClass::getSamedaySettings()) ) {
            wp_redirect(admin_url() . 'admin.php?page=sameday_lockers');
        }

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
	        SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
	        SamedayCourierHelperClass::getApiUrl()
        ));

        $request = new Sameday\Requests\SamedayGetLockersRequest();

        try {
            $lockers = $sameday->getLockers($request);
        } catch (\Sameday\Exceptions\SamedayAuthenticationException $e) {
            wp_redirect(admin_url() . 'admin.php?page=sameday_lockers');
        }

        $remoteLockers = [];
        foreach ($lockers->getLockers() as $lockerObject) {
            $locker = SamedayCourierQueryDb::getLockerSameday($lockerObject->getId(), SamedayCourierHelperClass::isTesting());
            if (!$locker) {
                // Pickup point not found, add it.
                SamedayCourierQueryDb::addLocker($lockerObject, SamedayCourierHelperClass::isTesting());
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

            SamedayCourierQueryDb::getLockers(SamedayCourierHelperClass::isTesting())
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

    private function updateLastSyncTimestamp()
    {
        $time = time();

		$samedayOptions = SamedayCourierHelperClass::getSamedaySettings();
	    $samedayOptions['sameday_sync_lockers_ts'] = $time;

        update_option('woocommerce_samedaycourier_settings', $samedayOptions);
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
            )
        );

        $errors = array();

        foreach ($post_fields as $field => $field_value) {
            if ($field_value['required'] && !strlen($field_value['value'])) {
                $errors[] = __("The {$field} must not be empty");
            }
        }

        // End of Validation check.

        if (empty($errors)) {
            $service = array(
                'id' => (int) $post_fields['id']['value'],
                'name' => $post_fields['name']['value'],
                'price' => $post_fields['price']['value'],
                'price_free' => $post_fields['price_free']['value'],
                'status' => $post_fields['status']['value']
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
     * @throws SamedaySDKException
     * @throws \Sameday\Exceptions\SamedayServerException
     */
    public function postAwb($params)
    {
        if (empty(SamedayCourierHelperClass::getSamedaySettings()) ) {
            wp_redirect(admin_url() . "post.php?post={$params['samedaycourier-order-id']}&action=edit");
        }

        if (empty($params['shipping_lines'])) {
            return wp_redirect(add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
        }

        $serviceId = $params['samedaycourier-service'];

        $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes($serviceId, SamedayCourierHelperClass::isTesting());
        $serviceTaxIds = array();
        if (!empty($params['samedaycourier-open-package-status'])) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === 'OPCG' && $optionalService->getPackageType()->getType() === (int) $params['samedaycourier-package-type']) {
                    $serviceTaxIds[] = $optionalService->getId();
                    break;
                }
            }
        }

        $lockerId = get_post_meta($params['samedaycourier-order-id'], '_sameday_shipping_locker_id', true );
        if (isset($lockerId)) {
            $locker = SamedayCourierQueryDb::getLockerSameday($lockerId, SamedayCourierHelperClass::isTesting());
        }

        $city = isset($locker) ? $locker->city : $params['shipping']['city'];
        $county = isset($locker)  ? $locker->county : SamedayCourierHelperClass::convertStateCodeToName($params['shipping']['country'], $params['shipping']['state']);
        $address = isset($locker) ? $locker->address : ltrim($params['shipping']['address_1']) . ' ' . $params['shipping']['address_2'];

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
	        SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
	        SamedayCourierHelperClass::getApiUrl()
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
            $serviceTaxIds,
            null,
	        $params['samedaycourier-client-reference'],
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
            $noticeMessage = SamedayCourierHelperClass::parseAwbErrors($errors);
            SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);
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

        $service = SamedayCourierQueryDb::getServiceSameday($serviceId, SamedayCourierHelperClass::isTesting());
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

	/**
	 * @param $awb
	 *
	 * @return bool
	 * @throws SamedaySDKException
	 */
    public function removeAwb($awb)
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));

        try {
            $sameday->deleteAwb(new Sameday\Requests\SamedayDeleteAwbRequest($awb->awb_number));
            SamedayCourierQueryDb::deleteAwbAndParcels($awb);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if (isset($error)) {
            SamedayCourierHelperClass::addFlashNotice('remove_awb_notice', $error, 'error', true);
            return wp_redirect(add_query_arg('remove-awb', 'error', "post.php?post={$awb->order_id}&action=edit"));
        }

        return wp_redirect(add_query_arg('remove-awb', 'success', "post.php?post={$awb->order_id}&action=edit"));
    }

    /**
     * @param $orderId
     *
     * @return bool
     * @throws SamedaySDKException
     */
    public function showAwbAsPdf($orderId)
    {
        $defaultLabelFormat = SamedayCourierHelperClass::getSamedaySettings()['default_label_format'];

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
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
	 * @throws SamedaySDKException
	 */
    public function showAwbHistory($orderId)
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));

        $awb = SamedayCourierQueryDb::getAwbForOrderId($orderId);
        if (empty($awb)) {
            return;
        }

        $parcels = unserialize($awb->parcels);

        global $wpdb;
	    $wpdb->delete($wpdb->prefix . 'sameday_package', ['order_id' => $orderId]);

	    foreach ($parcels as $parcel) {
            try {
                $parcelStatus = $sameday->getParcelStatusHistory(new \Sameday\Requests\SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber()));
            } catch (Exception $exception) {
                return samedaycourierCreateAwbHistoryTable(array());
            }

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
     * @throws SamedaySDKException
     * @throws \Sameday\Exceptions\SamedayServerException
     */
    public function addNewParcel($params)
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
                SamedayCourierHelperClass::getSamedaySettings()['user'],
                SamedayCourierHelperClass::getSamedaySettings()['password'],
                SamedayCourierHelperClass::getApiUrl()
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
            $noticeError = SamedayCourierHelperClass::parseAwbErrors($errors);
            SamedayCourierHelperClass::addFlashNotice('add_new_parcel_notice', $noticeError, 'error', true);
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
