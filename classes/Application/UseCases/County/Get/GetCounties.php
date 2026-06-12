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
        $this->sameday = $getCountiesRequest->sameday;
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
                ResponseNoticeType::ERROR,
                $exception->getMessage(),
            );
        }

        return new GetCountiesResponse(
            ResponseNoticeType::SUCCESS,
            null,
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
