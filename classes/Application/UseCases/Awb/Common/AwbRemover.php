<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Common;

use Sameday\Exceptions\SamedayAuthenticationException;
use Sameday\Exceptions\SamedayAuthorizationException;
use Sameday\Exceptions\SamedayNotFoundException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Exceptions\SamedayServerException;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbRemover
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(
        Sameday $sameday,
        SamedayAwbRepository $samedayAwbRepository
    )
    {
        $this->sameday = $sameday;
        $this->samedayAwbRepository = $samedayAwbRepository;
    }

    /**
     * @param string $awbNumber
     *
     * @return void
     *
     * @throws SamedayAuthenticationException
     * @throws SamedayAuthorizationException
     * @throws SamedayNotFoundException
     * @throws SamedayOtherException
     * @throws SamedaySDKException
     * @throws SamedayServerException
     */
    public function removeRemote(string $awbNumber): void
    {
        $this->sameday->deleteAwb(new SamedayDeleteAwbRequest($awbNumber));
    }

    /**
     * Removes the local persistence of the AWB and its parcels.
     *
     * @param SamedayAwb $awb
     *
     * @return void
     */
    public function removeLocal(SamedayAwb $awb): void
    {
        $this->samedayAwbRepository->deleteAwbAndParcels($awb);
    }

    /**
     * @param SamedayAwb $awb
     *
     * @return void
     *
     * @throws SamedayAuthenticationException
     * @throws SamedayAuthorizationException
     * @throws SamedayNotFoundException
     * @throws SamedayOtherException
     * @throws SamedaySDKException
     * @throws SamedayServerException
     */
    public function remove(SamedayAwb $awb): void
    {
        $this->removeRemote((string) $awb->getAwbNumber());
        $this->removeLocal($awb);
    }
}
