<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

use DateTime;
use Sameday\Objects\ParcelStatusHistory\HistoryObject as ParcelHistoryObject;
use Sameday\Objects\ParcelStatusHistory\SummaryObject as ParcelSummaryObject;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Objects\PostAwb\ParcelObject as PostAwbParcelObject;
use Sameday\Objects\Service\OptionalTaxObject;
use Sameday\Objects\Types\CostType;
use Sameday\Objects\Types\PackageType;

final class SerializedPayloadReader
{
    /**
     * @param string $serialized
     *
     * @return PostAwbParcelObject[]
     */
    public static function readPostAwbParcels(string $serialized): array
    {
        return self::readObjectList($serialized, [PostAwbParcelObject::class]);
    }

    /**
     * @param string $serialized
     *
     * @return PickupPointContactPersonObject[]
     */
    public static function readPickupPointContactPersons(string $serialized): array
    {
        return self::readObjectList($serialized, [PickupPointContactPersonObject::class]);
    }

    /**
     * @param string $serialized
     *
     * @return OptionalTaxObject[]
     */
    public static function readOptionalTaxes(string $serialized): array
    {
        return self::readObjectList(
            $serialized,
            [
                OptionalTaxObject::class,
                PackageType::class,
                CostType::class,
            ]
        );
    }

    /**
     * @param string $serialized
     *
     * @return ParcelSummaryObject|null
     */
    public static function readParcelStatusSummary(string $serialized): ?ParcelSummaryObject
    {
        if ('' === $serialized) {
            return null;
        }

        $summary = unserialize(
            $serialized,
            [
                'allowed_classes' => [
                    ParcelSummaryObject::class,
                    DateTime::class,
                ],
            ]
        );

        return $summary instanceof ParcelSummaryObject ? $summary : null;
    }

    /**
     * @param string $serialized
     *
     * @return ParcelHistoryObject[]
     */
    public static function readParcelStatusHistory(string $serialized): array
    {
        return self::readObjectList(
            $serialized,
            [
                ParcelHistoryObject::class,
                DateTime::class,
            ]
        );
    }

    /**
     * @param string $serialized
     * @param class-string[] $allowedClasses
     *
     * @return array<int, object>
     */
    private static function readObjectList(string $serialized, array $allowedClasses): array
    {
        if ('' === $serialized) {
            return [];
        }

        $decoded = unserialize(
            $serialized,
            [
                'allowed_classes' => $allowedClasses,
            ]
        );

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(
            array_filter(
                $decoded,
                static function ($item): bool {
                    return is_object($item);
                }
            )
        );
    }
}
