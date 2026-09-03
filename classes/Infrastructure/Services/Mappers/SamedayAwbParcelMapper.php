<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\DTOs\CarrierAwbParcelDto;

final class SamedayAwbParcelMapper
{
    /**
     * @param ParcelObject $parcel
     *
     * @return CarrierAwbParcelDto
     */
    public function map(ParcelObject $parcel): CarrierAwbParcelDto
    {
        return new CarrierAwbParcelDto(
            $parcel->getPosition(),
            $parcel->getAwbNumber()
        );
    }

    /**
     * @param CarrierAwbParcelDto $parcel
     *
     * @return ParcelObject
     */
    public function toSdkParcel(CarrierAwbParcelDto $parcel): ParcelObject
    {
        return new ParcelObject(
            $parcel->getPosition(),
            $parcel->getAwbNumber()
        );
    }

    /**
     * @param list<ParcelObject> $parcels
     *
     * @return list<CarrierAwbParcelDto>
     */
    public function mapCollection(array $parcels): array
    {
        return array_map(
            function (ParcelObject $parcel): CarrierAwbParcelDto {
                return $this->map($parcel);
            },
            $parcels
        );
    }

    /**
     * @param list<CarrierAwbParcelDto> $parcels
     *
     * @return list<ParcelObject>
     */
    public function toSdkCollection(array $parcels): array
    {
        return array_map(
            function (CarrierAwbParcelDto $parcel): ParcelObject {
                return $this->toSdkParcel($parcel);
            },
            $parcels
        );
    }
}
