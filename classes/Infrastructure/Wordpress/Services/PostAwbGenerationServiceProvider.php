<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostAwbGenerationResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo\WooOrderAddressRepository;
use Throwable;

final class PostAwbGenerationServiceProvider implements PostAwbGenerationServiceProviderInterface
{
    private DbHandler $dbHandler;

    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @param ?DbHandler $dbHandler
     * @param ?OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     * @param ?SamedayAwbRepository $samedayAwbRepository
     */
    public function __construct(
        ?DbHandler $dbHandler = null,
        ?OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater = null,
        ?SamedayAwbRepository $samedayAwbRepository = null
    ) {
        $resolvedDbHandler = $dbHandler ?? new DbHandler();
        $this->dbHandler = $resolvedDbHandler;
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository($resolvedDbHandler);
        $this->orderShippingAddressUpdater = $orderShippingAddressUpdater ?? new WooOrderShippingAddressUpdater(
            new WooOrderAddressRepository($resolvedDbHandler),
            new WooOrderShippingAddressArchive(),
            new LockerDtoFactory(new SamedayLockerRepository($resolvedDbHandler)),
            new WooSamedayShippingHdAddressParser(),
            new WooStateCodeResolver(new WooCountriesHandler()),
        );
    }

    /**
     * @param PostAwbGenerationRequestDto $postAwbGenerationRequestDto
     * @param CarrierServiceRules $rules
     * @param CourierServiceProviderInterface $courier
     *
     * @return PostAwbGenerationResponseDto
     */
    public function apply(
        PostAwbGenerationRequestDto $postAwbGenerationRequestDto,
        CarrierServiceRules $rules,
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

        $this->applyOrderChanges($postAwbGenerationRequestDto, $rules);

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

    /**
     * @param PostAwbGenerationRequestDto $postAwbGenerationRequestDto
     * @param CarrierServiceRules $rules
     *
     * @return void
     */
    private function applyOrderChanges(
        PostAwbGenerationRequestDto $postAwbGenerationRequestDto,
        CarrierServiceRules $rules
    ): void {
        $orderId = $postAwbGenerationRequestDto->getOrderId();
        $service = $postAwbGenerationRequestDto->getService();
        $shippingLines = $postAwbGenerationRequestDto->getShippingLines();
        $samedayOrderItemId = array_key_first($shippingLines);
        $shippingLine = null !== $samedayOrderItemId ? $shippingLines[$samedayOrderItemId] : null;

        try {
            if ($rules->isOohDeliveryOption($service)) {
                $this->orderShippingAddressUpdater->activateOutOfHome($orderId);
            } else {
                $this->orderShippingAddressUpdater->activateHomeDelivery($orderId);
            }
        } catch (Throwable $exception) {
        }

        if (null !== $shippingLine) {
            try {
                $shippingLine->update_meta_data('service_id', $service->getSamedayId());
                $shippingLine->update_meta_data('service_code', $service->getSamedayCode());
                $shippingLine->save_meta_data();

                $shippingLine->set_method_id(CarrierConstants::PLUGIN_NAME);
                $shippingLine->save();
            } catch (Throwable $exception) {
            }
        }

        if (null !== $samedayOrderItemId) {
            try {
                $this->dbHandler->updateRow(
                    $this->dbHandler->buildTableName('woocommerce_order_items'),
                    ['order_item_name' => $service->getName() ?? $service->getSamedayName() ?? ''],
                    ['order_item_id' => $samedayOrderItemId]
                );
            } catch (Throwable $exception) {
            }
        }
    }
}
