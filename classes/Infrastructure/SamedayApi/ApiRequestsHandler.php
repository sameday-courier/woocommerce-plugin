<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\CityObject;
use Sameday\Objects\CountyObject;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

class ApiRequestsHandler
{
    public function __construct()
    {
        $this->dbHandler = new DbHandler();
        $this->samedayServiceRepository = new SamedayServiceRepository($this->dbHandler);
        $this->samedayAwbRepository = new SamedayAwbRepository($this->dbHandler);
    }

    /**
     * @return array
     */
    public static function getCounties(): array
    {
        try {
            $sameday = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException|Exception $exception) {
            return [];
        }

        try{
            $samedayCounties = $sameday->getCounties(new SamedayGetCountiesRequest(null))
                ->getCounties()
            ;
        } catch (Exception $e) {
            return [];
        }

        return array_map(static function(CountyObject $county){
            return ['id' => $county->getId(), 'name' => $county->getName()];
        }, $samedayCounties);
    }
}
