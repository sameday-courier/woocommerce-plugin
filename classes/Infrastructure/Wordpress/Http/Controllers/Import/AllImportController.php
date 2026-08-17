<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import;

use Exception;
use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Factories\CityRequestFactory;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshService;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshServiceRequest;
use SamedayCourier\Shipping\Domain\AllImportSteps;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class AllImportController extends AbstractRecursiveBulkController
{
    /**
     * @var string
     */
    private const ACTION = 'all-import-next';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @return array{status: string, message: string, label: string}
     */
    protected function processItem(int $itemId): array
    {
        $step = $this->getStep($itemId);
        if (null === $step) {
            return [
                'status' => ResponseNoticeType::ERROR,
                'message' => TranslatorHandler::translate('Unknown import step.'),
                'label' => (string) $itemId,
            ];
        }

        try {
            $result = ($step['execute'])();
        } catch (Exception $exception) {
            return [
                'status' => ResponseNoticeType::ERROR,
                'message' => TranslatorHandler::translate($exception->getMessage()),
                'label' => $step['label'],
            ];
        }

        return [
            'status' => $result->getNoticeType(),
            'message' => TranslatorHandler::translate($result->getNoticeMessage() ?? ''),
            'label' => $step['label'],
        ];
    }

    /**
     * @return array{label: string, execute: callable(): ResponseInterface}|null
     */
    private function getStep(int $itemId): ?array
    {
        $steps = [
            AllImportSteps::SERVICES => [
                'label' => TranslatorHandler::translate('Services'),
                'execute' => function (): ResponseInterface {
                    return (new RefreshService(
                        new RefreshServiceRequest(
                            new CourierServiceProvider(),
                            new SamedayServiceRepository()
                        )
                    ))->execute();
                },
            ],
            AllImportSteps::PICKUP_POINTS => [
                'label' => TranslatorHandler::translate('Pickup points'),
                'execute' => function (): ResponseInterface {
                    return (new RefreshPickupPoint(
                        new RefreshPickupPointRequest(
                            new CourierServiceProvider(),
                            new SamedayPickupPointRepository()
                        )
                    ))->execute();
                },
            ],
            AllImportSteps::LOCKERS => [
                'label' => TranslatorHandler::translate('Lockers'),
                'execute' => function (): ResponseInterface {
                    return (new RefreshLocker(
                        new RefreshLockerRequest(
                            new SamedayLockerRepository(),
                            new CourierServiceProvider(),
                            new CarrierSettingsServiceProvider()
                        )
                    ))->execute();
                },
            ],
            AllImportSteps::CITIES => [
                'label' => TranslatorHandler::translate('Cities'),
                'execute' => static function (): ResponseInterface {
                    return (new RefreshCity(
                        (new CityRequestFactory())->create()
                    ))->execute();
                },
            ],
        ];

        return $steps[$itemId] ?? null;
    }
}
