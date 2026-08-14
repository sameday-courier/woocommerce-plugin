<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\OpenPackageOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
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
        if ('yes' !== $this->sessionHandler->get(CarrierSessionKeys::OPEN_PACKAGE)) {
            return;
        }

        PostMetaHandler::update(
            $orderId,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            1,
            true
        );

        $this->sessionHandler->set(CarrierSessionKeys::OPEN_PACKAGE, 'no');
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
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            true
        );
    }
}
