<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Locker;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
class LockerInstance
{
	static $instance;

	public $lockers_obj;

	public function __construct()
	{
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}

	public static function set_screen( $status, $option, $value )
	{
		return $value;
	}

	public function plugin_menu()
	{
		$parentSlug = 'edit.php?post_type=page';
		$pageSlug = 'sameday_lockers';

		$hook = add_submenu_page(
			$parentSlug,
			'SamedayCourier Locker Table',
			'Sameday lockers',
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
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-3">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<div>
                                <a href="<?php echo SamedaySettings::getPathToSettingsPage(); ?>" class="sameday_admin_button">
									<?php echo TranslatorHandler::translate('Back') ?>
                                </a>
								<form action="<?php echo admin_url('admin-post.php') ?>" method="post" style="width:200px; display:inline-block;top: -2px !important; position: relative;">
									<input type="hidden" name="action" value="refresh_lockers">
									<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('refresh_lockers')); ?>">
									<input type="submit" class="sameday_admin_button" value="Refresh Lockers">
								</form>
							</div>
							<form method="post">
								<?php
									$this->lockers_obj->prepare_items();
									$this->lockers_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'lockers',
			'default' => 5,
			'option'  => 'lockers_per_page'
		];

		add_screen_option( $option, $args );

		$this->lockers_obj = new Lockers();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

