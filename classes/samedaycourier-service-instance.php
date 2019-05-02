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
		$hook = add_pages_page(
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
                        <?php if($_GET['action'] !== 'edit') { ?>
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
                        <?php } else { ?>
                        <div> <?php echo $this->createServiceForm( $_GET['id'] ); ?> </div>
                        <?php } {?>
                    </div>
                </div>
            </div>
        </div>
		<?php }
	}

	private function getStatuses()
	{
		return array(
			array(
				'value' => 0,
				'text' => __('Disabled')
			),
			array(
				'value' => 1,
				'text' => __('Always')
			),
			array(
				'value' => 2,
				'text' => __('Interval')
			)
		);
	}

	/**
	 * @param $id
	 */
	private function createServiceForm($id)
    {
        $service = getService($id);

        if (! $service) {
	        WC_Admin_Settings::add_error('No service available !');
	        WC_Admin_Settings::show_messages(); exit;
        }

	    $statuses = '';
        foreach ($this->getStatuses() as $status) {
            $checked = $service->status == $status['value'] ? 'selected' : '';
	        $statuses .= '<option value="'.$status['value'].'" '.$checked.' >' . $status['text'] . '</option>';
        }

        return

        '<strong style="font-size: large; color: #0A246A"> Edit Service - ' . $service->sameday_name . '</strong>
        <form method="POST" onsubmit="" action="'.admin_url('admin-post.php').'">
            <input type="hidden" name="action" value="edit_service">
            <table class="form-table">
                <tbody>
                    <input type="hidden" name="samedaycourier-service-id" value="'.$id.'">
                    <tr valign="top">
                        <th scope="row" class="titledesc"> 
                            <label for="samedaycourier-service-name"> <span style="color: #ff2222"> * </span> Service Name  </label>
                        </th> 
                        <td class="forminp forminp-text">
                            <input type="text" name="samedaycourier-service-name" style="width: 297px; height: 36px;" id="samedaycourier-service-name" value="'.$service->name.'">
                         </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"> 
                            <label for="samedaycourier-price"> <span style="color: #ff2222"> * </span> Price  </label>
                        </th> 
                        <td class="forminp forminp-text">
                            <input type="number" name="samedaycourier-price" style="width: 297px; height: 36px;" id="samedaycourier-price" value="'.$service->price.'"> 
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"> 
                            <label for="samedaycourier-free-delivery-price"> Free delivery price  </label>
                        </th> 
                        <td class="forminp forminp-text">
                            <input type="number" name="samedaycourier-free-delivery-price" style="width: 297px; height: 36px;" id="samedaycourier-free-delivery-price" value="'.$service->price_free.'"> 
                        </td>
                    </tr>
                   <tr valign="top">
                        <th scope="row"> 
                            <label for="samedaycourier-status"> <span style="color: #ff2222"> * </span> Status </label>
                        </th> 
                        <td class="forminp forminp-text">
                            <select name="samedaycourier-status" style="width: 297px; height: 36px;" id="samedaycourier-status">
                                '.$statuses.'
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><button class="button-primary" type="submit" value="Submit" > Edit Service </button> </th>
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
