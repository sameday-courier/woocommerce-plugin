<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Exception;
use JsonException;
use Throwable;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Sameday;
use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

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
     * @var OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     */
    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @var ParcelDimensionsObject[] $parcelsDimensions
     */
    private array $parcelsDimensions;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @var ShippingDtoFactory $shippingDtoFactory
     */
    private ShippingDtoFactory $shippingDtoFactory;

    /**
     * @var BillingDtoFactory $billingDtoFactory
     */
    private BillingDtoFactory $billingDtoFactory;

    /**
     * @var GenerateAwbValidator $generateAwbValidator
     */
    private GenerateAwbValidator $generateAwbValidator;

    /**
     * @var AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver
     */
    private AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver;

    /**
     * @var AwbGenerateRecipientResolver $awbGenerateRecipientResolver
     */
    private AwbGenerateRecipientResolver $awbGenerateRecipientResolver;

    /**
     * @var SamedayServiceRules $samedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * @var AwbRemover $awbRemover
     */
    private AwbRemover $awbRemover;

    /**
     * @var OrderAwbProviderInterface $orderAwbProvider
     */
    private OrderAwbProviderInterface $orderAwbProvider;

    /**
     * @param GenerateAwbRequest $generateAwbRequest
     */
    public function __construct(
        GenerateAwbRequest $generateAwbRequest
    )
    {
        $this->awbItem = $generateAwbRequest->getGenerateAwbItem();
        $this->sameday = $generateAwbRequest->getSameday();
        $this->dbHandler = $generateAwbRequest->getDbHandler();
        $this->samedayServiceRepository = $generateAwbRequest->getSamedayServiceRepository();
        $this->samedayPickupPointRepository = $generateAwbRequest->getSamedayPickupPointRepository();
        $this->samedayAwbRepository = $generateAwbRequest->getSamedayAwbRepository();
        $this->orderShippingAddressUpdater = $generateAwbRequest->getOrderShippingAddressUpdater();
        $this->awbErrorParser = $generateAwbRequest->getAwbErrorParser();
        $this->parcelsDimensions = $generateAwbRequest->getParcelsDimensions();
        $this->lockerDtoFactory = $generateAwbRequest->getLockerDtoFactory();
        $this->shippingDtoFactory = $generateAwbRequest->getShippingDtoFactory();
        $this->billingDtoFactory = $generateAwbRequest->getBillingDtoFactory();
        $this->generateAwbValidator = $generateAwbRequest->getGenerateAwbValidator();
        $this->awbGenerateServiceTaxResolver = $generateAwbRequest->getAwbGenerateServiceTaxResolver();
        $this->awbGenerateRecipientResolver = $generateAwbRequest->getAwbGenerateRecipientResolver();
        $this->samedayServiceRules = $generateAwbRequest->getSamedayServiceRules();
        $this->awbRemover = $generateAwbRequest->getAwbRemover();
        $this->orderAwbProvider = $generateAwbRequest->getOrderAwbProvider();
    }

    /**
     * @return GenerateAwbResponse
     * @throws JsonException
     */
    public function execute(): GenerateAwbResponse
    {
        $item = $this->awbItem;

        $service = $this->samedayServiceRepository->getServiceSameday($item->getServiceId());
        $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday($item->getPickupPointId());
        $shipping = $this->shippingDtoFactory->fromInput($item->getShipping());
        $billing = $this->billingDtoFactory->fromInput($item->getBilling());
        $locker = $this->lockerDtoFactory->fromInput($item->getLocker());

        $awbValidator = $this->generateAwbValidator->validate(
            new GenerateAwbValidatorRequest(
                $item->getOrderId(),
                $service,
                $pickupPoint,
                $billing,
                $item->getShippingLines(),
                null !== $this->orderAwbProvider->get($item->getOrderId()),
                [] !== $this->parcelsDimensions,
            )
        );

        if ($awbValidator->hasErrors()) {
            return new GenerateAwbResponse(
                $awbValidator->toString(),
                ResponseNoticeType::ERROR
            );
        }

        $serviceTax = $this->awbGenerateServiceTaxResolver->resolve($service, $item);

        $awbRecipient = $this->awbGenerateRecipientResolver->resolve(
            $item->getOrderId(),
            $shipping,
            $billing,
            $service,
            $locker,
        );

        $recipient = $awbRecipient->getRecipient();

        $request = new SamedayPostAwbRequest(
            $pickupPoint->getSamedayId(),
            null,
            new PackageType($item->getPackageType()),
            $this->parcelsDimensions,
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
            new AwbRecipientEntityObject(
                $recipient->getCity(),
                $recipient->getCounty(),
                $recipient->getAddress(),
                $recipient->getName(),
                $recipient->getPhone(),
                $recipient->getEmail(),
                $recipient->getCompany(),
                $recipient->getPostcode(),
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
                $this->awbErrorParser->parse($errors),
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

        $this->applyOrderChanges($item, $service);

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
            $this->awbRemover->removeRemote($awbNumber);

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
     *
     * @return void
     */
    private function applyOrderChanges(
        GenerateAwbItem $item,
        SamedayService $service
    ): void {
        $shippingLines = $item->getShippingLines();
        $samedayOrderItemId = array_key_first($shippingLines);
        $shippingLine = null !== $samedayOrderItemId ? $shippingLines[$samedayOrderItemId] : null;

        try {
            if ($this->samedayServiceRules->isOohDeliveryOption($service)) {
                $this->orderShippingAddressUpdater->activateOutOfHome($item->getOrderId());
            } else {
                $this->orderShippingAddressUpdater->activateHomeDelivery($item->getOrderId());
            }
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
