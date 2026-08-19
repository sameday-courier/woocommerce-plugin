<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use JsonException;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;

final class LockerDtoFactory
{
    private const REQUIRED_PAYLOAD_KEYS = [
        'lockerId',
        'name',
        'address',
        'city',
        'county',
    ];

    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @param ?SamedayLockerRepository $samedayLockerRepository
     */
    public function __construct(
        ?SamedayLockerRepository $samedayLockerRepository = null
    ) {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
    }

    /**
     * @param mixed $raw
     *
     * @return ?LockerDto
     */
    public function fromInput($raw): ?LockerDto
    {
        if (null === $raw || '' === $raw) {
            return null;
        }

        if (is_int($raw) || (is_string($raw) && ctype_digit($raw))) {
            return $this->fromLockerId((int) $raw);
        }

        if (is_string($raw)) {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return null;
            }

            return is_array($decoded) ? $this->fromPayload($decoded) : null;
        }

        if (is_array($raw)) {
            return $this->fromPayload($raw);
        }

        return null;
    }

    /**
     * @param int $lockerId
     *
     * @return LockerDto|null
     */
    private function fromLockerId(int $lockerId): ?LockerDto
    {
        $locker = $this->samedayLockerRepository->getLockerSameday($lockerId);
        if (null === $locker) {
            return null;
        }

        $dto = LockerDto::fromSamedayLocker($locker);

        return $this->isComplete($dto) ? $dto : null;
    }

    /**
     * @param array $data
     *
     * @return ?LockerDto
     */
    private function fromPayload(array $data): ?LockerDto
    {
        if ($this->hasRequiredPayloadFields($data)) {
            return LockerDto::fromArray($data);
        }

        if (isset($data['lockerId']) && '' !== $data['lockerId'] && null !== $data['lockerId']) {
            return $this->fromLockerId((int) $data['lockerId']);
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function hasRequiredPayloadFields(array $data): bool
    {
        foreach (self::REQUIRED_PAYLOAD_KEYS as $key) {
            if (!isset($data[$key]) || '' === $data[$key] || null === $data[$key]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param LockerDto $locker
     *
     * @return bool
     */
    private function isComplete(LockerDto $locker): bool
    {
        return null !== $locker->getLockerId()
            && null !== $locker->getName() && '' !== $locker->getName()
            && null !== $locker->getAddress() && '' !== $locker->getAddress()
            && null !== $locker->getCity() && '' !== $locker->getCity()
            && null !== $locker->getCounty() && '' !== $locker->getCounty();
    }
}
