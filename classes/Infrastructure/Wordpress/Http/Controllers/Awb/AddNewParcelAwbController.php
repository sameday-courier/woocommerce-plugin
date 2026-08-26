<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\AddNewParcelAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\AddNewParcelAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

final class AddNewParcelAwbController extends AbstractController
{
    private const ACTION = "add-new-parcel";

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
        $params = new AddNewParcelAwbMapper($inputParams);
        $orderId = $params->orderId();

        $addNewParcelAwb = AddNewParcelAwbFactory::create();

        try {
            $result = $addNewParcelAwb->execute(
                new AddNewParcelAwbRequest(
                    $params->orderId(),
                    $params->parcelWeight(),
                    $params->parcelWidth(),
                    $params->parcelLength(),
                    $params->parcelHeight(),
                    $params->parcelObservation(),
                    $params->parcelIsLast()
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
                    'add-new-parcel' => ResponseNoticeType::ERROR,
                ]
            );
        }

        $noticeType = $result->hasError()
            ? ResponseNoticeType::ERROR
            : ResponseNoticeType::SUCCESS;

        if ('' !== $result->getNoticeMessage()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $noticeType,
            );
        }

        $this->redirectTo(
            'post.php',
            [
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'add-new-parcel' => $noticeType,
            ]
        );
    }
}
