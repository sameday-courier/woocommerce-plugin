<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

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
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    protected function processAction(array $inputParams): void
    {
        $removeAwbItem = RemoveAwbItem::fromArray($inputParams);

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectToOrderEdit($removeAwbItem->getOrderId());

            return;
        }

        $samedayAwbRepository = new SamedayAwbRepository();

        $removeAwb = new RemoveAwb(
            new RemoveAwbRequest(
                $removeAwbItem,
                new AwbRemover(
                    $samedayApiClient,
                    $samedayAwbRepository
                ),
                new AwbErrorParser()
            )
        );

        $result = $removeAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        $this->redirectToOrderEdit($removeAwbItem->getOrderId());
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    private function redirectToOrderEdit(int $orderId): void
    {
        $this->redirectTo(
            'post.php',
            [
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
