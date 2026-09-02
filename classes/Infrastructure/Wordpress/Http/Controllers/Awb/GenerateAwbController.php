<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\GenerateAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\GenerateAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingHandler;

final class GenerateAwbController extends AbstractController
{
    private const ACTION = "add-awb";

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
        $params = new GenerateAwbMapper($inputParams);
        $orderId = $params->orderId();
        $orderData = (new WooShippingHandler())->getShippingOrderById($orderId);

        if (empty($orderData)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("There is no data to process."),
            );

            $this->redirectTo(
                'post.php',
                [
                    'id' => $orderId,
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        $mapper = new GenerateAwbMapper(array_merge($inputParams, $orderData));
        $generateAwb = GenerateAwbFactory::create();
        $result = null;

        try {
            $result = $generateAwb->execute(
                new GenerateAwbRequest(
                    $mapper->orderId(),
                    $mapper->serviceId(),
                    $mapper->pickupPointId(),
                    $mapper->shippingLines(),
                    $mapper->shipping(),
                    $mapper->billing(),
                    $mapper->locker(),
                    $mapper->hasOpenPackage(),
                    $mapper->hasLockerFirstMile(),
                    $mapper->packageType(),
                    $mapper->awbPayment(),
                    $mapper->insuranceValue(),
                    $mapper->repayment(),
                    $mapper->clientReference(),
                    $mapper->observation(),
                    $mapper->packageDimensions()
                )
            );
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectTo(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-awb' => ResponseNoticeType::ERROR,
                ]
            );
        }

        if (null === $result) {
            return;
        }

        if ('' !== $result->getNoticeMessage()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->hasError()
                    ? ResponseNoticeType::ERROR
                    : ResponseNoticeType::SUCCESS,
            );
        }

        $this->redirectTo(
            'post.php',
            [
                'id' => $orderId,
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
