<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ApiRequestsHandler;
use SamedayCourier\Shipping\Utils\Helper;

class PickupPointInstance
{
	static $instance;

	public $pickuppoints_obj;

	public function __construct()
	{
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * @return void
	 */
    public function enqueue_scripts(string $hook): void
    {
        if (!isset($_GET['page']) || 'sameday_pickup_points' !== $_GET['page']) {
            return;
        }

	    add_thickbox();

        $pluginMainFile = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . 'samedaycourier-shipping.php';

        wp_enqueue_style(
            'sameday-thickboxform-style',
            plugins_url('assets/css/tickbox-form.css', $pluginMainFile),
            [],
            time()
        );
        wp_enqueue_script(
            'sameday-admin-helper',
            plugins_url('assets/js/helper.js', $pluginMainFile),
            ['jquery'],
            time(),
            true
        );
        wp_enqueue_script(
            'sameday-admin-script',
            plugins_url('assets/js/adminPickupPoints.js', $pluginMainFile),
            ['jquery'],
            time(),
            true
        );
    }

	public static function set_screen( $status, $option, $value )
	{
		return $value;
	}

	public function plugin_menu()
	{
		$hook = add_submenu_page(
			'',
			'SamedayCourier PickupPoint Table',
			'Sameday Pickup-points',
			'manage_options',
			'sameday_pickup_points',
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
						<div class="meta-box-sortables ui-sortable">
							<div>
                                <a href="<?php echo Helper::getPathToSettingsPage(); ?>" class="button-primary">
                                    <?php echo __('Back', SamedayConstants::TEXT_DOMAIN) ?>
                                </a>
								<form action="<?php echo admin_url('admin-post.php') ?>" method="post" style="width:fit-content; display:inline-block;top: -2px !important; position: relative;">
									<input type="hidden" name="action" value="refresh_pickup_points">
									<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('refresh_pickup_points')); ?>">
									<input type="submit" class="button-primary" value="Refresh Pickup point">
								</form>
                                <a href="#TB_inline?width=800&height=530&inlineId=smd-thickbox" class="button-primary button-samll thickbox">
                                    <?php echo __('Add Pickup Point', SamedayConstants::TEXT_DOMAIN) ?>
                                </a>
							</div>
							<form method="post">
								<?php
								$this->pickuppoints_obj->prepare_items();
								$this->pickuppoints_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
        <div id="smd-thickbox" class="smd-modal" style="display: none;">
            <div class="smd-modal-container">
                <form id="thickbox-form" data-url="send_pickup_point" method="POST">
                    <h3><?= __("Add New Pickup Point", SamedayConstants::TEXT_DOMAIN)?></h3>
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('send_pickup_point'); ?>">
                    <div class="form-group">
                        <label for="pickupPointCountry">Country</label>
                        <div class="form-input">
                            <select name="pickupPointCountry" id="pickupPointCountry">
                                <option value="<?php echo SamedayConstants::DEFAULT_COUNTRIES[Helper::getHostCountry()]['value']; ?>"><?php echo SamedayConstants::DEFAULT_COUNTRIES[Helper::getHostCountry()]['label']; ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointCounty"><?= __("County", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <select name="pickupPointCounty" id="pickupPointCounty" required data-url="">
                                <option><?= __("Choose City", SamedayConstants::TEXT_DOMAIN)?></option>
                                <?php foreach(ApiRequestsHandler::getCounties() as $county): ?>
                                    <option value="<?php echo $county['id']; ?>"><?php echo $county['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointCity"><?= __("City", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <select name="pickupPointCity" id="pickupPointCity" required disabled>

                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointAddress"><?= __("Address", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="text" name="pickupPointAddress" id="pickupPointAddress" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointDefault"><?= __("Default", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="checkbox" name="pickupPointDefault" id="pickupPointDefault" value="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointPostalCode"><?= __("Postal Code", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="number" name="pickupPointPostalCode" id="pickupPointPostalCode" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointAlias"><?= __("Alias", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="text" name="pickupPointAlias" id="pickupPointAlias" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointContactPersonName"><?= __("Contact Person Name", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="text" name="pickupPointContactPersonName" id="pickupPointContactPersonName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointContactPersonPhone"><?= __("Contact Person Phone", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="number" name="pickupPointContactPersonPhone" id="pickupPointContactPersonPhone" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pickupPointEmail"><?= __("Email", SamedayConstants::TEXT_DOMAIN)?></label>
                        <div class="form-input">
                            <input type="email" name="pickupPointEmail" id="pickupPointEmail" required>
                        </div>
                    </div>
                    <div class="form-footer">
                        <input type="submit" value="Save" class="button-primary">
                        <button class="button-secondary" onclick="tb_remove();">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="smd-thickbox-delete" class="smd-modal" style="display: none">
            <div class="smd-modal-container">
                <form id="form-deletePickupPoint" data-url="delete_pickup_point">
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('delete_pickup_point'); ?>">
                    <input type="hidden" name="sameday_id" id="input-deletePickupPoint">
                    <h3><?= __("Are you sure you want to delete this pickup point?", SamedayConstants::TEXT_DOMAIN)?></h3>
                    <div class="form-footer">
                        <input type="submit" name="submit" value="Submit">
                        <button class="button-secondary" onclick="tb_remove();">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
		<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option(): void
    {

		$option = 'per_page';
		$args   = [
			'label'   => 'pickuppoints',
			'default' => 5,
			'option'  => 'pickuppoints_per_page'
		];

		add_screen_option( $option, $args );

		$this->pickuppoints_obj = new PickupPoints();
	}

	/** Singleton instance */
	public static function get_instance(): self
    {
		if (!isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

