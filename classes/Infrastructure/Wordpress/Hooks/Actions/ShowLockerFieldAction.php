<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Exception;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;

final class ShowLockerFieldAction extends AbstractAction
{
    private const ACTION = 'woocommerce_review_order_after_shipping';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $serviceCode = (new WooShippingMethodProvider())->getChosenServiceCode();
        if (!(new SamedayServiceRules(new SamedayServiceRepository()))->isOohDeliveryOptionByCode($serviceCode)
            || !is_checkout()
        ) {
            return;
        }

        echo $this->buildHtmlContent($this->resolveShipTo());
    }

    /**
     * @return string|null
     */
    private function resolveShipTo(): ?string
    {
        $lockerSession = (new WooSessionHandler())->get(SamedaySessionKeys::LOCKER);
        if (null === $lockerSession) {
            return null;
        }

        try {
            $lockerSession = json_decode($lockerSession, false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return null;
        }

        return sprintf(
            '%s <br/> %s',
            esc_html($lockerSession->name ?? ''),
            esc_html($lockerSession->address ?? '')
        );
    }

    /**
     * @param string|null $shipTo
     *
     * @return string
     */
    private function buildHtmlContent(?string $shipTo): string
    {
        $settings = (new WordpressSamedaySettingsProvider())->get();
        if ($settings->isLockersMapEnabled()) {
            return $this->buildLockersMapHtml($shipTo);
        }

        return $this->buildLockersDropdownHtml();
    }

    /**
     * @param string|null $shipTo
     *
     * @return string
     */
    private function buildLockersMapHtml(?string $shipTo): string
    {
        $settings = (new WordpressSamedaySettingsProvider())->get();
        $html = sprintf(
            '<tr class="shipping-pickup-store">
                <td><strong>%s</strong></td>
                <th>
                    <button type="button" class="button alt sameday_select_locker"
                        id="select_locker"
                        data-username="%s"
                        data-country="%s"
                    >
                        %s
                    </button>
                </th>
            </tr>',
            TranslatorHandler::translate('Sameday Locker'),
            esc_attr($settings->getUser() ?? ''),
            esc_attr($settings->getHostCountry()),
            TranslatorHandler::translate('Show Locations Map')
        );

        if (null === $shipTo) {
            return $html;
        }

        return $html . sprintf(
            '<tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
                <td><strong>%s</strong></td>
                <th><span id="showLockerDetails">%s</span></th>
            </tr>',
            TranslatorHandler::translate('Ship to'),
            wp_kses_post($shipTo)
        );
    }

    /**
     * @return string
     */
    private function buildLockersDropdownHtml(): string
    {
        return sprintf(
            '<tr>
                <th><label for="shipping-pickup-store-select"></label></th>
                <td>
                    <select name="locker_id" id="shipping-pickup-store-select">
                        <option value="" class="sameday-locker-placeholder">
                            %s
                        </option>
                        %s
                    </select>
                </td>
            </tr>',
            TranslatorHandler::translate('Select easyBox'),
            $this->buildLockerOptions()
        );
    }

    /**
     * @return string
     */
    private function buildLockerOptions(): string
    {
        $samedayLockerRepository = new SamedayLockerRepository();
        $cities = $samedayLockerRepository->getCitiesWithLockers();
        $lockers = [];
        foreach ($cities as $city) {
            if (null !== $city->getCity()) {
                $lockers[$city->getCity() . ' (' . $city->getCounty() . ')'] = $samedayLockerRepository->getLockersByCity(
                    (string) $city->getCity()
                );
            }
        }

        $lockerOptions = '';
        foreach ($lockers as $city => $cityLockers) {
            $optionGroup = '<optgroup label="' . esc_attr($city) . '" class="sameday-locker-optgroup"></optgroup>';
            $options = '';
            foreach ($cityLockers as $locker) {
                $lockerDetails = esc_html($locker->getName() . ' - ' . $locker->getAddress());
                $isSelected = '';
                if ((int) (new WooSessionHandler())->get(SamedaySessionKeys::LOCKER) === (int) $locker->getLockerId()) {
                    $isSelected = "selected='selected'";
                }
                $options .= sprintf(
                    '<option value="%s" class="sameday-locker-option" %s> %s </option>',
                    esc_attr((string) $locker->getLockerId()),
                    $isSelected,
                    $lockerDetails
                );
            }

            $lockerOptions .= $optionGroup . $options;
        }

        return $lockerOptions;
    }
}
