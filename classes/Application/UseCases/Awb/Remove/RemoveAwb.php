<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class RemoveAwb
{
    private RemoveAwbRequest $removeAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(RemoveAwbRequest $removeAwbRequest)
    {
        $this->removeAwbRequest = $removeAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
    }

    /**
     * @return RemoveAwbResponse
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): RemoveAwbResponse
    {
        $awb = $this->removeAwbRequest->getAwb();
        $sameday = new Sameday(SdkInitiator::init());

        try {
            $sameday->deleteAwb(new SamedayDeleteAwbRequest((string) $awb->getAwbNumber()));
            $this->samedayAwbRepository->deleteAwbAndParcels($awb);
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $errors[] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        if (isset($errors)) {

            return new RemoveAwbResponse(
                $awb->getOrderId(),
                ResponseNoticeType::ERROR,
                Helper::parseAwbErrors($errors),
            );
        }

        return new RemoveAwbResponse(
            $awb->getOrderId(),
            ResponseNoticeType::SUCCESS,
        );
    }
}
