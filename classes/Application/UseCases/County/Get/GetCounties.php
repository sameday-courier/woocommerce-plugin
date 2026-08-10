<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use Exception;
use Sameday\Objects\CountyObject;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCounties
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(GetCountiesRequest $getCountiesRequest)
    {
        $this->sameday = $getCountiesRequest->getSameday();
    }

    /**
     * @return GetCountiesResponse
     */
    public function execute(): GetCountiesResponse
    {
        try {
            $samedayCounties = $this->sameday
                ->getCounties(new SamedayGetCountiesRequest(null))
                ->getCounties();
        } catch (Exception $exception) {
            return new GetCountiesResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GetCountiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            array_map(
                static function (CountyObject $county): array {
                    return [
                        'id' => $county->getId(),
                        'name' => $county->getName(),
                    ];
                },
                $samedayCounties
            ),
        );
    }
}
