<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\CarrierOptionalTaxDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayOptionalTaxMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\SerializedPayloadReader;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

/**
 * Resolves whether the open package option applies, shared by classic checkout and Checkout Blocks.
 */
final class OpenPackageAvailabilityProvider
{
    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @var SamedayOptionalTaxMapper $optionalTaxMapper
     */
    private SamedayOptionalTaxMapper $optionalTaxMapper;

    /**
     * @param SamedayServiceRepository|null $samedayServiceRepository
     * @param CarrierSettingsProviderInterface|null $carrierSettingsProvider
     * @param SamedayOptionalTaxMapper|null $optionalTaxMapper
     */
    public function __construct(
        ?SamedayServiceRepository $samedayServiceRepository = null,
        ?CarrierSettingsProviderInterface $carrierSettingsProvider = null,
        ?SamedayOptionalTaxMapper $optionalTaxMapper = null
    ) {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
        $this->carrierSettingsProvider = $carrierSettingsProvider ?? new CarrierSettingsServiceProvider();
        $this->optionalTaxMapper = $optionalTaxMapper ?? new SamedayOptionalTaxMapper();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->carrierSettingsProvider->get()->isOpenPackageStatusEnabled();
    }

    /**
     * @param string $serviceCode
     *
     * @return bool
     */
    public function isAvailableForServiceCode(string $serviceCode): bool
    {
        if ('' === $serviceCode || !$this->isEnabled()) {
            return false;
        }

        $service = $this->samedayServiceRepository->getServiceSamedayByCode($serviceCode);

        return null !== $service && null !== $this->findOpenPackageTaxId($service);
    }

    /**
     * Sameday service codes offering the open package tax, so the checkout can react
     * to shipping method changes without another round trip.
     *
     * @return string[]
     */
    public function supportedServiceCodes(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $serviceCodes = [];
        foreach ($this->samedayServiceRepository->getAvailableServices() as $service) {
            if (null !== $this->findOpenPackageTaxId($service)) {
                $serviceCodes[] = $service->getSamedayCode();
            }
        }

        return $serviceCodes;
    }

    /**
     * @param CarrierService $service
     *
     * @return int|null
     */
    private function findOpenPackageTaxId(CarrierService $service): ?int
    {
        foreach ($this->readOptionalTaxes($service->getServiceOptionalTaxes()) as $optionalTax) {
            if ($optionalTax->getCode() === CarrierConstants::OPEN_PACKAGE_OPTION_CODE) {
                return $optionalTax->getId();
            }
        }

        return null;
    }

    /**
     * @param string|null $serializedOptionalTaxes
     *
     * @return CarrierOptionalTaxDto[]
     */
    private function readOptionalTaxes(?string $serializedOptionalTaxes): array
    {
        if (null === $serializedOptionalTaxes || '' === $serializedOptionalTaxes) {
            return [];
        }

        $optionalTaxes = SerializedPayloadReader::readOptionalTaxes($serializedOptionalTaxes);
        $sdkOptionalTaxes = array_values(
            array_filter(
                $optionalTaxes,
                static function ($optionalTax): bool {
                    return $optionalTax instanceof OptionalTaxObject;
                }
            )
        );

        return $this->optionalTaxMapper->mapCollection($sdkOptionalTaxes);
    }
}
