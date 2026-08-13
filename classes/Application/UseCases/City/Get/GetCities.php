<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use Exception;
use Sameday\Objects\CityObject;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

final class GetCities
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var int $countyId
     */
    private int $countyId;

    public function __construct(GetCitiesRequest $getCitiesRequest)
    {
        $this->sameday = $getCitiesRequest->getSameday();
        $this->countyId = $getCitiesRequest->getGetCitiesItem()->getCountyId();
    }

    /**
     * @return GetCitiesResponse
     */
    public function execute(): GetCitiesResponse
    {
        $page = 1;
        $remoteCities = [];

        do {
            $request = new SamedayGetCitiesRequest($this->countyId);
            $request->setPage($page++);

            try {
                $cities = $this->sameday->getCities($request);
            } catch (Exception $exception) {
                return new GetCitiesResponse(
                    $exception->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($cities->getCities() as $city) {
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        return new GetCitiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            array_map(
                static function (CityObject $city): array {
                    return [
                        'id' => $city->getId(),
                        'name' => $city->getName(),
                    ];
                },
                $remoteCities
            ),
        );
    }
}
