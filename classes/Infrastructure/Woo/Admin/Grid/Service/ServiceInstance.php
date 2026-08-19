<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Service;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\RequestSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use WC_Admin_Settings;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

class ServiceInstance
{
    public static $instance;

    public $services_obj;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     */
    public function __construct()
    {
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_action('admin_menu', [$this, 'plugin_menu']);

        $this->samedayServiceRepository = new SamedayServiceRepository();
    }

    /**
     * @param mixed $status
     * @param mixed $option
     * @param mixed $value
     *
     * @return mixed
     */
    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    /**
     * @return mixed
     */
    public function plugin_menu()
    {
        $parentSlug = 'edit.php?post_type=page';
        $pageSlug = 'sameday_services';

        $hook = add_submenu_page(
            $parentSlug,
            'SamedayCourier Service Table',
            'Sameday Services',
            'manage_options',
            $pageSlug,
            [ $this, 'plugin_settings_page' ]
        );

        add_action("load-$hook", [ $this, 'screen_option' ]);

        // Keep page out of the Pages menu while retaining a real parent so WP can resolve $title.
        add_action('admin_head', static function () use ($parentSlug, $pageSlug): void {
            remove_submenu_page($parentSlug, $pageSlug);
        });
    }

    /**
     * Plugin settings page
     *
     * @return mixed
     */
    public function plugin_settings_page()
    {
        ?>
        <div class="wrap">

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <?php if (!isset($_GET['action']) || RequestSanitizer::getAction() !== 'edit') { ?>
                        <div class="meta-box-sortables ui-sortable">
                            <div>
                                <a href="<?php echo UrlsHandler::buildSamedaySettingsPage(); ?>"
                                   class="sameday_admin_button">
                                    <?php echo TranslatorHandler::translate('Back') ?>
                                </a>
                                <form action="<?php echo admin_url('admin-post.php') ?>"
                                      method="post"
                                      style="width:200px; display:inline-block;
                                             top: -2px !important; position: relative;">
                                    <input type="hidden" name="action" value="refresh_services">
                                    <input type="hidden"
                                           name="_wpnonce"
                                           value="<?php echo esc_attr(wp_create_nonce('refresh_services')); ?>">
                                    <input type="submit" class="sameday_admin_button" value="Refresh Services">
                                </form>
                            </div>
                            <form method="post">
                                <?php
                                    $this->services_obj->prepare_items();
                                    $this->services_obj->display();
                                ?>
                            </form>
                        </div>
                        <?php } else { ?>
                        <div> <?php echo $this->createServiceForm(RequestSanitizer::getIntId()); ?> </div>
                        <?php } {?>
                    </div>
                </div>
            </div>
        </div>
        <?php }
    }

    /**
     * @return array
     */
    private function getStatuses(): array
    {
        return array(
            array(
                'value' => 0,
                'text' => TranslatorHandler::translate('Disabled')
            ),
            array(
                'value' => 1,
                'text' => TranslatorHandler::translate('Always')
            )
        );
    }

    /**
     * @param mixed $id
     *
     * @return string
     */
    private function createServiceForm($id): string
    {
        $service = $this->samedayServiceRepository->getServiceById((int) $id);

        if (null === $service) {
            WC_Admin_Settings::add_error('No service available !');
            WC_Admin_Settings::show_messages();
            exit;
        }

        $greyedOut = "";
        $serviceName = $service->getSamedayName() ?? '';
        if ($service->getSamedayCode() === CarrierConstants::LOCKER_NEXT_DAY_CODE) {
            $greyedOut = "disabled";
            $carrierSettingsProvider = new CarrierSettingsServiceProvider();
            $hostCountry = $carrierSettingsProvider->get()->getHostCountry();
            $serviceName = CarrierConstants::OOH_SERVICES_LABELS[$hostCountry];
        }

        $statuses = '';
        foreach ($this->getStatuses() as $status) {
            $checked = ((int) ($service->getStatus() ?? 0)) === ((int) $status['value']) ? 'selected' : '';
            $statuses .= '<option value="' . $status['value'] . '" ' . $checked . ' >'
                . $status['text'] . '</option>';
        }

        $serviceNameLabel = TranslatorHandler::translate('Service Name');
        $priceLabel = TranslatorHandler::translate('Price');
        $freeDeliveryLabel = TranslatorHandler::translate('Free delivery price');
        $statusLabel = TranslatorHandler::translate('Status');
        $editServiceLabel = TranslatorHandler::translate('Edit Service');
        $priceValue = esc_attr((string) ($service->getPrice() ?? ''));
        $priceFreeValue = esc_attr((string) ($service->getPriceFree() ?? ''));
        $serviceIdValue = esc_attr((string) $id);
        $escapedServiceName = esc_html($serviceName);

        return
        '<strong style="font-size: large; color: #0A246A"> Edit Service - '
            . $escapedServiceName . '</strong>
            <form method="POST" onsubmit="" action="' . admin_url('admin-post.php') . '">
                <input type="hidden" name="action" value="edit-service">
                <table class="form-table editServiceForm">
                    <tbody>
                        <input type="hidden"
                               name="samedaycourier-service-id"
                               value="' . $serviceIdValue . '">
                        <input type="hidden" name="_wpnonce" value="' . wp_create_nonce('edit-service') . '">
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="samedaycourier-service-name">  '
            . $serviceNameLabel . '<span style="color: #ff2222"> * </span>  </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text"
                                       name="samedaycourier-service-name"
                                       style="width: 297px; height: 36px;" '
            . $greyedOut . '
                                       id="samedaycourier-service-name"
                                       value="' . $escapedServiceName . '">
                             </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="samedaycourier-price">  '
            . $priceLabel . '<span style="color: #ff2222"> * </span> </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       name="samedaycourier-price"
                                       step="any"
                                       style="width: 297px; height: 36px;"
                                       id="samedaycourier-price"
                                       value="' . $priceValue . '">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="samedaycourier-free-delivery-price">  '
            . $freeDeliveryLabel . ' </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="number"
                                       name="samedaycourier-free-delivery-price"
                                       step="any"
                                       style="width: 297px; height: 36px;"
                                       id="samedaycourier-free-delivery-price"
                                       value="' . $priceFreeValue . '">
                            </td>
                        </tr>
                       <tr valign="top">
                            <th scope="row">
                                <label for="samedaycourier-status">  '
            . $statusLabel . '<span style="color: #ff2222"> * </span> </label>
                            </th>
                            <td class="forminp forminp-text">
                                <select name="samedaycourier-status"
                                        style="width: 297px; height: 36px;"
                                        id="samedaycourier-status">
                                    ' . $statuses . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <button class="sameday_admin_button" type="submit" value="Submit" >  '
            . $editServiceLabel . '</button>
                            </th>
                        </tr>
                     </tbody>
                </table>
            </form>
        ';
    }

    /**
     * Screen options
     *
     * @return mixed
     */
    public function screen_option()
    {

        $option = 'per_page';
        $args   = [
            'label'   => 'services',
            'default' => 5,
            'option'  => 'services_per_page'
        ];

        add_screen_option($option, $args);

        $this->services_obj = new Services();
    }

    /**
     * Singleton instance
     */
    /**
     * @return self
     */
    public static function get_instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
