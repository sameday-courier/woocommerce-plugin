<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use JsonException;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\LockerDtoRequest;
use SamedayCourier\Shipping\Domain\Ports\LockerServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerServiceProvider;

final class LockerDtoFactory
{
    private const REQUIRED_PAYLOAD_KEYS = [
        'lockerId',
        'name',
        'address',
        'city',
        'county',
    ];

    private const OPTIONAL_PAYLOAD_KEYS = [
        'oohType',
        'postalCode',
    ];

    private LockerServiceProviderInterface $lockerServiceProvider;

    /**
     * @param ?LockerServiceProviderInterface $lockerServiceProvider
     */
    public function __construct(
        ?LockerServiceProviderInterface $lockerServiceProvider = null
    ) {
        $this->lockerServiceProvider = $lockerServiceProvider ?? new LockerServiceProvider();
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
        $locker = $this->lockerServiceProvider
            ->getLocker(new LockerDtoRequest($lockerId))
            ->getLocker();

        if (null === $locker) {
            return null;
        }

        return $this->isComplete($locker) ? $locker : null;
    }

    /**
     * @param array $data
     *
     * @return ?LockerDto
     */
    private function fromPayload(array $data): ?LockerDto
    {
        if ($this->hasRequiredPayloadFields($data)) {
            $mapped = $this->mapLockerInput($data);

            return new LockerDto(
                $mapped['lockerId'],
                $mapped['oohType'],
                $mapped['name'],
                $mapped['county'],
                $mapped['city'],
                $mapped['address'],
                $mapped['postalCode']
            );
        }

        if (isset($data['lockerId']) && '' !== $data['lockerId']) {
            return $this->fromLockerId((int) $data['lockerId']);
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getLockerKeys(): array
    {
        return array_merge(self::REQUIRED_PAYLOAD_KEYS, self::OPTIONAL_PAYLOAD_KEYS);
    }

    /**
     * @param array $data
     *
     * @return array{
     *     lockerId: ?int,
     *     oohType: ?string,
     *     name: ?string,
     *     county: ?string,
     *     city: ?string,
     *     address: ?string,
     *     postalCode: ?string
     * }
     */
    private function mapLockerInput(array $data): array
    {
        $mapped = [];

        foreach ($this->getLockerKeys() as $key) {
            if (!isset($data[$key]) || '' === $data[$key]) {
                $mapped[$key] = null;
                continue;
            }

            $mapped[$key] = 'lockerId' === $key
                ? (int) $data[$key]
                : (string) $data[$key];
        }

        return $mapped;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function hasRequiredPayloadFields(array $data): bool
    {
        foreach (self::REQUIRED_PAYLOAD_KEYS as $key) {
            if (!isset($data[$key]) || '' === $data[$key]) {
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
        return $this->hasRequiredPayloadFields($locker->toArray());
    }
}
