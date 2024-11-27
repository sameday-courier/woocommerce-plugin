<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SamedayCourierService extends WP_List_Table
{
    private $tableName = "sameday_service";

    private $countRecords = 0;

	/**
     * Class constructor
     */
	public function __construct() {

		parent::__construct( [
			'singular' => __('Service', SamedayCourierHelperClass::TEXT_DOMAIN),
			'plural'   => __('Services', SamedayCourierHelperClass::TEXT_DOMAIN),
			'ajax'     => false
		] );
	}

	private const ACCEPTED_FILTERS = [
		'sameday_id'
	];

	private const GRID_PER_PAGE_VALUE = 10;

    /**
     * @return array
     */
	private function getServices(): array
	{
		global $wpdb;

		$sql = SamedayCourierHelperClass::buildGridQuery(
			$wpdb->prefix . $this->tableName,
            SamedayCourierHelperClass::isTesting(),
			self::ACCEPTED_FILTERS
		);

        $services = array_filter(
            (array) $wpdb->get_results($sql, 'ARRAY_A'),
            static function($service) {
                return SamedayCourierHelperClass::isInUseServices($service['sameday_code']);
            }
        );

        foreach ($services as &$service) {
            if ($service['sameday_code'] === SamedayCourierHelperClass::LOCKER_NEXT_DAY_CODE) {
                $service['name'] = __(
                    SamedayCourierHelperClass::OOH_SERVICES_LABELS[SamedayCourierHelperClass::getHostCountry()],
                    SamedayCourierHelperClass::TEXT_DOMAIN
                );
                $service['sameday_name'] = __(
                    SamedayCourierHelperClass::SAMEDAY_OOH_LABEL,
                    SamedayCourierHelperClass::TEXT_DOMAIN
                );
            }
        }

        return $services;
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
            $this->getServices(),
            $perPage
        )[$pageNumber - 1] ?? [];
    }

	/**
	 * @return array
	 */
	private function getListOfStatuses(): array
	{
		return [
			0 => 'Disabled',
			1 => 'Always',
		];
	}

    /**
     * Text displayed when no service data is available
     */
    public function no_items(): void
    {
        __( 'No services available!', SamedayCourierHelperClass::TEXT_DOMAIN);
    }

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name)
	{
        if ("status" === $column_name) {
            return $this->getListOfStatuses()[$item[$column_name]];
        }

        if (("sameday_name" === $column_name)
            && $item[$column_name] === __(
                SamedayCourierHelperClass::SAMEDAY_OOH_LABEL,
                SamedayCourierHelperClass::TEXT_DOMAIN
            )
        ) {
            $title = SamedayCourierHelperClass::OOH_POPUP_TITLE[SamedayCourierHelperClass::getHostCountry()];
            return sprintf(
                "<span style='font-weight: bolder; cursor: help;' title='%s'>%s</span>",
                $title,
                $item[$column_name]
            );
        }

        return $item[$column_name];
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
			'sameday_id'    => __('Sameday ID', SamedayCourierHelperClass::TEXT_DOMAIN),
			'sameday_name' => __('Sameday name', SamedayCourierHelperClass::TEXT_DOMAIN),
			'name'    => __('Name', SamedayCourierHelperClass::TEXT_DOMAIN),
			'price'    => __('Price', SamedayCourierHelperClass::TEXT_DOMAIN),
			'price_free'    => __('Price free', SamedayCourierHelperClass::TEXT_DOMAIN),
			'status'    => __('Status', SamedayCourierHelperClass::TEXT_DOMAIN),
			'edit' => __('Edit', SamedayCourierHelperClass::TEXT_DOMAIN)
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

		$per_page = $this->get_items_per_page('services_per_page', self::GRID_PER_PAGE_VALUE);

		$this->set_pagination_args(
			[
				'total_items' => count($this->getServices()),
				'per_page'    => $per_page,
			]
		);

		$this->items = $this->buildGrid($per_page, $this->get_pagenum());
	}
}
