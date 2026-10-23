<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\LockerDtoFactoryFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostOrderMetaHandler;
use WC_Order;

final class WooLockerOrderDataHandler implements LockerOrderDataHandlerInterface
{
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(?LockerDtoFactory $lockerDtoFactory = null)
    {
        $this->lockerDtoFactory = $lockerDtoFactory ?? LockerDtoFactoryFactory::create();
    }

    /**
     * @param int $orderId
     * @param mixed $locker
     *
     * @return void
     */
    public function add(int $orderId, $locker): void
    {
        $lockerDto = $this->lockerDtoFactory->fromInput($locker);
        if (null === $lockerDto) {
            return;
        }

        try {
            $locker = json_encode($lockerDto->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        PostOrderMetaHandler::update(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
            $locker
        );
    }
}
