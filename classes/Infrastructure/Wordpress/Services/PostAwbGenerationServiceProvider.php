<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostAwbGenerationResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingChangesServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use Throwable;

final class PostAwbGenerationServiceProvider implements PostAwbGenerationServiceProviderInterface
{
    private SamedayAwbRepository $samedayAwbRepository;

    private OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider;

    /**
     * @param ?DbHandler $dbHandler
     * @param ?SamedayAwbRepository $samedayAwbRepository
     * @param ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     */
    public function __construct(
        ?DbHandler $dbHandler = null,
        ?SamedayAwbRepository $samedayAwbRepository = null,
        ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider = null
    ) {
        $resolvedDbHandler = $dbHandler ?? new DbHandler();
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository($resolvedDbHandler);
        $this->orderShippingChangesServiceProvider = $orderShippingChangesServiceProvider
            ?? new OrderShippingChangesServiceProvider($resolvedDbHandler);
    }

    /**
     * @param PostAwbGenerationRequestDto $postAwbGenerationRequestDto
     * @param CourierServiceProviderInterface $courier
     *
     * @return PostAwbGenerationResponseDto
     */
    public function apply(
        PostAwbGenerationRequestDto $postAwbGenerationRequestDto,
        CourierServiceProviderInterface $courier
    ): PostAwbGenerationResponseDto {
        $awbNumber = $postAwbGenerationRequestDto->getAwbNumber();

        try {
            $parcels = array_map(
                static function (array $parcel): ParcelObject {
                    return new ParcelObject(
                        (int) $parcel['position'],
                        (string) $parcel['awbNumber']
                    );
                },
                $postAwbGenerationRequestDto->getParcels()
            );

            $this->samedayAwbRepository->saveAwb([
                'order_id' => $postAwbGenerationRequestDto->getOrderId(),
                'awb_number' => $awbNumber,
                'parcels' => serialize($parcels),
                'awb_cost' => $postAwbGenerationRequestDto->getAwbCost(),
            ]);
        } catch (Throwable $exception) {
            return $this->rollbackRemoteAwb($courier, $awbNumber);
        }

        $this->orderShippingChangesServiceProvider->apply(
            new OrderShippingChangesRequestDto(
                $postAwbGenerationRequestDto->getOrderId(),
                $postAwbGenerationRequestDto->getService(),
                $postAwbGenerationRequestDto->getShippingLines()
            )
        );

        return new PostAwbGenerationResponseDto(
            true,
            'Awb generated successfully.'
        );
    }

    /**
     * @param CourierServiceProviderInterface $courier
     * @param string $awbNumber
     *
     * @return PostAwbGenerationResponseDto
     */
    private function rollbackRemoteAwb(
        CourierServiceProviderInterface $courier,
        string $awbNumber
    ): PostAwbGenerationResponseDto {
        try {
            $courier->removeAwb(new RemoveAwbRequestDto($awbNumber));

            $message = 'The AWB was generated but could not be saved. So it has been cancelled, please try again.';
        } catch (Throwable $rollbackException) {
            $message = sprintf(
                'The AWB %s was generated but could not be saved, and the automatic cancellation failed. 
                Please remove it manually.',
                $awbNumber
            );
        }

        return new PostAwbGenerationResponseDto(false, $message);
    }
}
