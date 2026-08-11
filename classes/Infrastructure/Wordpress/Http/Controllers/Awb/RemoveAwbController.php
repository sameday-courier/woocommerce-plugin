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
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

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

            $this->redirectTo($removeAwbItem->getOrderId());

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

        $this->redirectTo($removeAwbItem->getOrderId());
    }

    /**
     * @param  int $orderId
     *
     * @return void
     */
    private function redirectTo(int $orderId): void
    {
        Redirector::to(
            'post.php',
            [
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
