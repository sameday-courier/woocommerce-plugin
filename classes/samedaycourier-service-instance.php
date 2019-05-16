<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

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
        $service = SamedayCourierQueryDb::getService($id);

        if (! $service) {
	        WC_Admin_Settings::add_error('No service available !');
	        WC_Admin_Settings::show_messages(); exit;
        }

	    $statuses = '';
        foreach ($this->getStatuses() as $status) {
            $checked = $service->status == $status['value'] ? 'selected' : '';
	        $statuses .= '<option value="'.$status['value'].'" '.$checked.' >' . $status['text'] . '</option>';
        }

        $days = \SamedayCourierHelperClass::getDays();
        sort($days);

	    $intervals = '';
	    foreach ($days as $day) {
	        $working_days = unserialize($service->working_days);

		    $hFrom = $working_days["order_date_{$day['text']}_h_from"];
		    $mFrom = $working_days["order_date_{$day['text']}_m_from"];
		    $sFrom = $working_days["order_date_{$day['text']}_s_from"];

		    $hUntil = $working_days["order_date_{$day['text']}_h_until"];
		    $mUntil = $working_days["order_date_{$day['text']}_m_until"];
		    $sUntil = $working_days["order_date_{$day['text']}_s_until"];

		    $checked = isset($working_days["order_date_{$day['text']}_enabled"]) ? "checked" : "";

		    $intervals .= '
	            <tr valign="top" class="working_days" style="display: none">
                    <th scope="row"> 
                        <label for="samedaycourier-working-days"> ' . $day['text'] . ' <input type="checkbox" class="day" name="samedaycourier-working_days[order_date_'.$day['text'].'_enabled]" '.$checked.' value="'.$day['text'].'"> </label>
                    </th> 
                    <td class="forminp forminp-text">                                
                        <input type="number" style="width: 90px; height: 36px;" class="hour" placeholder="hh" name="samedaycourier-working_days[order_date_'.$day['text'].'_h_from]" min="0" max="23" step="1" value="'.$hFrom.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> :
                        <input type="number" style="width: 90px; height: 36px;" class="minutes" placeholder="mm" name="samedaycourier-working_days[order_date_'.$day['text'].'_m_from]" min="0" max="59" step="1" value="'.$mFrom.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> :
                        <input type="number" style="width: 90px; height: 36px;" class="seconds" placeholder="ss" name="samedaycourier-working_days[order_date_'.$day['text'].'_s_from]" min="0" max="59" step="1" value="'.$sFrom.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> <span><b> From </b></span>
                        <br/>
                        <input type="number" style="width: 90px; height: 36px;" class="hour" placeholder="hh" name="samedaycourier-working_days[order_date_'.$day['text'].'_h_until]" min="0" max="23" step="1" value="'.$hUntil.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> :
                        <input type="number" style="width: 90px; height: 36px;" class="minutes" placeholder="mm" name="samedaycourier-working_days[order_date_'.$day['text'].'_m_until]" min="0" max="59" step="1" value="'.$mUntil.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> :
                        <input type="number" style="width: 90px; height: 36px;" class="seconds" placeholder="ss" name="samedaycourier-working_days[order_date_'.$day['text'].'_s_until]" min="0" max="59" step="1" value="'.$sUntil.'" pattern="([01]?[0-9]{1}|2[0-3]{1})"> <span><b> Until </b></span>
                    </td>
                </tr>
	        ';
        }

        return
        '<strong style="font-size: large; color: #0A246A"> Edit Service - ' . $service->sameday_name . '</strong>
            <form method="POST" onsubmit="" action="'.admin_url('admin-post.php').'">
                <input type="hidden" name="action" value="edit_service">
                <table class="form-table editServiceForm">
                    <tbody>
                        <input type="hidden" name="samedaycourier-service-id" value="'.$id.'">
                        <tr valign="top">
                            <th scope="row" class="titledesc"> 
                                <label for="samedaycourier-service-name"> Service Name <span style="color: #ff2222"> * </span>  </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <input type="text" name="samedaycourier-service-name" style="width: 297px; height: 36px;" id="samedaycourier-service-name" value="'.$service->name.'">
                             </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"> 
                                <label for="samedaycourier-price"> Price <span style="color: #ff2222"> * </span> </label>
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
                                <label for="samedaycourier-status"> Status <span style="color: #ff2222"> * </span> </label>
                            </th> 
                            <td class="forminp forminp-text">
                                <select name="samedaycourier-status" style="width: 297px; height: 36px;" id="samedaycourier-status">
                                    '.$statuses.'
                                </select>
                            </td>
                        </tr>
                        '.$intervals.'
                        <tr>
                            <th><button class="button-primary" type="submit" value="Submit" > Edit Service </button> </th>
                        </tr>
                     </tbody>
                </table>
            </form>
            <script>
                jQuery(document).ready(function($) {
                    $(document).on("change", "#samedaycourier-status", function() { 
                        $("table.editServiceForm tr").filter(".working_days").hide();
                        if (parseInt($("#samedaycourier-status").val()) == 2) {                            
                            $("table.editServiceForm tr").filter(".working_days").show();
                        }                                         
                    });
                    $("#samedaycourier-status").trigger("change");
                    
                    $(document).on("click", ".day", function() {
                        var checked = $(this).is(":checked");
                        
                        $(this).closest("tr").children()[1].children[0].value = ""    
                        $(this).closest("tr").children()[1].children[1].value = ""
                        $(this).closest("tr").children()[1].children[2].value = ""  
                        $(this).closest("tr").children()[1].children[5].value = ""     
                        $(this).closest("tr").children()[1].children[6].value = ""  
                        $(this).closest("tr").children()[1].children[7].value = ""
                        
                        if (checked) {
                            $(this).closest("tr").children()[1].children[0].value = "00"    
                            $(this).closest("tr").children()[1].children[1].value = "00"
                            $(this).closest("tr").children()[1].children[2].value = "00"  
                            $(this).closest("tr").children()[1].children[5].value = "23"     
                            $(this).closest("tr").children()[1].children[6].value = "59"  
                            $(this).closest("tr").children()[1].children[7].value = "59"                      
                        } 
                    });                        
                });                
            </script>
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
