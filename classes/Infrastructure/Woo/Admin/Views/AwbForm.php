<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use JsonException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierCurrencyRules;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\AwbFormOptionsProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\SamedayIcon;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressCarrierSettingsProvider;
use WC_Order;

class AwbForm
{
    public const MODAL_ID = 'sameday-generate-awb-modal';

    /**
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    public function __construct(
        SamedayServiceRepository $samedayServiceRepository,
        SamedayLockerRepository $samedayLockerRepository,
        SamedayPickupPointRepository $samedayPickupPointRepository
    )
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->carrierServiceRules = new CarrierServiceRules($this->samedayServiceRepository);
    }

    /**
     * @param WC_Order $order
     * @return string
     */
    public function samedaycourierAddAwbForm(WC_Order $order): string
    {
        $postMetaLocker = PostMetaHandler::get(
            $order->get_id(),
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
        );

        $lockerDto = (new LockerDtoFactory($this->samedayLockerRepository))->fromInput($postMetaLocker);

        $serviceCode = null;
        foreach ($order->get_data()['shipping_lines'] as $shippingLine) {
            if ($shippingLine->get_method_id() !== CarrierConstants::PLUGIN_NAME) {
                continue;
            }

            if (null !== $serviceCode = $shippingLine->get_meta('service_code')) {
                if (
                    null !== $lockerDto
                    && '1' === $lockerDto->getOohType()
                    && $this->carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)
                ) {
                    $serviceCode = CarrierConstants::OOH_TYPES['1'] ;
                }

                break;
            }
        }

        $total_weight = 0.0;
        $weight = 0.0;
        foreach ($order->get_items() as $v) {
            $_product = wc_get_product($v['product_id']);
            $qty = $v['quantity'];

            if (isset($_product) && $_product !== false) {
                $weight = (float) $_product->get_weight();
            }

            $total_weight += (float) number_format($weight * $qty, 2);
        }
        $total_weight = $total_weight ?: 1;

        $pickupPointOptions = '';
        $pickupPoints = $this->samedayPickupPointRepository->getPickupPoints();
        foreach ($pickupPoints as $pickupPoint) {
            $checked = true === $pickupPoint->getDefaultPickupPoint() ? "selected" : "";
            $pickupPointOptions .= "<option value='{$pickupPoint->getSamedayId()}' {$checked}> {$pickupPoint->getSamedayAlias()} </option>" ;
        }

        $packageTypeOptions = '';
        $packagesType = AwbFormOptionsProvider::getPackageTypeOptions();
        foreach ($packagesType as $packageType) {
            $packageTypeOptions .= "<option value='{$packageType['value']}'>{$packageType['name']}</option>";
        }

        $awbPaymentTypeOptions = '';
        $awbPaymentsType = AwbFormOptionsProvider::getAwbPaymentTypeOptions();
        foreach ($awbPaymentsType as $awbPaymentType) {
            $awbPaymentTypeOptions .= "<option value='{$awbPaymentType['value']}'>{$awbPaymentType['name']}</option>";
        }

        $payment_gateway = wc_get_payment_gateway_by_order($order);
        $repayment = (float) $order->get_total();

        if ($payment_gateway->id !== CarrierConstants::CASH_ON_DELIVERY) {
            $repayment = 0;
        }

        $openPackage = (new WooOpenPackageOrderDataHandler())->isEnabled($order->get_id()) ? 'checked' : '';

        $lockerDetailsForm = null;
        $lockerDetails = null;
        if (null !== $lockerDto) {
            try {
                $lockerDetailsForm = json_encode($lockerDto->toArray(), JSON_THROW_ON_ERROR);
                $lockerDetails = sprintf('%s - %s', $lockerDto->getName(), $lockerDto->getAddress());
            } catch (JsonException $exception) {
                $lockerDto = null;
                $lockerDetailsForm = null;
                $lockerDetails = null;
            }
        }

        $settings = (new WordpressCarrierSettingsProvider())->get();
        $username = $settings->getUser();
        $hostCountry = $settings->getHostCountry();
        $destCity = $order->get_data()['shipping']['city'] ?? '';
        $destCountry = $order->get_data()['shipping']['country'] ?? '';

        $destCurrency = CarrierConstants::CURRENCY_MAPPER[$destCountry];
        $currency = $order->get_currency() ?? get_woocommerce_currency();
        $currencyWarningMessage = '';
        if (CarrierCurrencyRules::hasCurrencyIssue($repayment, $destCurrency, $currency)) {
            $message = sprintf(
                'Be aware that the intended currency is %s but the Repayment value is expressed in %s.
             Please consider a conversion !!',
                $destCurrency,
                $currency
            );
            $currencyWarningMessage = "
            <tr>
                <span>
                        <strong style='color: darkred'>"
                . TranslatorHandler::translate($message) . "
                        </strong>
                </span>
            </tr>
        ";
        }

        $samedayServices = $this->samedayServiceRepository->getAvailableServices();

        $allowLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
        $allowFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
        $servicesOptions = '';
        foreach ($samedayServices as $carrierService) {
            $firstMileId = $this->carrierServiceRules->isEligibleToLockerFirstMile($carrierService)
                ? $carrierService->getSamedayId()
                : 0;

            $checked = ($serviceCode === $carrierService->getSamedayCode()) ? 'selected' : '';
            $optionFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($firstMileId === $carrierService->getSamedayId()) {
                $optionFirstMile = CarrierConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            $optionLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['hide'];
            if ($this->carrierServiceRules->isOohDeliveryOption($carrierService)) {
                $optionLastMile = CarrierConstants::TOGGLE_HTML_ELEMENT['show'];
            }

            if ('' !== $checked) {
                $allowFirstMile = $optionFirstMile;
                $allowLastMile = $optionLastMile;
            }

            $option = sprintf(
                "<option data-fistMile='%s' data-lastMile='%s' value='%s' %s> %s </option>",
                $optionFirstMile,
                $optionLastMile,
                $carrierService->getSamedayId(),
                $checked,
                $carrierService->getSamedayName() ?? '',
            );
            $servicesOptions .= $option;
        }

        $title = TranslatorHandler::translate('Generate awb');
        $subtitle = TranslatorHandler::translate('Configure shipment details before generating the AWB.');
        $cancel = TranslatorHandler::translate('Cancel');

        $formBody = '<div id="sameday-shipping-content-add-awb">
                <table>
                    <tbody>
                         <input type="hidden" form="addAwbForm" name="samedaycourier-order-id" id="samedaycourier-order-id" value="' . $order->get_id() . '">
                         <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-repayment"> ' . sprintf("%s (%s)", TranslatorHandler::translate("Repayment"), $currency) .' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="text" onkeypress="return (event.charCode !== 8 && event.charCode === 0 || ( event.charCode === 46 || (event.charCode >= 48 && event.charCode <= 57)))" form="addAwbForm" name="samedaycourier-package-repayment" id="samedaycourier-package-repayment" value="' . $repayment . '">
                                <span>' . TranslatorHandler::translate("Payment type: ") . $payment_gateway->title . '</span>
                             </td>

                        </tr>
                        '. $currencyWarningMessage . '
                        <tr valign="middle" colspan="4">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-insurance-value"> ' . TranslatorHandler::translate("Insured value") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-insurance-value" min="0" step="0.1" id="samedaycourier-package-insurance-value" value="0">
                             </td>
                        </tr>
                        <tr>
                            <th><label>' . TranslatorHandler::translate("Parcels") . '</label></th>
                            <td class="forminp forminp-text">
                                <input readonly type="number" form="addAwbForm" min="0" step="0.1" id="samedaycourier-package-length" value="' . TranslatorHandler::translate("1") . '">
                            </td>
                             <td class="forminp forminp-text">
                                <input readonly type="text" form="addAwbForm" min="0" step="0.1" id="sameday-package-weight" value="Calculated Weight: ' . $total_weight . ' ' . OptionsHandler::getOption('woocommerce_weight_unit', 'kg') . '">
                             </td>
                             <td>
                                <button type="button" class="sameday_admin_button" id="addParcelButton">+</button>
                             </td>
                        </tr>
                        <tr valign="middle" class="rowPackageDimension">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-weight"> ' . TranslatorHandler::translate("Package Dimensions") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input class="samedaycourier-package-weight-class" type="number" form="addAwbForm" name="samedaycourier-package-dimensions[1][weight]" min="0.1" step="0.1" id="samedaycourier-package-weight" value="' . $total_weight . '" placeholder="' . TranslatorHandler::translate("Package Weight") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-dimensions[1][length]" min="0" step="0.1" id="samedaycourier-package-length" placeholder="' . TranslatorHandler::translate("Package Length") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-dimensions[1][height]" min="0" step="0.1" id="samedaycourier-package-height" placeholder="' . TranslatorHandler::translate("Package Height") . '">
                             </td>
                             <td class="forminp forminp-text">
                                <input type="number" form="addAwbForm" name="samedaycourier-package-dimensions[1][width]" min="0" step="0.1" id="samedaycourier-package-width" placeholder="' . TranslatorHandler::translate("Package Width") . '">
                             </td>
                             <td><button type="button" class="sameday_admin_button deleteParcelButton">✖</button></td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-pickup-point"> ' . TranslatorHandler::translate("Pickup-point") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-pickup-point" id="samedaycourier-package-pickup-point" >
                                    ' . $pickupPointOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-type"> ' . TranslatorHandler::translate("Package type") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-type" id="samedaycourier-package-type">
                                    ' . $packageTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-awb-payment"> ' . TranslatorHandler::translate("Awb payment") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-package-awb-payment" id="samedaycourier-package-awb-payment">
                                    ' . $awbPaymentTypeOptions . '
                                </select>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-service"> ' . TranslatorHandler::translate("Service") . ' <span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <select form="addAwbForm" name="samedaycourier-service" id="samedaycourier-service">
                                    ' . $servicesOptions . '
                                </select>
                                <input type="hidden" form="addAwbForm" name="samedaycourier-service-optional-tax-id" id="samedaycourier-service-optional-tax-id">
                             </td>
                        </tr> ';
        $formBody .= '<tr id="LockerFirstMile" class="'.$allowFirstMile.'">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-locker_first_mile"> ' . TranslatorHandler::translate("Personal delivery at locker") . '</label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-locker_first_mile" id="samedaycourier-locker_first_mile" class="sameday-modal-checkbox">
                                <span style="display:block;width:100%">' . TranslatorHandler::translate("Check this field if you want to apply for Personal delivery of the package at an easyBox terminal.") . '</span>
                                <span style="display:block;width:100%"><a href="https://sameday.ro/easybox#lockers-intro" target="_blank">' . TranslatorHandler::translate("Show map") . '</a></span>
                                <span class="sameday-custom-tooltip"> ' . TranslatorHandler::translate("Show locker dimensions") . '    <span class="sameday-tooltiptext">        <table class="table table-hover"> <tbody> <tr> <th></th> <th>L</th> <th>l</th> <th>h</th> </tr><tr> <td>Small (cm)</td><td> 47</td><td> 44.5</td><td> 10</td></tr><tr> <td>Medium (cm)</td><td> 47</td><td> 44.5</td><td> 19</td></tr><tr> <td>Large (cm)</td><td> 47</td><td> 44.5</td><td> 39</td></tr> </tbody></table>    </span></span>
                            </td>
                        </tr>';

        $formBody .=  '<tr id="LockerLastMile" class="'.$allowLastMile.'" style="vertical-align: middle;">
                             	<th scope="row" class="titledesc">
                                    <label for="samedaycourier-locker-details"> ' . TranslatorHandler::translate("Location details") . ' </label>
                                </th>
                                <td class="forminp forminp-text" colspan="4">';
        $formBody .= '<input type="hidden" form="addAwbForm" id="locker" name="locker" value="' . esc_attr((string) $lockerDetailsForm) . '">';
        $formBody .='  <textarea id="sameday_locker_name" disabled="disabled">' . esc_html((string) $lockerDetails) . '</textarea><br/>
                                    <button class="sameday_admin_button"
                                        data-username="' . esc_attr((string) $username) . '"
                                        data-country="' . esc_attr((string) $hostCountry) . '"
                                        data-dest_city="' . esc_attr($destCity) . '"
                                        data-dest_country="' . esc_attr($destCountry) . '"
                                        type="button"
                                        id="select_locker"> ' . TranslatorHandler::translate("Change location") . '
                                    </button>
                                </td>
                            </tr>';

        $formBody .= '<tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-open-package-status"> ' . TranslatorHandler::translate("Open package") . '</label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <input type="checkbox" form="addAwbForm" name="samedaycourier-open-package-status" id="samedaycourier-open-package-status" class="sameday-modal-checkbox" '.$openPackage.'>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-package-observation"> ' . TranslatorHandler::translate("Observation") . ' </label>
                            </th>
                            <td class="forminp forminp-text" colspan="4">
                                <textarea form="addAwbForm" name="samedaycourier-package-observation" id="samedaycourier-package-observation" ></textarea>
                             </td>
                        </tr>
                        <tr valign="middle">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-client-reference"> ' . TranslatorHandler::translate("Client Reference") . ' </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text" form="addAwbForm" name="samedaycourier-client-reference" id="samedaycourier-client-reference" value="' . $order->get_id() . '">
                              	<span>' . TranslatorHandler::translate("By default this field is complete with Order ID") . '</span>
                             </td>
                        </tr>
                    </tbody>
                </table>
            </div>';

        return sprintf(
            '<div id="%1$s" class="sameday-bulk-awb-modal sameday-generate-awb-modal" hidden data-sameday-generate-awb-modal>
                <div class="sameday-bulk-awb-modal__backdrop" data-sameday-generate-awb-close></div>
                <div class="sameday-bulk-awb-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="%1$s-title">
                    <div class="sameday-bulk-awb-modal__header">
                        <div class="sameday-bulk-awb-modal__heading">
                            <div class="sameday-bulk-awb-modal__icon" aria-hidden="true">
                                %7$s
                            </div>
                            <div class="sameday-bulk-awb-modal__titles">
                                <h2 id="%1$s-title">%2$s</h2>
                                <p>%3$s</p>
                            </div>
                        </div>
                        <button type="button" class="sameday-bulk-awb-modal__close" data-sameday-generate-awb-close aria-label="%4$s">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="sameday-bulk-awb-modal__body">
                        %5$s
                    </div>
                    <div class="sameday-bulk-awb-modal__footer">
                        <button type="button" class="sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--cancel" data-sameday-generate-awb-close>
                            %4$s
                        </button>
                        <button type="submit" form="addAwbForm" value="Submit" class="sameday_button sameday-bulk-awb-modal__btn sameday-bulk-awb-modal__btn--confirm">
                            %6$s
                        </button>
                    </div>
                </div>
            </div>',
            esc_attr(self::MODAL_ID),
            esc_html($title),
            esc_html($subtitle),
            esc_html($cancel),
            $formBody,
            esc_html(TranslatorHandler::translate('Generate Awb')),
            SamedayIcon::render('sameday-bulk-awb-modal__icon-svg sameday-bulk-awb-modal__icon-svg--package', 26)
        );
    }
}
