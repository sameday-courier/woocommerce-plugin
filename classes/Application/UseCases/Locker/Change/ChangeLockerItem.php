<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class ChangeLockerItem implements ItemInterface
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var mixed $locker
     */
    private $locker;

    /**
     * @param int $orderId
     * @param mixed $locker
     */
    public function __construct(int $orderId, $locker)
    {
        $this->orderId = $orderId;
        $this->locker = $locker;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        return new self(
            isset($inputParams['orderId']) ? (int) $inputParams['orderId'] : 0,
            $inputParams['locker'] ?? null,
        );
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return mixed
     */
    public function getLocker()
    {
        return $this->locker;
    }
}
