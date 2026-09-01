<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;

/**
 * Persists the selected EasyBox / locker into the active WooCommerce session.
 *
 * Stores only the compact LockerDto shape (never the raw SDK object) so session writes stay
 * small and deterministic for both classic admin-ajax and Blocks Store API callbacks.
 */
final class LockerSessionStore
{
    /**
     * @var SessionHandlerInterface
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @var LockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param SessionHandlerInterface|null $sessionHandler
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(
        ?SessionHandlerInterface $sessionHandler = null,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
    }

    /**
     * @param mixed $locker
     *
     * @return void
     */
    public function store($locker): void
    {
        if (null === $locker || '' === $locker) {
            return;
        }

        if (is_array($locker) && empty($locker['lockerId']) && isset($locker['id'])) {
            $locker['lockerId'] = $locker['id'];
        }

        $dto = $this->lockerDtoFactory->fromInput($locker);
        if (null === $dto) {
            return;
        }

        try {
            $this->sessionHandler->set(
                CarrierSessionKeys::LOCKER,
                json_encode($dto->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
            );
        } catch (JsonException $exception) {
            return;
        }
    }
}
