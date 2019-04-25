<?php

class SamedayCourierServiceInstance
{
	// class instance
	static $instance;

	// services WP_List_Table object
	public $services_obj;

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
			'SamedayCourier Service Table',
			'Sameday Services',
			'manage_options',
			'sameday_services',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );
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
									<input type="hidden" name="action" value="refresh_services">
									<input type="submit" class="button-primary" value="Refresh Services">
								</form>
							</div>
							<form method="post">
								<?php
								$this->services_obj->prepare_items();
								$this->services_obj->display();
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
			'label'   => 'services',
			'default' => 5,
			'option'  => 'services_per_page'
		];

		add_screen_option( $option, $args );

		$this->services_obj = new SamedayCourierService();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
