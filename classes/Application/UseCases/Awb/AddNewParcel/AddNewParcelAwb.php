<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewParcelAwb
{

    private AddNewParcelAwbRequest $addNewParcelAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->addNewParcelAwbRequest = $addNewParcelAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
    }

    /**
     * @return AddNewParcelAwbResponse
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): AddNewParcelAwbResponse
    {
        $sameday = new Sameday(SdkInitiator::init());
        $awb = $this->samedayAwbRepository->getAwbForOrderId($this->addNewParcelAwbRequest->getOrderId());

        if (null === $awb) {
            return new AddNewParcelAwbResponse(
                $this->addNewParcelAwbRequest->getOrderId(),
                'AWB not found for this order.',
                ResponseNoticeType::ERROR,
            );
        }

        $position = $this->getPosition($awb->getParcels() ?? '');
        $parcelItem = $this->addNewParcelAwbRequest->getAwbItem();

        $request = new SamedayPostParcelRequest(
            (string) $awb->getAwbNumber(),
            $parcelItem->getParcelDimensionsObject(),
            $position,
            $parcelItem->getParcelObservation(),
            null,
            $parcelItem->isParcelIsLast()
        );

        $parcel = null;
        try {
            $parcel = $sameday->postParcel($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
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

        if (isset($errors) && null === $parcel) {

            return new AddNewParcelAwbResponse(
                $this->addNewParcelAwbRequest->getOrderId(),
                AwbErrorParser::parse($errors),
                ResponseNoticeType::ERROR,
            );
        }

        $parcels = array_merge(
            unserialize($awb->getParcels() ?? '', ['']),
            [
                new ParcelObject(
                    $position,
                    $parcel->getParcelAwbNumber()
                )
            ]
        );

        $this->samedayAwbRepository->updateParcels($awb->getOrderId(), serialize($parcels));

        return new AddNewParcelAwbResponse(
            $this->addNewParcelAwbRequest->getOrderId(),
            "AWB added new parcel successfully.",
            ResponseNoticeType::SUCCESS,
        );
    }

    /**
     * @param $parcels
     *
     * @return int
     */
    private function getPosition($parcels): int
    {
        $nrOfParcels = count(unserialize($parcels, ['']));

        return $nrOfParcels + 1;
    }
}
