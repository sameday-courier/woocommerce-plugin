<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Exception;
use JsonException;
use Throwable;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\Common\AwbRemover;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateRecipientResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;

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

    /**
     * @var WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    private WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater;

    public function __construct(
        GenerateAwbRequest $generateAwbRequest
    )
    {
        $this->awbItem = $generateAwbRequest->generateAwbItem;
        $this->sameday = $generateAwbRequest->sameday;
        $this->dbHandler = $generateAwbRequest->dbHandler;
        $this->samedayServiceRepository = $generateAwbRequest->samedayServiceRepository;
        $this->samedayAwbRepository = $generateAwbRequest->samedayAwbRepository;
        $this->wooOrderShippingAddressUpdater = $generateAwbRequest->wooOrderShippingAddressUpdater;
    }

    /**
     * @return GenerateAwbResponse
     * @throws JsonException
     */
    public function execute(): GenerateAwbResponse
    {
        $item = $this->awbItem;

        $service = $item->getService();
        $pickupPoint = $item->getPickupPoint();
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

        $serviceTaxResolver = new AwbGenerateServiceTaxResolver(
            $service,
            $this->samedayServiceRepository,
            $item,
        );
        $serviceTax = $serviceTaxResolver->resolve();

        $awbRecipientResolver = new AwbGenerateRecipientResolver(
            $item,
            new SamedayServiceRules($this->samedayServiceRepository),
            new WooSamedayShippingHdAddressParser(),
        );
        $awbRecipient = $awbRecipientResolver->resolve();

        $request = new SamedayPostAwbRequest(
            $pickupPoint->getSamedayId(),
            null,
            new PackageType($item->getPackageType()),
            $item->getParcelsDimensions(),
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
            new AwbRecipientEntityObject(
                $awbRecipient->getRecipient()->getCity(),
                $awbRecipient->getRecipient()->getCounty(),
                $awbRecipient->getRecipient()->getAddress(),
                $awbRecipient->getRecipient()->getName(),
                $awbRecipient->getRecipient()->getPhone(),
                $awbRecipient->getRecipient()->getEmail(),
            ),
            $item->getInsuranceValue(),
            $item->getRepayment(),
            new CodCollectorType(CodCollectorType::CLIENT),
            null,
            $serviceTax->getServiceTaxIds(),
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
                AwbErrorParser::parse($errors),
                ResponseNoticeType::ERROR,
            );
        }

        $awbDetails = [
            'order_id' => $item->getOrderId(),
            'awb_number' => $awb->getAwbNumber(),
            'parcels' => serialize($awb->getParcels()),
            'awb_cost' => $awb->getCost(),
        ];

        try {
            $this->samedayAwbRepository->saveAwb($awbDetails);
        } catch (Throwable $exception) {
            return $this->rollbackRemoteAwb($awb->getAwbNumber());
        }

        $this->applyOrderChanges($item, $service, $awbRecipient);

        return new GenerateAwbResponse(
            "Awb generated successfully.",
            ResponseNoticeType::SUCCESS,
        );
    }

    /**
     * @param string $awbNumber
     *
     * @return GenerateAwbResponse
     */
    private function rollbackRemoteAwb(string $awbNumber): GenerateAwbResponse
    {
        try {
            (new AwbRemover($this->sameday, $this->samedayAwbRepository))->removeRemote($awbNumber);

            $message = 'The AWB was generated but could not be saved. So it has been cancelled, please try again.';
        } catch (Throwable $rollbackException) {
            $message = sprintf(
                'The AWB %s was generated but could not be saved, and the automatic cancellation failed. 
                Please remove it manually.',
                $awbNumber
            );
        }

        return new GenerateAwbResponse(
            $message,
            ResponseNoticeType::ERROR,
        );
    }

    /**
     * @param GenerateAwbItem $item
     * @param SamedayService $service
     * @param AwbGenerateRecipientResponse $awbRecipient
     *
     * @return void
     */
    private function applyOrderChanges(
        GenerateAwbItem $item,
        SamedayService $service,
        AwbGenerateRecipientResponse $awbRecipient
    ): void {
        $shippingLines = $item->getShippingLines();
        $samedayOrderItemId = array_key_first($shippingLines);
        $shippingLine = null !== $samedayOrderItemId ? $shippingLines[$samedayOrderItemId] : null;

        try {
            $recipient = $awbRecipient->getRecipient();
            $this->wooOrderShippingAddressUpdater->update(
                $item->getOrderId(),
                $recipient->getAddress1() ?? '',
                $recipient->getAddress2() ?? '',
                $recipient->getName() ?? '',
                $recipient->getCity() ?? '',
                WooStateCodeResolver::resolveFromName(
                    $recipient->getCountry() ?? '',
                    $recipient->getCounty() ?? ''
                ) ?: ($recipient->getCounty() ?? ''),
                $recipient->getPostcode() ?? '',
                $recipient->getCountry() ?? '',
            );
        } catch (Throwable $exception) {}

        if (null !== $shippingLine) {
            try {
                $shippingLine->update_meta_data('service_id', $service->getSamedayId());
                $shippingLine->update_meta_data('service_code', $service->getSamedayCode());
                $shippingLine->save_meta_data();

                $shippingLine->set_method_id(SamedayConstants::PLUGIN_NAME);
                $shippingLine->save();
            } catch (Throwable $exception) {}
        }

        if (null !== $samedayOrderItemId) {
            try {
                $this->dbHandler->updateRow(
                    $this->dbHandler->buildTableName('woocommerce_order_items'),
                    ['order_item_name' => $service->getName() ?? $service->getSamedayName() ?? ''],
                    ['order_item_id' => $samedayOrderItemId]
                );
            } catch (Throwable $exception) {}
        }
    }
}
