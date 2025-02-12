<?php

use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Responses\SamedayPostPickupPointResponse;

if (! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SamedayCourierPickupPoints extends WP_List_Table
{
    /**
     * @var string $tableName
     */
    private $tableName = 'sameday_pickup_point';
    /**
     * @var mixed|null
     */
    private mixed $client;

    /** Class constructor */
	public function __construct()
	{
		parent::__construct( [
			'singular' => __('Pickup-point', SamedayCourierHelperClass::TEXT_DOMAIN),
			'plural'   => __('Pickup-points', SamedayCourierHelperClass::TEXT_DOMAIN),
			'ajax'     => false
		] );
	}

	private const ACCEPTED_FILTERS = [
		'sameday_id'
	];

	private const GRID_PER_PAGE_VALUE = 10;

    /**
     * @return SamedayPostPickupPointResponse
     */

    public function postPickupPoint(SamedayPostPickupPointRequest $request): SamedayPostPickupPointResponse
    {
        return new SamedayPostPickupPointResponse($request, $this->client->sendRequest($request->buildRequest()));
    }

	private function getPickupPoints(): array
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
            $this->getPickupPoints(),
            $perPage
        )[$pageNumber - 1] ?? [];
    }

	/** Text displayed when no pickup-points data is available */
	public function no_items(): void
	{
		__( 'No pickup-points avaliable.', SamedayCourierHelperClass::TEXT_DOMAIN);
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
		switch ($column_name) {
			case 'contactPersons':
				return $this->parseContactPersons(unserialize($item[$column_name], ['']));
			case 'default_pickup_point':
				return $item[$column_name] ? "<strong>Yes</strong>" : "No";
            case 'delete':
                return '<a href="#TB_inline?width=400&height=100&inlineId=smd-thickbox-delete" class="button-secondary delete-pickup-point thickbox" data-id="' . $item['sameday_id'] . '">Delete</a>';
			default:
				return $item[$column_name];
		}
	}

	/**
	 * @param $contactPersons
	 *
	 * @return string
	 */
	private function  parseContactPersons($contactPersons): string
	{
		$persons = array();
		foreach ($contactPersons as $contact_person) {
			$persons[] = "<strong>{$contact_person->getName()}</strong> <br/> {$contact_person->getPhone()}";
		}

		return implode(',', $persons);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns(): array
	{
		return [
			'sameday_id' => __('Sameday ID', SamedayCourierHelperClass::TEXT_DOMAIN),
			'sameday_alias' => __('Name', SamedayCourierHelperClass::TEXT_DOMAIN),
			'city' => __('City', SamedayCourierHelperClass::TEXT_DOMAIN),
			'county' => __('County', SamedayCourierHelperClass::TEXT_DOMAIN),
			'address' => __('Address', SamedayCourierHelperClass::TEXT_DOMAIN),
			'contactPersons' => __('Contact Persons', SamedayCourierHelperClass::TEXT_DOMAIN),
			'default_pickup_point' => __('Is default ', SamedayCourierHelperClass::TEXT_DOMAIN),
            'delete' => __('Actions', SamedayCourierHelperClass::TEXT_DOMAIN),
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

		$per_page     = $this->get_items_per_page( 'pickup-points_per_page', self::GRID_PER_PAGE_VALUE);
		$current_page = $this->get_pagenum();
		$total_items  = count($this->getPickupPoints());

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page,
		]);

		$this->items = $this->buildGrid($per_page, $current_page);
	}
}

