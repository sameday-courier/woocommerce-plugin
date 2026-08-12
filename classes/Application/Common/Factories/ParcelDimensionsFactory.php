<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use Sameday\Objects\ParcelDimensionsObject;

if (!defined('ABSPATH')) {
    exit;
}

final class ParcelDimensionsFactory
{
    /**
     * @param mixed $weight
     * @param mixed $width
     * @param mixed $length
     * @param mixed $height
     */
    public function fromAttributes(
        $weight,
        $width = null,
        $length = null,
        $height = null
    ): ParcelDimensionsObject {
        return new ParcelDimensionsObject(
            (float) $weight,
            $this->optionalDimension($width),
            $this->optionalDimension($length),
            $this->optionalDimension($height),
        );
    }

    /**
     * @param array<int|string, mixed> $parcels
     *
     * @return ParcelDimensionsObject[]
     */
    public function fromList(array $parcels): array
    {
        ksort($parcels);

        $result = [];
        foreach ($parcels as $parcel) {
            if (!is_array($parcel)) {
                continue;
            }

            $result[] = $this->fromAttributes(
                $parcel['weight'] ?? 0,
                $parcel['width'] ?? null,
                $parcel['length'] ?? null,
                $parcel['height'] ?? null,
            );
        }

        return $result;
    }

    /**
     * @param mixed $value
     */
    private function optionalDimension($value): ?float
    {
        if ('' === $value || !is_numeric($value)) {
            return null;
        }

        $value = (float) $value;

        return $value > 0 ? $value : null;
    }
}
