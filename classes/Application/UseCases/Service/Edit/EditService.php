<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;

final class EditService
{
    /**
     * @var EditServiceRequest $editServiceRequest
     */
    private EditServiceRequest $editServiceRequest;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param EditServiceRequest $editServiceRequest
     */
    public function __construct(EditServiceRequest $editServiceRequest)
    {
        $this->editServiceRequest = $editServiceRequest;
        $this->samedayServiceRepository = $editServiceRequest->getSamedayServiceRepository();
    }

    /**
     * @return EditServiceResponse
     */
    public function execute(): EditServiceResponse
    {
        $editServiceItem = $this->editServiceRequest->getEditServiceItem();
        $serviceId = $editServiceItem->getId();

        $postFields = [
            'id' => [
                'required' => true,
                'value' => (string) $serviceId,
            ],
            'name' => [
                'required' => true,
                'value' => $editServiceItem->getName(),
            ],
            'price' => [
                'required' => true,
                'value' => $editServiceItem->getPrice(),
            ],
            'price_free' => [
                'required' => false,
                'value' => $editServiceItem->getPriceFree(),
            ],
            'status' => [
                'required' => false,
                'value' => $editServiceItem->getStatus(),
            ],
        ];

        $errors = [];

        foreach ($postFields as $field => $fieldValue) {
            if ($fieldValue['required'] && ('' === trim((string) $fieldValue['value']))) {
                $errors[] = "The $field must not be empty";
            }
        }

        if (!empty($errors)) {
            return new EditServiceResponse(
                $serviceId,
                implode(' ', $errors),
                ResponseNoticeType::ERROR,
            );
        }

        $priceFree = null;
        if ((float) $postFields['price_free']['value'] > 0) {
            $priceFree = (float) $postFields['price_free']['value'];
        }

        $currentService = $this->samedayServiceRepository->getServiceById($serviceId);
        if (null === $currentService) {
            return new EditServiceResponse(
                $serviceId,
                "Unable to find service $serviceId",
                ResponseNoticeType::ERROR,
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
            return new EditServiceResponse(
                $serviceId,
                'Unable to update service',
                ResponseNoticeType::ERROR,
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
                return new EditServiceResponse(
                    $serviceId,
                    'Service updated, but unable to sync PUDO status',
                    ResponseNoticeType::ERROR,
                );
            }
        }

        return new EditServiceResponse(
            $serviceId,
            "Service has been edited",
            ResponseNoticeType::SUCCESS,
        );
    }
}
