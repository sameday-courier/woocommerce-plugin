<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Application\Sql\GridQueryBuilder;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\RequestSanitizer;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;
use SamedayCourier\Shipping\Utils\Helper;
use WP_List_Table;

if (!class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class PickupPoints extends WP_List_Table
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /** Class constructor */
	public function __construct()
	{
		parent::__construct(
            [
                'singular' => __('Pickup-point', SamedayConstants::TEXT_DOMAIN),
                'plural' => __('Pickup-points', SamedayConstants::TEXT_DOMAIN),
                'ajax' => false
		    ]
        );

        $this->dbHandler = new DbHandler();
        $this->samedayPickupPointRepository = new SamedayPickupPointRepository($this->dbHandler);
	}

	private const GRID_PER_PAGE_VALUE = 10;

	private const ACCEPTED_ORDER_BY_COLUMNS = [
		'sameday_id',
		'sameday_alias',
	];

	private const ACCEPTED_SEARCH_COLUMNS = [
		'sameday_id',
		'sameday_alias',
		'city',
		'county',
		'address',
		'contactPersons',
		'default_pickup_point',
	];

	private const SEARCH_PARAM_TO_COLUMN = [
		'search_sameday_id' => 'sameday_id',
		'search_sameday_alias' => 'sameday_alias',
		'search_city' => 'city',
		'search_county' => 'county',
		'search_address' => 'address',
		'search_contactPersons' => 'contactPersons',
	];

	/** Text displayed when no pickup-points data is available */
	public function no_items(): void
	{
		__( 'No pickup-points available.', SamedayConstants::TEXT_DOMAIN);
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
                return '<a href="#TB_inline?width=400&height=100&inlineId=smd-thickbox-delete" class="sameday_admin_button delete-pickup-point thickbox" data-id="' . esc_attr((string) (int) $item['sameday_id']) . '">Delete</a>';
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
			'sameday_id' => __('Sameday ID', SamedayConstants::TEXT_DOMAIN),
			'sameday_alias' => __('Name', SamedayConstants::TEXT_DOMAIN),
			'city' => __('City', SamedayConstants::TEXT_DOMAIN),
			'county' => __('County', SamedayConstants::TEXT_DOMAIN),
			'address' => __('Address', SamedayConstants::TEXT_DOMAIN),
			'contactPersons' => __('Contact Persons', SamedayConstants::TEXT_DOMAIN),
			'default_pickup_point' => __('Is default ', SamedayConstants::TEXT_DOMAIN),
            'delete' => __('Actions', SamedayConstants::TEXT_DOMAIN),
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
			'sameday_id' => array('sameday_id', true),
            'sameday_alias' => array('sameday_alias', true)
		);
	}

    public function prepare_items(): void
    {
        $this->_column_headers = $this->get_column_info();

        $searchParams = $this->get_search_params();
        [$likeFilters, $exactFilters] = $this->buildSearchFilters($searchParams);

        $per_page = $this->get_items_per_page('pickup-points_per_page', self::GRID_PER_PAGE_VALUE);
        $current_page = $this->get_pagenum();

        $countQuery = GridQueryBuilder::buildCount(
            $this->samedayPickupPointRepository->getTableName(),
            Helper::isTesting(),
            null,
            null,
            self::ACCEPTED_SEARCH_COLUMNS,
            $likeFilters,
            $exactFilters,
        );

        $this->set_pagination_args([
            'total_items' => (int) $this->dbHandler->getVar($countQuery['sql'], $countQuery['params']),
            'per_page'    => $per_page,
        ]);

        $dataQuery = GridQueryBuilder::build(
            $this->samedayPickupPointRepository->getTableName(),
            Helper::isTesting(),
            self::ACCEPTED_ORDER_BY_COLUMNS,
            RequestSanitizer::getOrderBy(self::ACCEPTED_ORDER_BY_COLUMNS),
            RequestSanitizer::getOrder(),
            $per_page,
            $current_page,
            null,
            null,
            self::ACCEPTED_SEARCH_COLUMNS,
            $likeFilters,
            $exactFilters,
        );

        $this->items = $this->dbHandler->getRows($dataQuery['sql'], $dataQuery['params']);
    }

    /**
     * @param array<string, string> $searchParams
     *
     * @return array{0: array<string, string>, 1: array<string, int>}
     */
    private function buildSearchFilters(array $searchParams): array
    {
        $likeFilters = [];

        foreach (self::SEARCH_PARAM_TO_COLUMN as $searchKey => $column) {
            if ('' !== ($searchParams[$searchKey] ?? '')) {
                $likeFilters[$column] = $searchParams[$searchKey];
            }
        }

        $exactFilters = [];
        $defaultPickupPointFilter = $searchParams['search_default_pickup_point'] ?? '';

        if ('yes' === $defaultPickupPointFilter) {
            $exactFilters['default_pickup_point'] = 1;
        } elseif ('no' === $defaultPickupPointFilter) {
            $exactFilters['default_pickup_point'] = 0;
        }

        return [$likeFilters, $exactFilters];
    }

    // Add search parameter handling
    public function get_search_params(): array
    {
        return [
            'search_sameday_id' => sanitize_text_field($_GET['search_sameday_id'] ?? ''),
            'search_sameday_alias' => sanitize_text_field($_GET['search_sameday_alias'] ?? ''),
            'search_city' => sanitize_text_field($_GET['search_city'] ?? ''),
            'search_county' => sanitize_text_field($_GET['search_county'] ?? ''),
            'search_address' => sanitize_text_field($_GET['search_address'] ?? ''),
            'search_contactPersons' => sanitize_text_field($_GET['search_contactPersons'] ?? ''),
            'search_default_pickup_point' => sanitize_text_field($_GET['search_default_pickup_point'] ?? ''),
        ];
    }

    protected function extra_tablenav($which) {
        if ($which === 'top') {
            ?>
            <div class="alignleft actions">
                <script>
                    function addSearchToHeaders() {
                        const table = document.querySelector('.wp-list-table');
                        if (!table) return;

                        const headerRow = table.querySelector('thead tr');
                        if (!headerRow) return;

                        // Check if search row already exists
                        if (headerRow.nextElementSibling && headerRow.nextElementSibling.classList.contains('search-row')) {
                            return;
                        }

                        // Create search row
                        const searchRow = document.createElement('tr');
                        searchRow.classList.add('search-row');

                        const headers = headerRow.querySelectorAll('th');
                        headers.forEach((header) => {
                            const td = document.createElement('td');
                            const columnKey = header.id;

                            if (columnKey && columnKey !== 'cb' && columnKey !== 'delete') {
                                const currentValue = new URLSearchParams(window.location.search).get('search_' + columnKey) || '';

                                if (columnKey === 'default_pickup_point') {
                                    const select = document.createElement('select');
                                    select.name = 'search_' + columnKey;
                                    select.style.cssText = 'width:100%;padding:4px;';
                                    select.onchange = searchTable;

                                    ['', 'yes', 'no'].forEach(function(optionValue) {
                                        const option = document.createElement('option');
                                        option.value = optionValue;
                                        option.textContent = optionValue === '' ? 'All' : (optionValue === 'yes' ? 'Yes' : 'No');
                                        if (currentValue === optionValue) {
                                            option.selected = true;
                                        }
                                        select.appendChild(option);
                                    });

                                    td.appendChild(select);
                                } else {
                                    const placeholder = {
                                        'sameday_id': 'ID...',
                                        'sameday_alias': 'Name...',
                                        'city': 'City...',
                                        'county': 'County...',
                                        'address': 'Address...',
                                        'contactPersons': 'Contact...'
                                    }[columnKey] || 'Search...';

                                    const input = document.createElement('input');
                                    input.type = 'text';
                                    input.name = 'search_' + columnKey;
                                    input.value = currentValue;
                                    input.placeholder = placeholder;
                                    input.style.cssText = 'width:100%;padding:4px;font-size:12px;';
                                    input.onkeypress = function(event) {
                                        if (event.key === 'Enter') {
                                            searchTable();
                                        }
                                    };

                                    td.appendChild(input);
                                }
                            }

                            searchRow.appendChild(td);
                        });

                        // Insert search row after header row
                        headerRow.parentNode.insertBefore(searchRow, headerRow.nextSibling);
                    }

                    function searchTable() {
                        const form = document.createElement('form');
                        form.method = 'GET';
                        form.style.display = 'none';

                        // Add page parameter
                        const pageInput = document.createElement('input');
                        pageInput.name = 'page';
                        pageInput.value = <?php echo wp_json_encode(RequestSanitizer::getPageSlug()); ?>;
                        form.appendChild(pageInput);

                        // Add search parameters
                        document.querySelectorAll('.search-row input, .search-row select').forEach(input => {
                            if (input.value.trim() !== '') {
                                const searchInput = document.createElement('input');
                                searchInput.name = input.name;
                                searchInput.value = input.value;
                                form.appendChild(searchInput);
                            }
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }

                    // Add search fields when page loads
                    document.addEventListener('DOMContentLoaded', addSearchToHeaders);
                    // Also add when table is updated via AJAX
                    setTimeout(addSearchToHeaders, 100);
                </script>

                <?php if (array_filter($this->get_search_params())): ?>
                    <a href="?page=<?php echo esc_attr(RequestSanitizer::getPageSlug()); ?>" class="sameday_admin_button">Clear Search</a>
                <?php endif; ?>
            </div>
            <?php
        }
    }
}

