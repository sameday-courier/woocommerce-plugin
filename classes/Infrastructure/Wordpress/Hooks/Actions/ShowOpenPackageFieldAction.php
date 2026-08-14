<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressCarrierSettingsProvider;

final class ShowOpenPackageFieldAction extends AbstractAction
{
    private const ACTION_NAME = 'woocommerce_review_order_after_shipping';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION_NAME;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!is_checkout()) {
            return;
        }

        $samedayServiceRepository = new SamedayServiceRepository();
        $service = $samedayServiceRepository->getServiceSamedayByCode(
            (new WooShippingMethodProvider())->getChosenServiceCode()
        );

        /** @var OptionalTaxObject[] $optionalTaxes */
        $optionalTaxes = [];
        if (null !== $service && null !== $service->getServiceOptionalTaxes() && '' !== $service->getServiceOptionalTaxes()) {
            $optionalTaxes = unserialize($service->getServiceOptionalTaxes(), ['']);
            if (!$optionalTaxes) {
                $optionalTaxes = [];
            }
        }

        $taxOpenPackage = null;
        foreach ($optionalTaxes as $optionalTax) {
            if ($optionalTax->getCode() === CarrierConstants::OPEN_PACKAGE_OPTION_CODE) {
                $taxOpenPackage = $optionalTax->getId();
            }
        }

        if ($this->hasToShowOpenPackageOption($taxOpenPackage)) {
            echo $this->buildHtmlContent();
        }
    }

    /**
     * @param int|null $taxId
     *
     * @return bool
     */
    private function hasToShowOpenPackageOption(?int $taxId): bool
    {
        if (null === $taxId) {
            return false;
        }

        return (new WordpressCarrierSettingsProvider())->get()->isOpenPackageStatusEnabled();
    }

    /**
     * @return string
     */
    private function createNewField(): string
    {
        return woocommerce_form_field(
            'open_package',
            [
                'type' => 'checkbox',
                'class' => ['input-checkbox'],
                'id' => 'sameday_open_package',
                'label' => (new WordpressCarrierSettingsProvider())->get()->getOpenPackageLabel(),
                'required' => false,
                'return' => true,
            ],
            'yes' === (new WooSessionHandler())->get(CarrierSessionKeys::OPEN_PACKAGE)
        );
    }

    /**
     * @return string
     */
    private function buildHtmlContent(): string
    {
        return sprintf(
    '<tr class="shipping-pickup-store">
                <th></th>
                <td>
                    <ul id="shipping_method" class="woocommerce-shipping-methods sameday-shipping-methods">
                        <li>
                            %s
                        </li>
                    </ul>
                </td>
            </tr>',
            $this->createNewField()
        );
    }
}
