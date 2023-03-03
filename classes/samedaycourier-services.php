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
			'singular' => __('Service', 'samedaycourier'),
			'plural'   => __('Services', 'samedaycourier'),
			'ajax'     => false
		] );
	}

	private const ACCEPTED_FILTERS = [
		'sameday_id'
	];

	private const GRID_PER_PAGE_VALUE = 10;

	/**
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return array
	 */
	public static function get_services(
		int $per_page = self::GRID_PER_PAGE_VALUE,
		int $page_number = 1
	): array
	{

		global $wpdb;

		$is_testing = SamedayCourierHelperClass::isTesting();
		$table = $wpdb->prefix . "sameday_service";

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

		$table = "{$wpdb->prefix}sameday_service";
		$is_testing = SamedayCourierHelperClass::isTesting();

		$sql = sprintf(
			"SELECT COUNT(*) FROM %s WHERE is_testing='%s'",
			$table,
			$is_testing
		);

		return $wpdb->get_var($sql);
	}


	/** Text displayed when no service data is available */
	public function no_items(): void
	{
		_e( 'No services available!','samedaycourier');
	}

	/**
	 * @return array
	 */
	private function getListOfStatuses(): array
	{
		return [
			0 => 'Disabled',
			1 => 'Always',
			2 => 'Interval',
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
	public function column_default( $item, $column_name )
	{
		switch ($column_name)
		{
			case 'status':
				return $this->getListOfStatuses()[$item[$column_name]];
			default:
				return $item[$column_name];
		}
	}

	public function column_edit($item): string
	{
		$actions = array(
			'edit' => sprintf(
				'<a href="?post_type=page&page=%s&action=%s&id=%s">Edit</a>','sameday_services',
				'edit',
				(int) $item['id']
			),
		);

		$args = '<span class="dashicons dashicons-edit"></span>';

		return sprintf('%1$s %2$s', $args, $this->row_actions($actions));
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns(): array
	{
		return [
			'sameday_id'    => __('Sameday ID', 'samedaycourier'),
			'sameday_name' => __('Sameday name', 'samedaycourier'),
			'name'    => __('Name', 'samedaycourier'),
			'price'    => __('Price', 'samedaycourier'),
			'price_free'    => __('Price free', 'samedaycourier'),
			'status'    => __('Status', 'samedaycourier'),
			'edit' => __('Edit', 'samedaycourier')
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
			'sameday_id' => array('sameday_id', true)
		);
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items(): void
	{

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'services_per_page', self::GRID_PER_PAGE_VALUE);
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			[
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page //WE have to determine how many items to show on a page
			]
		);

		$this->items = self::get_services($per_page, $current_page);
	}
}
