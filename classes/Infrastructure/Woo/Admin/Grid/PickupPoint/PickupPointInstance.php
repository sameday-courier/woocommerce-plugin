<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\PickupPointForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

class PickupPointInstance
{
    public static $instance;

    public $pickuppoints_obj;

    /**
     */
    public function __construct()
    {
        add_filter('set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3);
        add_action('admin_menu', [ $this, 'plugin_menu' ]);
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
        $pageSlug = 'sameday_pickup_points';

        $hook = add_submenu_page(
            $parentSlug,
            'SamedayCourier PickupPoint Table',
            'Sameday Pickup-points',
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
                        <div class="meta-box-sortables ui-sortable">
                            <div class="sameday-settings-actions">
                                <a href="<?php echo UrlsHandler::buildSamedaySettingsPage(); ?>"
                                   class="sameday_admin_button">
                                    <?php echo TranslatorHandler::translate('Back') ?>
                                </a>
                                <form action="<?php echo admin_url('admin-post.php') ?>" method="post">
                                    <input type="hidden" name="action" value="refresh_pickup_points">
                                    <input type="hidden"
                                           name="_wpnonce"
                                           value="<?php echo esc_attr(wp_create_nonce('refresh_pickup_points')); ?>">
                                    <input type="submit" class="sameday_admin_button" value="Refresh Pickup point">
                                </form>
                                <a href="#"
                                   class="sameday_admin_button button-samll"
                                   role="button"
                                   data-sameday-modal-open="<?php echo esc_attr(PickupPointForm::ADD_MODAL_ID); ?>">
                                    <?php echo TranslatorHandler::translate('Add Pickup Point') ?>
                                </a>
                            </div>
                            <form method="get">
                                <input type="hidden" name="page" value="sameday_pickup_points" />
                                <?php
                                    $this->pickuppoints_obj->prepare_items();
                                    $this->pickuppoints_obj->render_request_preservation_fields();
                                    $this->pickuppoints_obj->views();
                                    $this->pickuppoints_obj->display();
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        PickupPointForm::renderModals();
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
            'label'   => 'pickuppoints',
            'default' => 5,
            'option'  => 'pickuppoints_per_page'
        ];

        add_screen_option($option, $args);

        $this->pickuppoints_obj = new PickupPoints();
    }

    /** Singleton instance */
    /**
     * @return mixed
     */
    public static function get_instance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
