<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SamedayCourierLockers extends WP_List_Table
{
    private $tableName = 'sameday_locker';

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
     * @return array
     */
    private function getLockers(): array
    {
        global $wpdb;

        $table = $wpdb->prefix . $this->tableName;
        $is_testing = SamedayCourierHelperClass::isTesting();

        $sql = SamedayCourierHelperClass::buildGridQuery(
            $table,
            $is_testing,
            self::ACCEPTED_FILTERS,
        );

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * @param int $perPage
     * @param int $pageNumber
     *
     * @return array
     */
    private function buildGrid(
        int $perPage = self::GRID_PER_PAGE_VALUE,
        int $pageNumber = 1
    ): array
    {
        return array_chunk(
            $this->getLockers(),
            $perPage
        )[$pageNumber - 1] ?? [];
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
        $total_items  = count($this->getLockers());

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        $this->items = $this->buildGrid($per_page, $current_page);
	}
}

