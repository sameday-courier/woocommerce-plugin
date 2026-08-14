<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\OpenPackageOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;

final class WooOpenPackageOrderDataHandler implements OpenPackageOrderDataHandlerInterface
{
    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(?SessionHandlerInterface $sessionHandler = null)
    {
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function saveFromSession(int $orderId): void
    {
        if ('yes' !== $this->sessionHandler->get(SamedaySessionKeys::OPEN_PACKAGE)) {
            return;
        }

        PostMetaHandler::update(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            1,
            true
        );

        $this->sessionHandler->set(SamedaySessionKeys::OPEN_PACKAGE, 'no');
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    public function isEnabled(int $orderId): bool
    {
        return '' !== PostMetaHandler::get(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            true
        );
    }
}
