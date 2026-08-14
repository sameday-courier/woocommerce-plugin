<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class GenerateAwb
{
    /**
     * @var GenerateAwbItem $awbItem
     */
    private GenerateAwbItem $awbItem;

    /**
     * @var CourierServiceProviderInterface $courier
     */
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider
     */
    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

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
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

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
        $this->courier = $generateAwbRequest->getCourier();
        $this->samedayServiceRepository = $generateAwbRequest->getSamedayServiceRepository();
        $this->samedayPickupPointRepository = $generateAwbRequest->getSamedayPickupPointRepository();
        $this->postAwbGenerationServiceProvider = $generateAwbRequest->getPostAwbGenerationServiceProvider();
        $this->parcelsDimensions = $generateAwbRequest->getParcelsDimensions();
        $this->lockerDtoFactory = $generateAwbRequest->getLockerDtoFactory();
        $this->shippingDtoFactory = $generateAwbRequest->getShippingDtoFactory();
        $this->billingDtoFactory = $generateAwbRequest->getBillingDtoFactory();
        $this->generateAwbValidator = $generateAwbRequest->getGenerateAwbValidator();
        $this->awbGenerateServiceTaxResolver = $generateAwbRequest->getAwbGenerateServiceTaxResolver();
        $this->awbGenerateRecipientResolver = $generateAwbRequest->getAwbGenerateRecipientResolver();
        $this->carrierServiceRules = $generateAwbRequest->getCarrierServiceRules();
        $this->orderAwbProvider = $generateAwbRequest->getOrderAwbProvider();
    }

    /**
     * @return GenerateAwbResponse
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

        $serviceTax = $this->awbGenerateServiceTaxResolver->resolve(
            $service,
            $item->hasOpenPackage(),
            $item->hasLockerFirstMile(),
            $item->getPackageType()
        );

        $awbRecipient = $this->awbGenerateRecipientResolver->resolve(
            $item->getOrderId(),
            $shipping,
            $billing,
            $service,
            $locker,
        );

        $recipient = $awbRecipient->getRecipient();

        $request = new PostAwbRequestDto(
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

        try {
            $awb = $this->courier->postAwb($request);
        } catch (CourierServiceException $exception) {
            return new GenerateAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        $postAwbGenerationResponse = $this->postAwbGenerationServiceProvider->apply(
            new PostAwbGenerationRequestDto(
                $item->getOrderId(),
                $item->getShippingLines(),
                $service,
                $awb->getAwbNumber(),
                $awb->getCost(),
                $awb->getParcels()
            ),
            $this->carrierServiceRules,
            $this->courier
        );

        return new GenerateAwbResponse(
            $postAwbGenerationResponse->getMessage(),
            $postAwbGenerationResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
