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
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewParcelAwb
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
     * @return void
     * @throws JsonException|SamedaySDKException
     */
    public function execute(): void
    {
        $sameday = new Sameday(SdkInitiator::init());
        $awb = $this->samedayAwbRepository->getAwbForOrderId($this->addNewParcelAwbRequest->getOrderId());

        if (null === $awb) {
            NoticerHandler::addFlashNotice(
                'add_new_parcel_notice',
                __('AWB not found for this order.', SamedayConstants::TEXT_DOMAIN),
                'error',
                true
            );

            Redirector::to(
                'post.php',
                [
                    'post' => $this->addNewParcelAwbRequest->getOrderId(),
                    'action' => 'edit',
                    'add-new-parcel' => 'error',
                ]
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
            $noticeError = Helper::parseAwbErrors($errors);
            NoticerHandler::addFlashNotice('add_new_parcel_notice', $noticeError, 'error', true);

            Redirector::to('post.php', [
                'post' => $awb->getOrderId(),
                'action' => 'edit',
                'add-new-parcel' => 'error',
            ]);
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

        Redirector::to(
            'post.php',
            [
                'post' => $awb->getOrderId(),
                'action' => 'edit',
                'add-new-parcel' => 'success',
            ]
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
