<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLockerItem;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLockerRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\ChangeLockerServiceProvider;

final class ChangeLockerController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'change_locker';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $changeLockerItem = ChangeLockerItem::fromArray($inputParams);

        $result = (new ChangeLocker(
            new ChangeLockerRequest(
                $changeLockerItem,
                new ChangeLockerServiceProvider(),
            )
        ))->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate($result->getNoticeMessage() ?? 'Failed to change locker.')
            );
        }

        $this->sendJsonSuccessResponse(null);
    }
}
