<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import;

use Exception;
use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshServiceRequest;
use SamedayCourier\Shipping\Domain\AllImportSteps;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshCityFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshLockerFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshPickupPointFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshServiceFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

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
     * @param int $itemId
     *
     * @return array
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
            'status' => $result->hasError()
                ? ResponseNoticeType::ERROR
                : ResponseNoticeType::SUCCESS,
            'message' => TranslatorHandler::translate($result->getNoticeMessage()),
            'label' => $step['label'],
        ];
    }

    /**
     * @param int $itemId
     *
     * @return array|null
     */
    private function getStep(int $itemId): ?array
    {
        $steps = [
            AllImportSteps::SERVICES => [
                'label' => TranslatorHandler::translate('Services'),
                'execute' => function (): ResponseInterface {
                    $refreshService = RefreshServiceFactory::create();

                    return $refreshService->execute(new RefreshServiceRequest());
                },
            ],
            AllImportSteps::PICKUP_POINTS => [
                'label' => TranslatorHandler::translate('Pickup points'),
                'execute' => function (): ResponseInterface {
                    $refreshPickupPoint = RefreshPickupPointFactory::create();

                    return $refreshPickupPoint->execute(new RefreshPickupPointRequest());
                },
            ],
            AllImportSteps::LOCKERS => [
                'label' => TranslatorHandler::translate('Lockers'),
                'execute' => function (): ResponseInterface {
                    $refreshLocker = RefreshLockerFactory::create();

                    return $refreshLocker->execute(new RefreshLockerRequest());
                },
            ],
            AllImportSteps::CITIES => [
                'label' => TranslatorHandler::translate('Cities'),
                'execute' => static function (): ResponseInterface {
                    $refreshCity = RefreshCityFactory::create();

                    return $refreshCity->execute(new RefreshCityRequest());
                },
            ],
        ];

        return $steps[$itemId] ?? null;
    }
}
