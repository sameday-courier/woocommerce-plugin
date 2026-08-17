<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\Requests\EditServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\EditServiceResponseDto;
use SamedayCourier\Shipping\Domain\Ports\EditServiceServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class EditServiceServiceProvider implements EditServiceServiceProviderInterface
{
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(
        ?SamedayServiceRepository $samedayServiceRepository = null
    ) {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
    }

    /**
     * @param EditServiceRequestDto $editServiceRequestDto
     *
     * @return EditServiceResponseDto
     */
    public function edit(EditServiceRequestDto $editServiceRequestDto): EditServiceResponseDto
    {
        $serviceId = $editServiceRequestDto->getId();

        $postFields = [
            'id' => [
                'required' => true,
                'value' => (string) $serviceId,
            ],
            'name' => [
                'required' => true,
                'value' => $editServiceRequestDto->getName(),
            ],
            'price' => [
                'required' => true,
                'value' => $editServiceRequestDto->getPrice(),
            ],
            'price_free' => [
                'required' => false,
                'value' => $editServiceRequestDto->getPriceFree(),
            ],
            'status' => [
                'required' => false,
                'value' => $editServiceRequestDto->getStatus(),
            ],
        ];

        $errors = [];

        foreach ($postFields as $field => $fieldValue) {
            if ($fieldValue['required'] && ('' === trim((string) $fieldValue['value']))) {
                $errors[] = "The $field must not be empty";
            }
        }

        if (!empty($errors)) {
            return new EditServiceResponseDto(
                $serviceId,
                false,
                implode(' ', $errors)
            );
        }

        $priceFree = null;
        if ((float) $postFields['price_free']['value'] > 0) {
            $priceFree = (float) $postFields['price_free']['value'];
        }

        $currentService = $this->samedayServiceRepository->getServiceById($serviceId);
        if (null === $currentService) {
            return new EditServiceResponseDto(
                $serviceId,
                false,
                "Unable to find service $serviceId"
            );
        }

        $service = [
            'id' => $serviceId,
            'name' => InputSanitizer::sanitizeInput($postFields['name']['value']),
            'price' => (float) $postFields['price']['value'],
            'price_free' => $priceFree,
            'status' => (int) $postFields['status']['value'],
        ];

        if (!$this->samedayServiceRepository->updateService($service)) {
            return new EditServiceResponseDto(
                $serviceId,
                false,
                'Unable to update service'
            );
        }

        if ($currentService->getSamedayCode() === CarrierConstants::LOCKER_NEXT_DAY_CODE) {
            $pudoService = $this->samedayServiceRepository->getServiceSamedayByCode(
                CarrierConstants::PUDO_CODE
            );

            if (null !== $pudoService
                && !$this->samedayServiceRepository->updateService(
                    [
                        'id' => $pudoService->getId(),
                        'status' => $service['status'],
                    ]
                )
            ) {
                return new EditServiceResponseDto(
                    $serviceId,
                    false,
                    'Service updated, but unable to sync PUDO status'
                );
            }
        }

        return new EditServiceResponseDto(
            $serviceId,
            true,
            "Service has been edited"
        );
    }
}
