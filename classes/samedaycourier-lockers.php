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
			'singular' => __('Locker', SamedayCourierHelperClass::TEXT_DOMAIN),
			'plural'   => __('Lockers', SamedayCourierHelperClass::TEXT_DOMAIN),
			'ajax'     => false
		] );
	}

	private const GRID_PER_PAGE_VALUE = 10;

	private const ACCEPTED_FILTERS = [
		'locker_id'
	];

	/**
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return array|object|stdClass[]|null
	 */
	public static function get_lockers(
		int $per_page = self::GRID_PER_PAGE_VALUE,
		int $page_number = 1
	)
	{
		global $wpdb;

		$table = "{$wpdb->prefix}sameday_locker";
		$is_testing = SamedayCourierHelperClass::isTesting();

		$sql = SamedayCourierHelperClass::buildGridQuery(
			$table,
			$is_testing,
			self::ACCEPTED_FILTERS,
			$per_page,
			$page_number
		);

		return $wpdb->get_results($sql, 'ARRAY_A');
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count(): ?string
	{
		global $wpdb;

		$table = "{$wpdb->prefix}sameday_locker";
		$is_testing = SamedayCourierHelperClass::isTesting();

		$sql = sprintf(
			"SELECT COUNT(*) FROM %s WHERE is_testing='%s'",
			$table,
			$is_testing
		);

		return $wpdb->get_var($sql);
	}


	/** Text displayed when no lockers data is available */
	public function no_items(): void
	{
		_e('No lockers available!', SamedayCourierHelperClass::TEXT_DOMAIN);
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name )
	{
		return $item[$column_name];
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns(): array
	{
		return [
			'locker_id' => __('Locker ID', SamedayCourierHelperClass::TEXT_DOMAIN),
			'name' => __('Name', SamedayCourierHelperClass::TEXT_DOMAIN),
			'city' => __('City', SamedayCourierHelperClass::TEXT_DOMAIN),
			'county' => __('County', SamedayCourierHelperClass::TEXT_DOMAIN),
			'address' => __('Address', SamedayCourierHelperClass::TEXT_DOMAIN),
			'lat' => __('Latitude', SamedayCourierHelperClass::TEXT_DOMAIN),
			'lng' => __('Longitude', SamedayCourierHelperClass::TEXT_DOMAIN),
			'postal_code' => __('Postal code', SamedayCourierHelperClass::TEXT_DOMAIN)
		];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array
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
	public function prepare_items(): void
	{

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'lockers_per_page', self::GRID_PER_PAGE_VALUE);
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_lockers($per_page, $current_page);
	}
}

