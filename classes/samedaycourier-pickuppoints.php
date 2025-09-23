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

	/** Text displayed when no pickup-points data is available */
	public function no_items(): void
	{
		__( 'No pickup-points available.', SamedayCourierHelperClass::TEXT_DOMAIN);
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
			'sameday_id' => array('sameday_id', true),
            'sameday_alias' => array('sameday_alias', true)
		);
	}

    public function prepare_items(): void
    {
        $this->_column_headers = $this->get_column_info();

        // Get search parameters
        $search_params = $this->get_search_params();

        // Get filtered data
        $filtered_data = $this->getPickupPoints($search_params);

        $per_page     = $this->get_items_per_page( 'pickup-points_per_page', self::GRID_PER_PAGE_VALUE);
        $current_page = $this->get_pagenum();
        $total_items  = count($filtered_data);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // Apply pagination to filtered data
        $offset = ($current_page - 1) * $per_page;
        $this->items = array_slice($filtered_data, $offset, $per_page);
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

    // Update data query to accept search filters
    private function getPickupPoints($search_params = [])
    {
            global $wpdb;

            // Start with base query
            $where_conditions = [];
            $query_params = [];

            // Add testing condition
		    $where_conditions[] = "is_testing = %d";
		    $query_params[] = SamedayCourierHelperClass::isTesting();

            // Add search conditions
            if (!empty($search_params['search_sameday_id'])) {
                $where_conditions[] = "sameday_id LIKE %s";
                $query_params[] = '%' . $search_params['search_sameday_id'] . '%';
            }

            if (!empty($search_params['search_sameday_alias'])) {
                $where_conditions[] = "sameday_alias LIKE %s";
                $query_params[] = '%' . $search_params['search_sameday_alias'] . '%';
            }

            if (!empty($search_params['search_city'])) {
                $where_conditions[] = "city LIKE %s";
                $query_params[] = '%' . $search_params['search_city'] . '%';
            }

            if (!empty($search_params['search_county'])) {
                $where_conditions[] = "county LIKE %s";
                $query_params[] = '%' . $search_params['search_county'] . '%';
            }

            if (!empty($search_params['search_address'])) {
                $where_conditions[] = "address LIKE %s";
                $query_params[] = '%' . $search_params['search_address'] . '%';
            }

            if (!empty($search_params['search_contactPersons'])) {
                $where_conditions[] = "contactPersons LIKE %s";
                $query_params[] = '%' . $search_params['search_contactPersons'] . '%';
            }

            if (!empty($search_params['search_default_pickup_point'])) {
                if ($search_params['search_default_pickup_point'] === 'yes') {
                    $where_conditions[] = "default_pickup_point = 1";
                } elseif ($search_params['search_default_pickup_point'] === 'no') {
                    $where_conditions[] = "default_pickup_point = 0";
                }
            }

            // Build final query
            $sql = "SELECT * FROM " . $wpdb->prefix . $this->tableName;
            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            // Execute query
            if (!empty($query_params)) {
                $sql = $wpdb->prepare($sql, $query_params);
            }

            return $wpdb->get_results($sql, 'ARRAY_A');
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
                                    td.innerHTML = `
                                <select name="search_${columnKey}" onchange="searchTable()" style="width:100%;padding:4px;">
                                    <option value="">All</option>
                                    <option value="yes" ${currentValue === 'yes' ? 'selected' : ''}>Yes</option>
                                    <option value="no" ${currentValue === 'no' ? 'selected' : ''}>No</option>
                                </select>
                            `;
                                } else {
                                    const placeholder = {
                                        'sameday_id': 'ID...',
                                        'sameday_alias': 'Name...',
                                        'city': 'City...',
                                        'county': 'County...',
                                        'address': 'Address...',
                                        'contactPersons': 'Contact...'
                                    }[columnKey] || 'Search...';

                                    td.innerHTML = `
                                <input type="text"
                                       name="search_${columnKey}"
                                       value="${currentValue}"
                                       placeholder="${placeholder}"
                                       onkeypress="if(event.key==='Enter') searchTable()"
                                       style="width:100%;padding:4px;font-size:12px;">
                            `;
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
                        pageInput.value = '<?php echo esc_js($_GET['page'] ?? ''); ?>';
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
                    <a href="?page=<?php echo esc_attr($_GET['page']); ?>" class="button">Clear Search</a>
                <?php endif; ?>
            </div>
            <?php
        }
    }
}

