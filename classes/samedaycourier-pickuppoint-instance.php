<?php

class SamedayCourierPickupPointInstance
{
	// class instance
	static $instance;

	// Pickup-points WP_List_Table object
	public $pickuppoints_obj;

	// class constructor
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
		$hook = add_menu_page(
			'SamedayCourier PickupPoint Table',
			'Sameday Pickup-points',
			'manage_options',
			'sameday_pickup_points',
			[ $this, 'plugin_settings_page' ],
			'dashicons-admin-site'
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
								<form action="<?php echo admin_url('admin-post.php') ?>" method="post">
									<input type="hidden" name="action" value="refresh_pickup_points">
									<input type="submit" class="button-primary" value="Refresh Pickup point">
								</form>
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
		<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'pickuppoints',
			'default' => 5,
			'option'  => 'pickuppoints_per_page'
		];

		add_screen_option( $option, $args );

		$this->pickuppoints_obj = new SamedayCourierPickupPoints();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

