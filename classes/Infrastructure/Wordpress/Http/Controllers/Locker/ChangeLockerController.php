<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\ChangeLockerFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\ChangeLockerRequestFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\ChangeLockerMapper;

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
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $params = new ChangeLockerMapper($inputParams);
        $changeLocker = ChangeLockerFactory::create();

        $result = $changeLocker->execute(
            ChangeLockerRequestFactory::create()->fromMapper($params)
        );

        if ($result->hasError()) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate($result->getNoticeMessage())
            );
        }

        $this->sendJsonSuccessResponse(null);
    }
}
