<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\OrderShippingChangesResponseDto;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingChangesServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo\WooOrderAddressRepository;
use WC_Order_Item_Shipping;
use Throwable;

final class OrderShippingChangesServiceProvider implements OrderShippingChangesServiceProviderInterface
{
    private DbHandler $dbHandler;

    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    private CarrierServiceRules $carrierServiceRules;

    /**
     * @param ?DbHandler $dbHandler
     * @param ?OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     * @param ?CarrierServiceRules $carrierServiceRules
     */
    public function __construct(
        ?DbHandler $dbHandler = null,
        ?OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater = null,
        ?CarrierServiceRules $carrierServiceRules = null
    ) {
        $resolvedDbHandler = $dbHandler ?? new DbHandler();
        $this->dbHandler = $resolvedDbHandler;
        $this->orderShippingAddressUpdater = $orderShippingAddressUpdater ?? new WooOrderShippingAddressUpdater(
            new WooOrderAddressRepository($resolvedDbHandler),
            new WooOrderShippingAddressArchive(),
            new LockerDtoFactory(new LockerServiceProvider(new SamedayLockerRepository($resolvedDbHandler))),
            new WooSamedayShippingHdAddressParser(),
            new WooStateCodeResolver(new WooCountriesHandler()),
        );
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules(
            new SamedayServiceRepository($resolvedDbHandler)
        );
    }

    /**
     * @param OrderShippingChangesRequestDto $request
     *
     * @return OrderShippingChangesResponseDto
     */
    public function apply(OrderShippingChangesRequestDto $request): OrderShippingChangesResponseDto
    {
        $orderId = $request->getOrderId();
        $service = $request->getService();
        $shippingLines = $request->getShippingLines();
        $samedayOrderItemId = null;
        $shippingLine = null;
        foreach ($shippingLines as $orderItemId => $candidateShippingLine) {
            if (
                $candidateShippingLine instanceof WC_Order_Item_Shipping
                && CarrierConstants::PLUGIN_NAME === $candidateShippingLine->get_method_id()
                && $service->getSamedayCode() === $candidateShippingLine->get_meta(
                    CarrierConstants::META_SERVICE_CODE
                )
            ) {
                $samedayOrderItemId = $orderItemId;
                $shippingLine = $candidateShippingLine;
                break;
            }
        }

        try {
            if ($this->carrierServiceRules->isOohDeliveryOption($service)) {
                $this->orderShippingAddressUpdater->activateOutOfHome($orderId);
            } else {
                $this->orderShippingAddressUpdater->activateHomeDelivery($orderId);
            }
        } catch (Throwable $exception) {
            wc_get_logger()->error(
                'Unable to apply the order shipping address change.',
                [
                    'source' => 'samedaycourier-shipping',
                    'order_id' => $orderId,
                    'exception' => $exception->getMessage(),
                ]
            );
        }

        if (null !== $shippingLine) {
            try {
                $shippingLine->update_meta_data(
                    CarrierConstants::META_SERVICE_ID,
                    (string) $service->getSamedayId()
                );
                $shippingLine->update_meta_data(
                    CarrierConstants::META_SERVICE_CODE,
                    $service->getSamedayCode()
                );
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

        return new OrderShippingChangesResponseDto(
            true,
            'Order shipping changes applied successfully.'
        );
    }
}
