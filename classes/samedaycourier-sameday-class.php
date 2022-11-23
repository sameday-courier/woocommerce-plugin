<?php

use Sameday\Exceptions\SamedayAuthenticationException;
use Sameday\Exceptions\SamedayAuthorizationException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayNotFoundException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Exceptions\SamedayServerException;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Requests\SamedayPostParcelRequest;

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
	 * @throws SamedayAuthorizationException
	 * @throws SamedayServerException
	 */
    public function refreshServices(): bool
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
            $request = new SamedayGetServicesRequest();
            $request->setPage($page++);

            try {
                $services = $sameday->getServices($request);
            } catch (Exception $e) {
                return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
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
            static function ($service) {
                return array(
                    'id' => $service->id,
                    'sameday_id' => (int) $service->sameday_id
                );
            },

            SamedayCourierQueryDb::getServices(SamedayCourierHelperClass::isTesting())
        );

        // Delete local services that aren't present in remote services anymore.
        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices, true)) {
                SamedayCourierQueryDb::deleteService($localService['id']);
            }
        }

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
    }

	/**
	 * @throws SamedaySDKException
	 */
	public function refreshPickupPoints(): bool
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
            } catch (Exception $e) {
	            return wp_redirect(admin_url() . 'admin.php?page=sameday_pickup_points');
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
            static function ($pickupPoint) {
                return array(
                    'id' => $pickupPoint->id,
                    'sameday_id' => (int) $pickupPoint->sameday_id
                );
            },

            SamedayCourierQueryDb::getPickupPoints(SamedayCourierHelperClass::isTesting())
        );

        // Delete local pickup points that aren't present in remote pickup points anymore.
        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, true)) {
                SamedayCourierQueryDb::deletePickupPoint($localPickupPoint['id']);
            }
        }

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_pickup_points');
    }

    /**
     * @return bool
     * @throws SamedayAuthorizationException
     * @throws SamedayBadRequestException
     * @throws SamedaySDKException
     * @throws SamedayServerException
     */
    public function refreshLockers(): bool
    {
        if (empty(SamedayCourierHelperClass::getSamedaySettings()) ) {
            return wp_redirect(admin_url() . 'admin.php?page=sameday_lockers');
        }

        $this->updateLockersList();

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_lockers');
    }

	/**
	 * @throws SamedaySDKException
	 */
	public function updateLockersList(): void
	{
		$sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
			SamedayCourierHelperClass::getSamedaySettings()['user'],
			SamedayCourierHelperClass::getSamedaySettings()['password'],
			SamedayCourierHelperClass::getApiUrl()
		));

		$request = new Sameday\Requests\SamedayGetLockersRequest();

		try {
			$lockers = $sameday->getLockers($request);
		} catch (Exception $exception) {return;}

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
			static function ($locker) {
				return array(
					'id' => $locker->id,
					'locker_id' => (int) $locker->locker_id
				);
			},

			SamedayCourierQueryDb::getLockers(SamedayCourierHelperClass::isTesting())
		);

		// Delete local lockers that aren't present in remote lockers anymore.
		foreach ($localLockers as $localLocker) {
			if (!in_array($localLocker['locker_id'], $remoteLockers, true)) {
				SamedayCourierQueryDb::deleteLocker($localLocker['id']);
			}
		}

		$this->updateLastSyncTimestamp();
	}

    private function updateLastSyncTimestamp(): void
    {
        $time = time();

		$samedayOptions = SamedayCourierHelperClass::getSamedaySettings();
	    $samedayOptions['sameday_sync_lockers_ts'] = $time;

        update_option('woocommerce_samedaycourier_settings', $samedayOptions);
    }

    /**
     * @return bool
     */
    public function editService(): bool
    {
        if (!($_POST['action'] === 'edit_service')) {
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
            if ($field_value['required'] && ('' === trim($field_value['value']))) {
                $errors[] = __("The $field must not be empty");
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
	 * @throws SamedayAuthenticationException
	 * @throws SamedayAuthorizationException
	 * @throws SamedayNotFoundException
	 * @throws SamedayOtherException
	 * @throws SamedaySDKException
	 * @throws SamedayServerException
	 * @throws JsonException
	 */
    public function postAwb($params): bool
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


		$post_meta_samedaycourier_order_id = get_post_meta( $params['samedaycourier-order-id'], '_sameday_shipping_locker_id', true);

	    $lockerDetails = null;
        $lockerDetailsForm = json_decode(stripslashes(html_entity_decode($params['locker_id'])), true );
       
		if ('' !== $post_meta_samedaycourier_order_id) {
            update_post_meta($params['order_id'],'_sameday_shipping_locker_id',$params['locker_id']);
			$lockerDetails = json_decode(get_post_meta( $params['order_id'], '_sameday_shipping_locker_id', true ), true, 512, JSON_THROW_ON_ERROR );
		}
       
	    $locker = null;
        if (isset($lockerDetailsForm['id'])) {
            $locker = $lockerDetailsForm['id'];
        } else if ('' !== $post_meta_samedaycourier_order_id) {
            $locker = $post_meta_samedaycourier_order_id;
        }

        $city =  $params['shipping']['city'];
        $county =  SamedayCourierHelperClass::convertStateCodeToName($params['shipping']['country'], $params['shipping']['state']);
        $address = ltrim($params['shipping']['address_1']) . ' ' . $params['shipping']['address_2'];

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
	        SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
	        SamedayCourierHelperClass::getApiUrl()
        ));

        $parcelDimensions[] = new ParcelDimensionsObject(
            $params['samedaycourier-package-weight'] <= 0 ? 1 : $params['samedaycourier-package-weight'],
            $params['samedaycourier-package-length'],
            $params['samedaycourier-package-height'],
            $params['samedaycourier-package-width']
        );

        $companyObject = null;
        if ('' !== $params['shipping']['company']) {
            $companyObject = new CompanyEntityObject(
                $params['shipping']['company'],
                '',
                '',
                '',
                ''
            );
        }

        $request = new SamedayPostAwbRequest(
            $params['samedaycourier-package-pickup-point'],
            null,
            new PackageType($params['samedaycourier-package-type']),
            $parcelDimensions,
            $serviceId,
            new AwbPaymentType($params['samedaycourier-package-awb-payment']),
            new AwbRecipientEntityObject(
                $city,
                $county,
                $address,
                ltrim($params['shipping']['first_name']) . " " . $params['shipping']['last_name'],
	            $params['billing']['phone'] ?? "",
	            $params['billing']['email'] ?? "",
                $companyObject,
                $params['shipping']['postcode']
            ),
            $params['samedaycourier-package-insurance-value'],
            $params['samedaycourier-package-repayment'],
            new CodCollectorType( CodCollectorType::CLIENT),
            null,
            $serviceTaxIds,
            null,
	        $params['samedaycourier-client-reference'],
            $params['samedaycourier-package-observation'],
            '',
            '',
            $locker
        );

	    $errors = null;
	    $awb = null;
        try {
            // No errors, post AWB.
            $awb = $sameday->postAwb($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
        } catch (Exception $e) {
			$errors[] = $e->getMessage();
        }

        if (null !== $errors && null === $awb) {
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
		$shippingLines = (array) $params['shipping_lines'];
        foreach ($shippingLines as $id => $shippingLine) {
            $samedayOrderItemId = $id;
			if (null !== $samedayOrderItemId) {
				break;
			}
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
    public function removeAwb($awb): bool
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));

        try {
            $sameday->deleteAwb(new Sameday\Requests\SamedayDeleteAwbRequest($awb->awb_number));
            SamedayCourierQueryDb::deleteAwbAndParcels($awb);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if (isset($error)) {
            SamedayCourierHelperClass::addFlashNotice('remove_awb_notice', $error, 'error', true);

            return wp_redirect(add_query_arg('remove-awb', 'error', "post.php?post=$awb->order_id&action=edit"));
        }

        return wp_redirect(add_query_arg('remove-awb', 'success', "post.php?post=$awb->order_id&action=edit"));
    }

    /**
     * @param $orderId
     *
     * @return string
     * @throws SamedaySDKException
     */
    public function showAwbAsPdf($orderId): string
    {
        $defaultLabelFormat = SamedayCourierHelperClass::getSamedaySettings()['default_label_format'];

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));

        $awb = SamedayCourierQueryDb::getAwbForOrderId($orderId);

	    $errors = null;
	    $pdf = null;
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

        if (null !== $errors && null === $pdf) {
            return wp_redirect(add_query_arg('show-awb', 'error', "post.php?post=$awb->order_id&action=edit"));
        }

        header('Content-type: application/pdf');
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");

        echo $pdf;

		exit();
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
                $parcelStatus = $sameday->getParcelStatusHistory(new SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber()));
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
     * @throws SamedayAuthenticationException
     * @throws SamedayAuthorizationException
     * @throws SamedayNotFoundException
     * @throws SamedayOtherException
     * @throws SamedaySDKException
     * @throws SamedayServerException
     */
    public function addNewParcel($params): bool
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
                SamedayCourierHelperClass::getSamedaySettings()['user'],
                SamedayCourierHelperClass::getSamedaySettings()['password'],
                SamedayCourierHelperClass::getApiUrl()
            )
        );

        $awb = SamedayCourierQueryDb::getAwbForOrderId($params['samedaycourier-order-id']);

        $position = $this->getPosition($awb->parcels);

        $request = new SamedayPostParcelRequest(
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
        } catch ( SamedayBadRequestException $e) {
            $errors = $e->getErrors();
        }

        if (isset($errors)) {
            $noticeError = SamedayCourierHelperClass::parseAwbErrors($errors);
            SamedayCourierHelperClass::addFlashNotice('add_new_parcel_notice', $noticeError, 'error', true);

            return wp_redirect(add_query_arg('add-new-parcel', 'error', "post.php?post=$awb->order_id&action=edit"));
        }

        $parcels = array_merge(unserialize($awb->parcels), array(new ParcelObject(
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
    private function getPosition($parcels): int
    {
        $nrOfParcels = count(unserialize($parcels));

        return $nrOfParcels + 1;
    }
}
