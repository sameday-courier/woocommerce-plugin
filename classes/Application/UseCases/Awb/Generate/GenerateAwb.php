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
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
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

        $service = $this->samedayServiceRepository->getServiceSameday($item->getService()->getId());
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

        $serviceTaxResponse = (new AwbGenerateServiceTaxResolver(
            $service,
            $this->samedayServiceRepository,
            $item,
        ))->resolve();

        $awbRecipientResolver = new AwbGenerateRecipientResolver($item);
        $awbRecipient = $awbRecipientResolver->resolve();

        $request = new SamedayPostAwbRequest(
            $item->getPickupPointId(),
            null,
            new PackageType($item->getPackageType()),
            $item->getParcelsDimensions(),
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
            new AwbRecipientEntityObject(
                $awbRecipient->getRecipient()->getCity(),
                $awbRecipient->getRecipient()->getState(),
                $awbRecipient->getRecipient()->getAddress(),
                $awbRecipient->getRecipient()->getName(),
                $awbRecipient->getRecipient()->getPhone(),
                $awbRecipient->getRecipient()->getEmail(),
            ),
            $item->getInsuranceValue(),
            $item->getRepayment(),
            new CodCollectorType(CodCollectorType::CLIENT),
            null,
            $serviceTaxResponse->getServiceTaxIds(),
            null,
            $item->getClientReference(),
            $item->getObservation(),
            '',
            '',
            null,
            $awbRecipient->getOoh()->getLockerId(),
            null,
            $awbRecipient->getOoh()->getOohLastMile(),
            $awbRecipient->getCurrency()
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
                $awbRecipient->getRecipient()->getAddress1(),
                $awbRecipient->getRecipient()->getAddress2(),
                $awbRecipient->getRecipient()->getName(),
                $awbRecipient->getRecipient()->getCity(),
                $awbRecipient->getRecipient()->getState(),
                $awbRecipient->getRecipient()->getPostcode(),
                $awbRecipient->getRecipient()->getCountry(),
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
