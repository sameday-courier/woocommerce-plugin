<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\SamedayLocker;

final class SamedayLockerMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayLocker
     */
    public function map(array $row): SamedayLocker
    {
        $locker = new SamedayLocker();

        $locker->setId(isset($row['id']) ? (int) $row['id'] : 0);
        $locker->setLockerId(
            isset($row['locker_id']) && $row['locker_id'] !== '' && $row['locker_id'] !== null
                ? (int) $row['locker_id']
                : null
        );
        $locker->setName($row["name"] ?? null);
        $locker->setCounty($row["county"] ?? null);
        $locker->setCity($row["city"] ?? null);
        $locker->setAddress($row["address"] ?? null);
        $locker->setLat($row["lat"] ?? null);
        $locker->setLng($row["lng"] ?? null);
        $locker->setPostalCode($row["postal_code"] ?? null);
        $locker->setBoxes($row["boxes"] ?? null);
        $locker->setIsTesting(isset($row["is_testing"]) ? ((int) $row["is_testing"] !== 0) : null);

        return $locker;
    }
}
