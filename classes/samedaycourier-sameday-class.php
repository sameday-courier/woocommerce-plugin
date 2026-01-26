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
	private const USER_ROLE_PERMISSIONS = [
		'administrator',
		'shop_manager',
	];

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

        // Update PUDO Service
        $lnService = SamedayCourierQueryDb::getServiceSamedayByCode(
            SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE,
            SamedayCourierHelperClass::isTesting()
        );

        $pudoService = SamedayCourierQueryDb::getServiceSamedayByCode(
            SamedayCourierHelperClass::PUDO_CODE,
            SamedayCourierHelperClass::isTesting()
        );

        if (null !== $lnService && null !== $pudoService) {
            $pudoService->status = $lnService->status;
            SamedayCourierQueryDb::updateService((array) $pudoService);
        }

        return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
    }

	/**
	 * @return void
	 */
    public function importCities(): void
    {
		if (false === SamedayCourierQueryDb::checkIfTableExists('sameday_cities')) {
			SamedayCourierQueryDb::createSamedayCitiesTable();
		}

		if (!file_exists($file = plugin_dir_path( __FILE__ ) . 'cities.json')) {
			return;
		}

		try {
			$cities = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $exception) {
			return;
		}

        // Remove all previews unnecessary stored data
        SamedayCourierQueryDb::truncateSamedayCityTable();
        delete_transient(SamedayCourierHelperClass::TRANSIENT_CACHE_KEY_FOR_CITIES);

	    foreach ($cities as $samedayCity) {
            if (array_key_exists($samedayCity->country_code, WC()->countries->get_shipping_countries())) {
                SamedayCourierQueryDb::addCity($samedayCity);
            }
	    }

		set_transient(
			SamedayCourierHelperClass::TRANSIENT_CACHE_KEY_FOR_CITIES,
			SamedayCourierQueryDb::getCities()
		);
    }

	/**
	 * @throws SamedaySDKException
	 */
	public function refreshSamedayPickupPoints(): bool
    {
        if (empty(SamedayCourierHelperClass::getSamedaySettings())) {
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
    public function refreshSamedayLockers(): bool
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

		$page = 1;
		$remoteLockers = [];
		do {
			$request = new Sameday\Requests\SamedayGetLockersRequest();
			$request->setPage($page++);

			try {
				$lockers = $sameday->getLockers($request);
			} catch (Exception $exception) {return;}

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
		} while ($page < $lockers->getPages());

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
	    if (false === $this->isAllowed() || false === wp_verify_nonce($_POST['_wpnonce'], 'edit-service')) {
		    return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
		}

        if (!($_POST['action'] === 'edit_service')) {
            return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
        }

        if (null === $_POST['samedaycourier-service-name'] ?? null) {
            $_POST['samedaycourier-service-name'] = SamedayCourierHelperClass::OOH_SERVICES_LABELS[
                SamedayCourierHelperClass::getHostCountry()
            ];
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
                $errors[] = __("The $field must not be empty", SamedayCourierHelperClass::TEXT_DOMAIN);
            }
        }

        // End of Validation check.

	    $priceFree = null;
		if ((float) $post_fields['price_free']['value'] > 0) {
			$priceFree = (float) $post_fields['price_free']['value'];
		}

        if (empty($errors)) {
            $currentService = (array) SamedayCourierQueryDb::getService($post_fields['id']['value']);
            $service = array(
                'id' => (int) $post_fields['id']['value'],
                'name' => SamedayCourierHelperClass::sanitizeInput($post_fields['name']['value']),
                'price' => (float) $post_fields['price']['value'],
                'price_free' => $priceFree,
                'status' => (int) $post_fields['status']['value']
            );

            SamedayCourierQueryDb::updateService($service);

            // Update PUDO
            if ($currentService['sameday_code'] === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE) {
                $pudoService = (array) SamedayCourierQueryDb::getServiceSamedayByCode(
                    SamedayCourierHelperClass::PUDO_CODE,
	                SamedayCourierHelperClass::isTesting()
                );

                $pudoService['status'] = $service['status'];
                SamedayCourierQueryDb::updateService($pudoService);
            }

            return wp_redirect(admin_url() . 'edit.php?post_type=page&page=sameday_services');
        }

		$fieldId = (int) $post_fields['id']['value'];

        return wp_redirect(admin_url() . "edit.php?post_type=page&page=sameday_services&action=edit&id='$fieldId'");
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
		if (false === $this->isAllowed() || false === wp_verify_nonce($params['_wpnonce'], 'add-awb')) {
			$noticeMessage = __('You are not allowed to do this operation !', SamedayCourierHelperClass::TEXT_DOMAIN);
			SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

			return wp_redirect(add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
		}

        if (empty(SamedayCourierHelperClass::getSamedaySettings()) ) {
            wp_redirect(admin_url() . "post.php?post={$params['samedaycourier-order-id']}&action=edit");
        }

        if (empty($params['shipping_lines'])) {
            return wp_redirect(add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit"));
        }

		$service = SamedayCourierQueryDb::getServiceSameday(
			$params['samedaycourier-service'],
			SamedayCourierHelperClass::isTesting()
		);

        $optionalServices = SamedayCourierQueryDb::getServiceIdOptionalTaxes(
            $service->sameday_id,
            SamedayCourierHelperClass::isTesting()
        );
        $serviceTaxIds = array();

        if (isset($params['samedaycourier-open-package-status'])) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayCourierHelperClass::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === (int) $params['samedaycourier-package-type']
                ) {
                    $serviceTaxIds[] = SamedayCourierHelperClass::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

		if (isset($params['samedaycourier-locker_first_mile'])) {
			foreach ($optionalServices as $optionalService) {
				if ($optionalService->getCode() === SamedayCourierHelperClass::PERSONAL_DELIVERY_OPTION_CODE
				    && $optionalService->getPackageType()->getType() === (int) $params['samedaycourier-package-type']
				) {
					$serviceTaxIds[] = SamedayCourierHelperClass::PERSONAL_DELIVERY_OPTION_CODE;
					break;
				}
			}
		}

	    /** Recipient details */
        $city = $params['shipping']['city'];
        if ('' === $city || null === $city) {
            $city = $params['billing']['city'];
        }

        $state = $params['shipping']['state'];
        if ('' === $state || null === $state) {
            $state = $params['billing']['state'];
        }

        $country = $params['shipping']['country'];
        if ('' === $country || null === $country) {
            $country = $params['billing']['country'];
        }

	    $postalCode = $params['shipping']['postcode'];
	    if ('' === $postalCode || null === $postalCode) {
		    $postalCode = $params['billing']['postcode'];
	    }
	    if (false === SamedayCourierHelperClass::validatePostalCode($postalCode, $state)) {
		    $postalCode = null;
	    }

	    $county = SamedayCourierHelperClass::convertStateCodeToName(
            $country,
            $state
	    );

	    $address = sprintf(
		    '%s %s',
		    ltrim($params['shipping']['address_1']),
		    ltrim($params['shipping']['address_2'])
	    );

		$address_1 = $params['shipping']['address_1'];
		$address_2 = $params['shipping']['address_2'];

	    $name = sprintf(
		    '%s %s',
		    ltrim($params['shipping']['first_name']),
		    ltrim($params['shipping']['last_name'])
	    );

        $inputErrors = null;
        if ('' === $phone = $params['billing']['phone'] ?? '') {
            $inputErrors[] = __('Must complete phone number!', SamedayCourierHelperClass::TEXT_DOMAIN);
        }

        if ('' === $email = $params['billing']['email'] ?? '') {
            $inputErrors[] = __('Must complete email!', SamedayCourierHelperClass::TEXT_DOMAIN);
        }

        if (!empty($inputErrors)) {
            SamedayCourierHelperClass::addFlashNotice(
                'add_awb_notice',
                implode('<br />', $inputErrors),
                'error',
                true
            );
            return wp_redirect(
                add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit")
            );
        }

	    $lockerId = null;
        $oohLastMile = null;
        if ('' !== ($locker = $params['locker'] ?? '')
            && SamedayCourierHelperClass::isOohDeliveryOption($service->sameday_code)
        ) {
	        $locker = json_decode(
		        $locker,
				true,
				512,
				JSON_THROW_ON_ERROR
	        );

            if ($service->sameday_code === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE) {
                $lockerId = $locker['id'] ?? $locker['lockerId'];
            }

            if ($service->sameday_code === SamedayCourierHelperClass::PUDO_CODE) {
                $oohLastMile = $locker['id'] ?? $locker['lockerId'];
            }

	        $city = $locker['city'] ?? $city;
	        $county = $locker['county'] ?? $county;
	        $address = $locker['address'] ?? $address;
			$postalCode = $locker['postalCode'] ?? $postalCode;
	        $address_1 = $address;
	        $address_2 = $locker['name'];
	        $state = SamedayCourierHelperClass::convertStateNameToCode($country, $county);
        }

        $post_meta_samedaycourier_address_hd = SamedayCourierHelperClass::parsePostMetaSamedaycourierAddressHd(
            $params['samedaycourier-order-id']
        );
	    if (!SamedayCourierHelperClass::isOohDeliveryOption($service->sameday_code)) {
            if (null !== $post_meta_samedaycourier_address_hd) {
                $city = $post_meta_samedaycourier_address_hd['city'];
                $county = SamedayCourierHelperClass::convertStateCodeToName(
                    $post_meta_samedaycourier_address_hd['country'],
                    $post_meta_samedaycourier_address_hd['state']
                );
                $address = sprintf(
                    '%s %s',
                    $post_meta_samedaycourier_address_hd['address_1'],
                    $post_meta_samedaycourier_address_hd['address_2']
                );
                $postalCode = $post_meta_samedaycourier_address_hd['postcode'];

                $address_1 = $post_meta_samedaycourier_address_hd['address_1'];
                $address_2 = $post_meta_samedaycourier_address_hd['address_2'];
                $state = $post_meta_samedaycourier_address_hd['state'];
            } else {
                $city = $params['billing']['city'];
                $address_1 = $params['billing']['address_1'];
                $address_2 = $params['billing']['address_2'];
                $address = sprintf(
                    '%s %s',
                    $address_1,
                    $address_2
                );
                $country = $params['billing']['country'];
                $state = $params['billing']['state'];
                $county = SamedayCourierHelperClass::convertStateCodeToName(
                    $country,
                    $state
                );
                $postalCode = $params['billing']['postcode'];
            }
	    }

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
	        SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
	        SamedayCourierHelperClass::getApiUrl()
        ));

        $parcelDimensions = [];
        // Iterate through the inputs based on their names
        foreach ($params as $key => $value) {
            // Match keys that belong to package data
            if (preg_match('/^samedaycourier-package-(weight|length|height|width)(\d+)$/', $key, $matches)) {
                $attribute = $matches[1]; // weight, length, height, or width
                $index = $matches[2];    // the number in the input name

                // Ensure the index exists in the parcelDimensions array
                if (!isset($parcelDimensions[$index])) {
                    $parcelDimensions[$index] = [
                        'weight' => null,
                        'length' => null,
                        'height' => null,
                        'width' => null
                    ];
                }

                // Assign the value to the correct attribute
                $parcelDimensions[$index][$attribute] = $value;
            }
        }

        // Transform the array into ParcelDimensionsObject instances
        $parcelDimensionsObjects = [];
        foreach ($parcelDimensions as $dimension) {
            $parcelDimensionsObjects[] = new ParcelDimensionsObject(
                $dimension['weight'],
                $dimension['length'],
                $dimension['height'],
                $dimension['width']
            );
        }

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
            $parcelDimensionsObjects,
	        $service->sameday_id,
            new AwbPaymentType($params['samedaycourier-package-awb-payment']),
            new AwbRecipientEntityObject(
                $city,
                $county,
                $address,
	            $name,
	            $phone,
	            $email,
                $companyObject,
	            $postalCode
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
            null,
	        $lockerId,
            null,
            $oohLastMile,
            SamedayCourierHelperClass::CURRENCY_MAPPER[$country]
        );

        $errors = null;
	    $awb = null;
        try {
            // No errors, post AWB.
            $awb = $sameday->postAwb($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
            if ($errors !== '') {
                try {
					$rawResponse = $e->getRawResponse()->getBody();
                    $errorMessages = json_decode($rawResponse, false, 512,JSON_THROW_ON_ERROR)
	                    ->errors
	                    ->errors
                    ;
                    $errors[] = [
                        'key' => ['Validation Failed', ''],
                        'errors' => $errorMessages
                    ];
                } catch (JsonException $exception) {
                    $errors[] = [
                        'key' => 'JSON Validation Failed',
                        'errors' => $exception->getMessage()
                    ];
                }
            }
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ('' === $message) {
                $message = 'The request could not be processed!';
            }
			$errors[] = [
                'code' => $e->getCode(),
                'message' => $message,
            ];
        }

        if (null !== $errors && null === $awb) {
            $noticeMessage = SamedayCourierHelperClass::parseAwbErrors($errors);
            SamedayCourierHelperClass::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

			return wp_redirect(
				add_query_arg('add-awb', 'error', "post.php?post={$params['samedaycourier-order-id']}&action=edit")
			);
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

        $metas = array(
            'service_id' => $service->sameday_id,
            'service_code' => $service->sameday_code
        );

        try {
            SamedayCourierHelperClass::updateAddressFields(
                $params['samedaycourier-order-id'],
                $address_1,
                $address_2,
                $name,
                $city,
                $state,
                $postalCode,
                $country
            );
        } catch (Exception $exception) {}

        // Add/update sameday metadata.
        foreach ($metas as $key => $value) {
            $shippingLine->update_meta_data($key, $value);
        }
        $shippingLine->save_meta_data();

        // Set sameday shipping method.
        $shippingLine->set_method_id('samedaycourier');
        $shippingLine->save();

        try {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'woocommerce_order_items',
                array('order_item_name' => $service->name),
                array('order_item_id' => $samedayOrderItemId)
            );
        } catch (Exception $exception) {}

        return wp_redirect(
			add_query_arg('add-awb', 'success', "post.php?post={$params['samedaycourier-order-id']}&action=edit")
        );
    }

	/**
	 * @param $awb
	 * @param $nonce
	 *
	 * @return bool
	 * @throws SamedaySDKException
	 */
    public function removeAwb($awb, $nonce): bool
    {
		if (false === $this->isAllowed() || false === wp_verify_nonce($nonce, 'remove-awb')) {
			return false;
		}

        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
	        SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));

        try {
            $sameday->deleteAwb(new Sameday\Requests\SamedayDeleteAwbRequest($awb->awb_number));
            SamedayCourierQueryDb::deleteAwbAndParcels($awb);
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $errors[] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        if (isset($errors)) {
            SamedayCourierHelperClass::addFlashNotice('remove_awb_notice', SamedayCourierHelperClass::parseAwbErrors($errors), 'error', true);

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
    public function showAwbAsPdf($orderId, $nonce): string
    {
	    if (false === $this->isAllowed() || false === wp_verify_nonce($nonce, 'show-as-pdf')) {
		    return false;
	    }


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

        $parcels = unserialize($awb->parcels, ['']);

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
     * @return bool
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function addNewParcel($params): bool
    {
		if (false === $this->isAllowed() || false === wp_verify_nonce($params['_wpnonce'], 'add-new-parcel')) {
			return false;
		}

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
                (float) number_format((float) $params['samedaycourier-parcel-weight'], 2),
                (float) number_format((float) $params['samedaycourier-parcel-length'], 2),
                (float) number_format((float) $params['samedaycourier-parcel-height'],2),
                (float) number_format((float) $params['samedaycourier-parcel-width'], 2)
            ),
            $position,
            $params['samedaycourier-parcel-observation'],
            null,
            $params['samedaycourier-parcel-is-last']
        );

	    $parcel = null;
        try {
            $parcel = $sameday->postParcel($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $errors[] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        if (isset($errors) && null === $parcel) {
            $noticeError = SamedayCourierHelperClass::parseAwbErrors($errors);
            SamedayCourierHelperClass::addFlashNotice('add_new_parcel_notice', $noticeError, 'error', true);

            return wp_redirect(add_query_arg('add-new-parcel', 'error', "post.php?post=$awb->order_id&action=edit"));
        }

        $parcels = array_merge(unserialize($awb->parcels, ['']), array(new ParcelObject(
                    $position,
                    $parcel->getParcelAwbNumber()
                )
            )
        );

        SamedayCourierQueryDb::updateParcels($awb->order_id, serialize($parcels));

        return wp_redirect(add_query_arg('add-new-parcel', 'success', "post.php?post=$awb->order_id&action=edit"));
    }


    /**
     * @throws SamedayOtherException
     * @throws SamedaySDKException
     * @throws SamedayBadRequestException
     * @throws SamedayServerException
     * @throws SamedayAuthenticationException
     * @throws SamedayAuthorizationException
     * @throws SamedayNotFoundException
     */
    public function addNewPickupPoint($params): void
    {
        $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
            SamedayCourierHelperClass::getSamedaySettings()['user'],
            SamedayCourierHelperClass::getSamedaySettings()['password'],
            SamedayCourierHelperClass::getApiUrl()
        ));
    }

	private function isAllowed(): bool
	{
		$currentUser = wp_get_current_user();
		$roles = $currentUser->roles ?? [];

		$userRolePermissions = self::USER_ROLE_PERMISSIONS;

		foreach ($userRolePermissions as $role) {
			if (in_array($role, $roles, true)) {
				return true;
			}
		}

		return false;
	}

    /**
     * @param $parcels
     *
     * @return int
     */
    private function getPosition($parcels): int
    {
        $nrOfParcels = count(unserialize($parcels, ['']));

        return $nrOfParcels + 1;
    }
}
