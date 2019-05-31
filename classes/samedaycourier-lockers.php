<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SamedayCourierLockers extends WP_List_Table
{
	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Locker', 'samedaycourier' ),
			'plural'   => __( 'Lockers', 'samedaycourier' ),
			'ajax'     => false
		] );
	}

	/**
	 * Retrieve lockers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_lockers( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$is_testing = get_option('woocommerce_samedaycourier_settings')['is_testing'] === 'yes' ? 1 : 0;

		$sql = "SELECT * FROM {$wpdb->prefix}sameday_locker WHERE is_testing=".$is_testing;

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

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}sameday_locker WHERE is_testing=".$is_testing;

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no lockers data is available */
	public function no_items() {
		_e( 'No lockers avaliable.', 'samedaycourier' );
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
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'locker_id' => __( 'Locker ID', 'samedaycourier' ),
			'name' => __( 'Name', 'samedaycourier' ),
			'city' => __( 'City', 'samedaycourier' ),
			'county' => __( 'County', 'samedaycourier' ),
			'address' => __( 'Address', 'samedaycourier' ),
			'lat' => __( 'Latitude', 'samedaycourier' ),
			'lng' => __( 'Longitude', 'samedaycourier' ),
			'postal_code' => __( 'Postal code', 'samedaycourier' )
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
		return array(
			'locker_id' => array(
				'locker_id',
				true
			)
		);
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items()
	{

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'lockers_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_lockers( $per_page, $current_page );
	}
}

