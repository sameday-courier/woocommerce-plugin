<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Locker;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;
use SamedayCourier\Shipping\Utils\Helper;
use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Lockers extends WP_List_Table
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SamedayLockerRepository $samedayServiceRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

	/** Class constructor */
	public function __construct() {

		parent::__construct(
            [
                'singular' => __('Locker', SamedayConstants::TEXT_DOMAIN),
                'plural' => __('Lockers', SamedayConstants::TEXT_DOMAIN),
                'ajax' => false
		    ]
        );

        $this->dbHandler = new DbHandler();
        $this->samedayLockerRepository = new SamedayLockerRepository($this->dbHandler);
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
        $is_testing = Helper::isTesting();

        $sql = Helper::buildGridQuery(
            $this->samedayLockerRepository->getTableName(),
            $is_testing,
            self::ACCEPTED_FILTERS,
        );

        return $this->dbHandler->getRows($sql);
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
		_e('No lockers available!', SamedayConstants::TEXT_DOMAIN);
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
			'locker_id' => __('Locker ID', SamedayConstants::TEXT_DOMAIN),
			'name' => __('Name', SamedayConstants::TEXT_DOMAIN),
			'city' => __('City', SamedayConstants::TEXT_DOMAIN),
			'county' => __('County', SamedayConstants::TEXT_DOMAIN),
			'address' => __('Address', SamedayConstants::TEXT_DOMAIN),
			'lat' => __('Latitude', SamedayConstants::TEXT_DOMAIN),
			'lng' => __('Longitude', SamedayConstants::TEXT_DOMAIN),
			'postal_code' => __('Postal code', SamedayConstants::TEXT_DOMAIN)
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
