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
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
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
     * @var GenerateAwbItem $awbItem
     */
    private GenerateAwbItem $awbItem;

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

    public function __construct(
        GenerateAwbRequest $generateAwbRequest
    )
    {
        $this->awbItem = $generateAwbRequest->generateAwbItem;
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
        $item = $this->awbItem;

        $service = $this->samedayServiceRepository->getServiceSameday($item->getServiceId());
        $awbValidator = (new GenerateAwbValidator(
            new GenerateAwbValidatorRequest(
                $service,
                $item
            )
        ))->validate();

        if ($awbValidator->hasErrors()) {
            return new GenerateAwbResponse(
                $awbValidator->toString(),
                ResponseNoticeType::ERROR
            );
        }

        $serviceTaxIds = (new AwbGenerateServiceTaxResolver(
            $service,
            $this->samedayServiceRepository,
            $item,
        ))->resolve();

        $awbRecipient = (new AwbGenerateRecipientResolver(
            $item
        ))->resolve();

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



        $request = new SamedayPostAwbRequest(
            $item->getPickupPointId(),
            null,
            new PackageType($item->getPackageType()),
            $item->getParcelsDimensions(),
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
            $awbRecipient,
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
                $this->parseAwbErrors($errors),
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

    /**
     * @param array $errors
     *
     * @return string
     */
    private function parseAwbErrors(array $errors): string
    {
        $allErrors = array();
        foreach ($errors as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $message) {
                    $allErrors[] = implode('.', $error['key']) . ': ' . $message;
                }
            } else {
                $allErrors[] = sprintf('%s : %s',
                    $error['code'] ?? 'Generic Error',
                    $error['message'] ?? 'Something went wrong'
                );
            }
        }

        return implode('<br/>', $allErrors);
    }
}
