<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;

final class AddNewParcelAwb
{
    /**
     * @var AddNewParcelAwbRequest $addNewParcelAwbRequest
     */
    private AddNewParcelAwbRequest $addNewParcelAwbRequest;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @var ParcelDimensionsFactory $parcelDimensionsFactory
     */
    private ParcelDimensionsFactory $parcelDimensionsFactory;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->addNewParcelAwbRequest = $addNewParcelAwbRequest;
        $this->sameday = $addNewParcelAwbRequest->getSameday();
        $this->samedayAwbRepository = $addNewParcelAwbRequest->getSamedayAwbRepository();
        $this->awbErrorParser = $addNewParcelAwbRequest->getAwbErrorParser();
        $this->parcelDimensionsFactory = $addNewParcelAwbRequest->getParcelDimensionsFactory();
    }

    /**
     * @return AddNewParcelAwbResponse
     * @throws JsonException
     */
    public function execute(): AddNewParcelAwbResponse
    {
        $parcelItem = $this->addNewParcelAwbRequest->getAwbItem();

        $orderId = $parcelItem->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'AWB not found for this order.',
                ResponseNoticeType::ERROR,
            );
        }

        $position = $this->getPosition($awb->getParcels() ?? '');

        $parcelDimensionsObject = $this->parcelDimensionsFactory->fromAttributes(
            $parcelItem->getParcelWeight(),
            $parcelItem->getParcelWidth(),
            $parcelItem->getParcelLength(),
            $parcelItem->getParcelHeight(),
        );

        $request = new SamedayPostParcelRequest(
            (string) $awb->getAwbNumber(),
            $parcelDimensionsObject,
            $position,
            $parcelItem->getParcelObservation(),
            null,
            $parcelItem->isParcelIsLast()
        );

        $parcel = null;
        try {
            $parcel = $this->sameday->postParcel($request);
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
                $orderId,
                $this->awbErrorParser->parse($errors),
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

        if (!$this->samedayAwbRepository->updateParcels($awb->getOrderId(), serialize($parcels))) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'Unable to update AWB parcels',
                ResponseNoticeType::ERROR,
            );
        }

        return new AddNewParcelAwbResponse(
            $orderId,
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
