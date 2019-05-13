<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SamedayCourierService extends WP_List_Table
{
	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Service', 'samedaycourier' ),
			'plural'   => __( 'Services', 'samedaycourier' ),
			'ajax'     => false
		] );
	}

	/**
	 * Retrieve services data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_services( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$is_testing = get_option('woocommerce_samedaycourier_settings')['is_testing'] === 'yes' ? 1 : 0;

		$sql = "SELECT * FROM {$wpdb->prefix}sameday_service WHERE is_testing=".$is_testing;

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$is_testing = get_option('woocommerce_samedaycourier_settings')['is_testing'] === 'yes' ? 1 : 0;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}sameday_service WHERE is_testing=".$is_testing;

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no service data is available */
	public function no_items() {
		_e( 'No services avaliable.', 'samedaycourier' );
	}

	/**
	 * @return array
	 */
	private function getListOfStatuses()
	{
		return [
			'0' => 'Disabled',
			'1' => 'Always',
			'2' => 'Interval'
		];
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'status':
				return $this->getListOfStatuses()[$item[ $column_name ]];
			default:
				return $item[ $column_name ];
		}
	}

	function column_edit($item) {
		$actions = array(
			'edit' => sprintf('<a href="?post_type=page&page=%s&action=%s&id=%s">Edit</a>',$_REQUEST['page'],'edit', $item['id']),
		);

		$args = '<span class="dashicons dashicons-edit"></span>';

		return sprintf('%1$s %2$s', $args, $this->row_actions($actions));
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'sameday_id'    => __( 'Sameday ID', 'samedaycourier' ),
			'sameday_name' => __( 'Sameday name', 'samedaycourier' ),
			'name'    => __( 'Name', 'samedaycourier' ),
			'price'    => __( 'Price', 'samedaycourier' ),
			'price_free'    => __( 'Price free', 'samedaycourier' ),
			'status'    => __( 'Status', 'samedaycourier' ),
			'edit' => __('Edit', 'samedaycourier')
		];

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns()
	{
		$sortable_columns = array(
			'sameday_id' => array( 'sameday_id', true )
		);

		return $sortable_columns;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items()
	{

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'services_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_services( $per_page, $current_page );
	}
}
