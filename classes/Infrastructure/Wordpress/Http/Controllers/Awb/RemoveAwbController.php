<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
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
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    public function __construct()
    {
        $this->samedayAwbRepository = new SamedayAwbRepository();
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
        $orderId = (int) $inputParams['order-id'];
        if (null === $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId)
        ) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("Unable to remove awb for order $orderId"),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        $removeAwb = new RemoveAwb(new RemoveAwbRequest($awb, new AwbErrorParser()));

        $result = $removeAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'post.php',
            [
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
