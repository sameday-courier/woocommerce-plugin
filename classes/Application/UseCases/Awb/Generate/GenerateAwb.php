<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwb
{
    /**
     * @var GenerateAwbRequest
     */
    private GenerateAwbRequest $generateAwbRequest;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var DbHandler $dbHandler
     */
    private DbHandler $dbHandler;

    public function __construct(GenerateAwbRequest $generateAwbRequest)
    {
        $this->generateAwbRequest = $generateAwbRequest;
        $this->sameday = $generateAwbRequest->sameday;
        $this->dbHandler = $generateAwbRequest->dbHandler;
        $this->samedayServiceRepository = $generateAwbRequest->samedayServiceRepository;
        $this->samedayAwbRepository = $generateAwbRequest->samedayAwbRepository;
    }

    /**
     * @return GenerateAwbResponse
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): GenerateAwbResponse
    {
        $item = $this->generateAwbRequest->generateAwbItem;
        $shipping = $item->getShipping();
        $billing = $item->getBilling();

        if (empty(OptionsHandler::getSamedayOptions())) {
            return new GenerateAwbResponse(
                "No sameday options available.",
                ResponseNoticeType::ERROR,
            );
        }

        if (empty($item->getShippingLines())) {
            return new GenerateAwbResponse(
                "No shipping lines for this awb item.",
                ResponseNoticeType::ERROR
            );
        }

        $service = $this->samedayServiceRepository->getServiceSameday($item->getServiceId());

        if (null === $service) {
            return new GenerateAwbResponse(
                "Selected service could not be found.",
                ResponseNoticeType::ERROR,
            );
        }

        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes(
            $service->getSamedayId()
        );
        $serviceTaxIds = [];

        if ($item->hasOpenPackage()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $item->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($item->hasLockerFirstMile()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $item->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        $city = $shipping->getCity();
        if ('' === $city || null === $city) {
            $city = $billing->getCity();
        }

        $state = $shipping->getState();
        if ('' === $state || null === $state) {
            $state = $billing->getState();
        }

        $country = $shipping->getCountry();
        if ('' === $country || null === $country) {
            $country = $billing->getCountry();
        }

        $postalCode = $shipping->getPostcode();
        if ('' === $postalCode || null === $postalCode) {
            $postalCode = $billing->getPostcode();
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
            ltrim($shipping->getAddress1() ?? ''),
            ltrim($shipping->getAddress2() ?? '')
        );

        $address_1 = $shipping->getAddress1();
        $address_2 = $shipping->getAddress2();

        $name = sprintf(
            '%s %s',
            ltrim($shipping->getFirstName() ?? ''),
            ltrim($shipping->getLastName() ?? '')
        );

        $inputErrors = null;
        if ('' === $phone = $billing->getPhone() ?? '') {
            $inputErrors[] = __('Must complete phone number!', SamedayConstants::TEXT_DOMAIN);
        }

        if ('' === $email = $billing->getEmail() ?? '') {
            $inputErrors[] = __('Must complete email!', SamedayConstants::TEXT_DOMAIN);
        }

        if (!empty($inputErrors)) {
            return new GenerateAwbResponse(
                implode('<br />', $inputErrors),
                ResponseNoticeType::ERROR,
            );
        }

        $lockerId = null;
        $oohLastMile = null;
        if (null !== ($locker = $item->getLocker())
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
            $item->getOrderId()
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
                $city = $billing->getCity();
                $address_1 = $billing->getAddress1();
                $address_2 = $billing->getAddress2();
                $address = sprintf(
                    '%s %s',
                    $address_1,
                    $address_2
                );
                $country = $billing->getCountry();
                $state = $billing->getState();
                $county = Helper::convertStateCodeToName(
                    $country,
                    $state
                );
                $postalCode = $billing->getPostcode();
            }
        }

        $companyObject = null;
        if ('' !== ($shipping->getCompany() ?? '')) {
            $companyObject = new CompanyEntityObject(
                $shipping->getCompany(),
                '',
                '',
                '',
                ''
            );
        }

        $request = new SamedayPostAwbRequest(
            $item->getPickupPointId(),
            null,
            new PackageType($item->getPackageType()),
            $item->getParcelsDimensions(),
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
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
            $item->getInsuranceValue(),
            $item->getRepayment(),
            new CodCollectorType(CodCollectorType::CLIENT),
            null,
            $serviceTaxIds,
            null,
            $item->getClientReference(),
            $item->getObservation(),
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
            $awb = $this->sameday->postAwb($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
            if ($errors !== '') {
                try {
                    $rawResponse = $e->getRawResponse()->getBody();
                    $errorMessages = json_decode($rawResponse, false, 512, JSON_THROW_ON_ERROR)
                        ->errors
                        ->errors
                    ;
                    $errors[] = [
                        'key' => ['Validation Failed', ''],
                        'errors' => $errorMessages,
                    ];
                } catch (JsonException $exception) {
                    $errors[] = [
                        'key' => 'JSON Validation Failed',
                        'errors' => $exception->getMessage(),
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
            return new GenerateAwbResponse(
                Helper::parseAwbErrors($errors),
                ResponseNoticeType::ERROR,
            );
        }

        $awbDetails = [
            'order_id' => $item->getOrderId(),
            'awb_number' => $awb->getAwbNumber(),
            'parcels' => serialize($awb->getParcels()),
            'awb_cost' => $awb->getCost(),
        ];

        $this->samedayAwbRepository->saveAwb($awbDetails);

        $samedayOrderItemId = null;
        $shippingLines = $item->getShippingLines();
        foreach ($shippingLines as $id => $shippingLine) {
            $samedayOrderItemId = $id;
            if (null !== $samedayOrderItemId) {
                break;
            }
        }

        $metas = [
            'service_id' => $service->getSamedayId(),
            'service_code' => $service->getSamedayCode(),
        ];

        try {
            Helper::updateAddressFields(
                $item->getOrderId(),
                $address_1,
                $address_2,
                $name,
                $city,
                $state,
                $postalCode,
                $country
            );
        } catch (Exception $exception) {}

        foreach ($metas as $key => $value) {
            $shippingLine->update_meta_data($key, $value);
        }
        $shippingLine->save_meta_data();

        $shippingLine->set_method_id('samedaycourier');
        $shippingLine->save();

        try {
            $this->dbHandler->updateRow(
                $this->dbHandler->buildTableName('woocommerce_order_items'),
                ['order_item_name' => $service->getName() ?? $service->getSamedayName() ?? ''],
                ['order_item_id' => $samedayOrderItemId]
            );
        } catch (Exception $exception) {
            return new GenerateAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GenerateAwbResponse(
            "Awb generated successfully.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
