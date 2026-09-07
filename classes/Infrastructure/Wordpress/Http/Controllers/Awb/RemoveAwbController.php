<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RemoveAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\RemoveAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

final class RemoveAwbController extends AbstractController
{
    private const ACTION = 'remove-awb';

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
        $params = new RemoveAwbMapper($inputParams);
        $orderId = $params->orderId();
        $removeAwb = RemoveAwbFactory::create();

        try {
            $result = $removeAwb->execute(
                new RemoveAwbRequest($orderId)
            );
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectTo();
        }

        NoticerHandler::addFlashNotice(
            TranslatorHandler::translate($result->getNoticeMessage()),
            $result->hasError()
                ? ResponseNoticeType::ERROR
                : ResponseNoticeType::SUCCESS,
        );

        $this->redirectTo();
    }
}
