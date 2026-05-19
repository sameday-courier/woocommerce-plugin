<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayAuthenticationException;
use Sameday\Exceptions\SamedayAuthorizationException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayNotFoundException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Exceptions\SamedayServerException;
use Sameday\Objects\CityObject;
use Sameday\Objects\CountyObject;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceVerifier;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Utils\Helper;

class ApiRequestsHandler
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayCityRepository $samedayCityRepository
     */
    private SamedayCityRepository $samedayCityRepository;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct()
    {
        $this->dbHandler = new DbHandler();
        $this->samedayServiceRepository = new SamedayServiceRepository($this->dbHandler);
        $this->samedayAwbRepository = new SamedayAwbRepository($this->dbHandler);
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
		if (!UserPermissionChecker::hasAllowedRole() || !NonceVerifier::verify($params['_wpnonce'], 'add-awb')) {
			$noticeMessage = __('You are not allowed to do this operation !', SamedayConstants::TEXT_DOMAIN);
			NoticerHandler::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

			Redirector::to('post.php', [
                'post' => $params['samedaycourier-order-id'],
                'action' => 'edit',
                'add-awb' => 'error',
            ]);
		}

        if (empty(OptionsHandler::getSamedayOptions()) ) {
            Redirector::to('post.php', [
                'post' => $params['samedaycourier-order-id'],
                'action' => 'edit',
            ]);
        }

        if (empty($params['shipping_lines'])) {
            Redirector::to('post.php', [
                'post' => $params['samedaycourier-order-id'],
                'action' => 'edit',
                'add-awb' => 'error',
            ]);
        }

		$service = $this->samedayServiceRepository->getServiceSameday(
            (int) $params['samedaycourier-service']
        );

        if (null === $service) {
            NoticerHandler::addFlashNotice(
                'add_awb_notice',
                __('Selected service could not be found.', SamedayConstants::TEXT_DOMAIN),
                'error',
                true
            );

            Redirector::to('post.php', [
                'post' => $params['samedaycourier-order-id'],
                'action' => 'edit',
                'add-awb' => 'error',
            ]);
        }

        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes(
            $service->getSamedayId()
        );
        $serviceTaxIds = array();

        if (isset($params['samedaycourier-open-package-status'])) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === (int) $params['samedaycourier-package-type']
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

		if (isset($params['samedaycourier-locker_first_mile'])) {
			foreach ($optionalServices as $optionalService) {
				if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
				    && $optionalService->getPackageType()->getType() === (int) $params['samedaycourier-package-type']
				) {
					$serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
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
	    if (false === Helper::validatePostalCode($postalCode, $state)) {
		    $postalCode = null;
	    }

	    $county = Helper::convertStateCodeToName(
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
            $inputErrors[] = __('Must complete phone number!', SamedayConstants::TEXT_DOMAIN);
        }

        if ('' === $email = $params['billing']['email'] ?? '') {
            $inputErrors[] = __('Must complete email!', SamedayConstants::TEXT_DOMAIN);
        }

        if (!empty($inputErrors)) {
            NoticerHandler::addFlashNotice(
                'add_awb_notice',
                implode('<br />', $inputErrors),
                'error',
                true
            );
            Redirector::to('post.php', [
                'post' => $params['samedaycourier-order-id'],
                'action' => 'edit',
                'add-awb' => 'error',
            ]);
        }

	    $lockerId = null;
        $oohLastMile = null;
        if ('' !== ($locker = $params['locker'] ?? '')
            && Helper::isOohDeliveryOption($service->getSamedayCode())
        ) {
	        $locker = json_decode(
		        $locker,
				true,
				512,
				JSON_THROW_ON_ERROR
	        );

            if ($service->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
                $lockerId = $locker['id'] ?? $locker['lockerId'];
            }

            if ($service->getSamedayCode() === SamedayConstants::PUDO_CODE) {
                $oohLastMile = $locker['id'] ?? $locker['lockerId'];
            }

	        $city = $locker['city'] ?? $city;
	        $county = $locker['county'] ?? $county;
	        $address = $locker['address'] ?? $address;
			$postalCode = $locker['postalCode'] ?? $postalCode;
	        $address_1 = $address;
	        $address_2 = $locker['name'];
	        $state = Helper::convertStateNameToCode($country, $county);
        }

        $post_meta_samedaycourier_address_hd = Helper::parsePostMetaSamedaycourierAddressHd(
            $params['samedaycourier-order-id']
        );
	    if (!Helper::isOohDeliveryOption($service->getSamedayCode())) {
            if (null !== $post_meta_samedaycourier_address_hd) {
                $city = $post_meta_samedaycourier_address_hd['city'];
                $county = Helper::convertStateCodeToName(
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
                $county = Helper::convertStateCodeToName(
                    $country,
                    $state
                );
                $postalCode = $params['billing']['postcode'];
            }
	    }

        $sameday = new Sameday(SdkInitiator::init());

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
	        $service->getSamedayId(),
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
            SamedayConstants::CURRENCY_MAPPER[$country]
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
            $noticeMessage = Helper::parseAwbErrors($errors);
            NoticerHandler::addFlashNotice('add_awb_notice', $noticeMessage, 'error', true);

			Redirector::to('post.php', [
				'post' => $params['samedaycourier-order-id'],
				'action' => 'edit',
				'add-awb' => 'error',
			]);
        }

        $awbDetails = array(
            'order_id' => $params['samedaycourier-order-id'],
            'awb_number' => $awb->getAwbNumber(),
            'parcels' => serialize($awb->getParcels()),
            'awb_cost' => $awb->getCost()
        );

        $this->samedayAwbRepository->saveAwb($awbDetails);

        $samedayOrderItemId = null;
		$shippingLines = (array) $params['shipping_lines'];
        foreach ($shippingLines as $id => $shippingLine) {
            $samedayOrderItemId = $id;
			if (null !== $samedayOrderItemId) {
				break;
			}
        }

        $metas = array(
            'service_id' => $service->getSamedayId(),
            'service_code' => $service->getSamedayCode()
        );

        try {
            Helper::updateAddressFields(
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
            $this->dbHandler->updateRow(
                $this->dbHandler->buildTableName('woocommerce_order_items'),
                ['order_item_name' => $service->getName() ?? $service->getSamedayName() ?? ''],
                ['order_item_id' => $samedayOrderItemId]
            );
        } catch (Exception $exception) {}

        Redirector::to('post.php', [
			'post' => $params['samedaycourier-order-id'],
			'action' => 'edit',
			'add-awb' => 'success',
        ]);
    }

    /**
     * @return array
     */
    public static function getCounties(): array
    {
        try {
            $sameday = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException|Exception $exception) {
            return [];
        }

        try{
            $samedayCounties = $sameday->getCounties(new SamedayGetCountiesRequest(null))
                ->getCounties()
            ;
        } catch (Exception $e) {
            return [];
        }

        return array_map(static function(CountyObject $county){
            return ['id' => $county->getId(), 'name' => $county->getName()];
        }, $samedayCounties);
    }

    /**
     * @param $countyId
     *
     * @return array
     */
    public static function getCities($countyId): array
    {
        try {
            $sameday = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            return [];
        }

        $page = 1;
        $remoteCities = [];
        do {
            $request = new SamedayGetCitiesRequest($countyId);
            $request->setPage($page++);

            try {
                $cities = $sameday->getCities($request);
            } catch (Exception $e) {
                return [];
            }

            foreach ($cities->getCities() as $city) {
                // Save as current sameday service.
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        if (!empty($remoteCities)) {
            return array_map(static function(CityObject $city){
                return [
                    'id' => $city->getId(),
                    'name' => $city->getName()
                ];
            }, $remoteCities);
        }

        return [];
    }
}
