<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Service;


if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\GridQueryBuilder;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\RequestSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;
use WP_List_Table;

if (!class_exists( 'WP_List_Table' )) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Services extends WP_List_Table
{
    /**
     * @var DbHandlerInterface
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayRepository;

	/**
     * Class constructor
     */
	public function __construct() {

		parent::__construct(
            [
                'singular' => TranslatorHandler::translate('Service'),
                'plural' => TranslatorHandler::translate('Services'),
                'ajax' => false
		    ]
        );

        $this->dbHandler = new DbHandler();
        $this->samedayRepository = new SamedayServiceRepository($this->dbHandler);
	}

	private const ACCEPTED_FILTERS = [
		'sameday_id'
	];

	private const GRID_PER_PAGE_VALUE = 10;

    /**
     * @param int $perPage
     * @param int $pageNumber
     *
     * @return array
     */
	private function getServices(int $perPage, int $pageNumber): array
	{
		$query = GridQueryBuilder::build(
			$this->samedayRepository->getTableName(),
			(new WordpressSamedaySettingsProvider())->get()->isTesting(),
			self::ACCEPTED_FILTERS,
			RequestSanitizer::getOrderBy(self::ACCEPTED_FILTERS),
			RequestSanitizer::getOrder(),
			$perPage,
			$pageNumber,
			'sameday_code',
			SamedayConstants::IN_USE_SERVICES,
		);

        $services = $this->dbHandler->getRows($query['sql'], $query['params']);

        foreach ($services as &$service) {
            if ($service['sameday_code'] === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
                $service['name'] = TranslatorHandler::translate(SamedayConstants::OOH_SERVICES_LABELS[(new WordpressSamedaySettingsProvider())->get()->getHostCountry()]);
                $service['sameday_name'] = TranslatorHandler::translate(SamedayConstants::SAMEDAY_OOH_LABEL);
            }
        }

        return $services;
	}

    private function getServicesCount(): int
    {
        $query = GridQueryBuilder::buildCount(
            $this->samedayRepository->getTableName(),
            (new WordpressSamedaySettingsProvider())->get()->isTesting(),
            'sameday_code',
            SamedayConstants::IN_USE_SERVICES,
        );

        return (int) $this->dbHandler->getVar($query['sql'], $query['params']);
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
        echo TranslatorHandler::translate('No services available!');
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
            && $item[$column_name] === TranslatorHandler::translate(SamedayConstants::SAMEDAY_OOH_LABEL)
        ) {
            $title = SamedayConstants::OOH_POPUP_TITLE[(new WordpressSamedaySettingsProvider())->get()->getHostCountry()];
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
			'sameday_id'    => TranslatorHandler::translate('Sameday ID'),
			'sameday_name' => TranslatorHandler::translate('Sameday name'),
			'name'    => TranslatorHandler::translate('Name'),
			'price'    => TranslatorHandler::translate('Price'),
			'price_free'    => TranslatorHandler::translate('Price free'),
			'status'    => TranslatorHandler::translate('Status'),
			'edit' => TranslatorHandler::translate('Edit')
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
		$current_page = $this->get_pagenum();

		$this->set_pagination_args(
			[
				'total_items' => $this->getServicesCount(),
				'per_page'    => $per_page,
			]
		);

		$this->items = $this->getServices($per_page, $current_page);
	}
}
