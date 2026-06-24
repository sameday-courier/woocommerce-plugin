<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

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
        $this->samedayServiceRepository = new SamedayServiceRepository();
    }

    /**
     * @return EditServiceResponse
     */
    public function execute(): EditServiceResponse
    {
        $serviceId = $this->editServiceRequest->getId();

        $postFields = [
            'id' => [
                'required' => true,
                'value' => (string) $serviceId,
            ],
            'name' => [
                'required' => true,
                'value' => $this->editServiceRequest->getName(),
            ],
            'price' => [
                'required' => true,
                'value' => $this->editServiceRequest->getPrice(),
            ],
            'price_free' => [
                'required' => false,
                'value' => $this->editServiceRequest->getPriceFree(),
            ],
            'status' => [
                'required' => false,
                'value' => $this->editServiceRequest->getStatus(),
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

        $currentService = $this->samedayServiceRepository->getService($serviceId);
        if (null === $currentService) {
            return new EditServiceResponse(
                $serviceId,
                "Unable to find service $serviceId",
                ResponseNoticeType::ERROR,
            );
        }

        $service = [
            'id' => $serviceId,
            'name' => Helper::sanitizeInput($postFields['name']['value']),
            'price' => (float) $postFields['price']['value'],
            'price_free' => $priceFree,
            'status' => (int) $postFields['status']['value'],
        ];

        $this->samedayServiceRepository->updateService($service);

        if ($currentService->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
            $pudoService = $this->samedayServiceRepository->getServiceSamedayByCode(
                SamedayConstants::PUDO_CODE
            );

            if (null !== $pudoService) {
                $this->samedayServiceRepository->updateService(
                    [
                        'id' => $pudoService->getId(),
                        'status' => $service['status'],
                    ]
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
