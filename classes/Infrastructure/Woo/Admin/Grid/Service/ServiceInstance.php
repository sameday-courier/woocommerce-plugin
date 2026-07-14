<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Service;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\RequestSanitizer;
use SamedayCourier\Shipping\Utils\Helper;
use WC_Admin_Settings;
class ServiceInstance
{
	static $instance;

	public $services_obj;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

	public function __construct()
	{
		add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
		add_action('admin_menu', [$this, 'plugin_menu']);

        $this->samedayServiceRepository = new SamedayServiceRepository();
	}

	public static function set_screen($status, $option, $value)
	{
		return $value;
	}

	public function plugin_menu()
	{
		$hook = add_submenu_page(
            '',
            'SamedayCourier Service Table',
            'Sameday Services',
            'manage_options',
            'sameday_services',
		    [ $this, 'plugin_settings_page' ]
        );

		add_action("load-$hook", [ $this, 'screen_option' ]);
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
        ?>
        <div class="wrap">

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <?php if(!isset($_GET['action']) || RequestSanitizer::getAction() !== 'edit') { ?>
                        <div class="meta-box-sortables ui-sortable">
                            <div>
                                <a href="<?php echo Helper::getPathToSettingsPage(); ?>" class="sameday_admin_button">
		                            <?php echo __('Back', SamedayConstants::TEXT_DOMAIN) ?>
                                </a>
                                <form action="<?php echo admin_url('admin-post.php') ?>" method="post" style="width:200px; display:inline-block;top: -2px !important; position: relative;">
                                    <input type="hidden" name="action" value="refresh_services">
                                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('refresh_services')); ?>">
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

    private function getStatuses(): array
    {
		return array(
			array(
				'value' => 0,
				'text' => __('Disabled', SamedayConstants::TEXT_DOMAIN)
			),
			array(
				'value' => 1,
				'text' => __('Always', SamedayConstants::TEXT_DOMAIN)
			)
		);
	}

    /**
     * @param $id
     *
     * @return string
     */
	private function createServiceForm($id): string
    {
        $service = $this->samedayServiceRepository->getServiceById((int) $id);

        if (null === $service) {
	        WC_Admin_Settings::add_error('No service available !');
	        WC_Admin_Settings::show_messages(); exit;
        }

        $greyedOut = "";
        $serviceName = $service->getSamedayName() ?? '';
        if ($service->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
            $greyedOut = "disabled";
            $serviceName = SamedayConstants::OOH_SERVICES_LABELS[Helper::getHostCountry()];
        }

	    $statuses = '';
        foreach ($this->getStatuses() as $status) {
            $checked = ((int) ($service->getStatus() ?? 0)) === ((int) $status['value']) ? 'selected' : '';
	        $statuses .= '<option value="'.$status['value'].'" '.$checked.' >' . $status['text'] . '</option>';
        }

        return
        '<strong style="font-size: large; color: #0A246A"> Edit Service - ' . esc_html($serviceName) . '</strong>
            <form method="POST" onsubmit="" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="sameday_edit_service">
                <table class="form-table editServiceForm">
                    <tbody>
                        <input type="hidden" name="samedaycourier-service-id" value="'.esc_attr((string) $id).'">
                        <input type="hidden" name="_wpnonce" value="'.wp_create_nonce('edit-service').'">
                        <tr valign="top">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service-name">  '.__('Service Name', SamedayConstants::TEXT_DOMAIN).'<span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" name="samedaycourier-service-name" style="width: 297px; height: 36px;" ' . $greyedOut . ' id="samedaycourier-service-name" value="'.esc_html($serviceName).'">
                             </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"> 
                                <label for="samedaycourier-price">  '.__('Price', SamedayConstants::TEXT_DOMAIN).'<span style="color: #ff2222"> * </span> </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" name="samedaycourier-price" step="any" style="width: 297px; height: 36px;" id="samedaycourier-price" value="'.esc_attr((string) ($service->getPrice() ?? '')).'"> 
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"> 
                                <label for="samedaycourier-free-delivery-price">  '.__('Free delivery price', SamedayConstants::TEXT_DOMAIN).' </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="number" name="samedaycourier-free-delivery-price" step="any" style="width: 297px; height: 36px;" id="samedaycourier-free-delivery-price" value="'.esc_attr((string) ($service->getPriceFree() ?? '')).'"> 
                            </td>
                        </tr>
                       <tr valign="top">
                            <th scope="row"> 
                                <label for="samedaycourier-status">  '.__('Status', SamedayConstants::TEXT_DOMAIN).'<span style="color: #ff2222"> * </span> </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select name="samedaycourier-status" style="width: 297px; height: 36px;" id="samedaycourier-status">
                                    '.$statuses.'
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><button class="sameday_admin_button" type="submit" value="Submit" >  '.__('Edit Service', SamedayConstants::TEXT_DOMAIN).'</button> </th>
                        </tr>
                     </tbody>
                </table>
            </form>
        ';
    }

	/**
	 * Screen options
	 */
	public function screen_option() {

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
	public static function get_instance(): self
    {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
