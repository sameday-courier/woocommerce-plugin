<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\PickupPointForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\GridQueryBuilder;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\RequestSanitizer;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use WP_List_Table;

class PickupPoints extends WP_List_Table
{
    public const PAGE_SLUG = 'sameday_pickup_points';

    public const PARENT_POST_TYPE = 'page';

    /**
     * @var DbHandlerInterface $dbHandler
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct(
            [
                'singular' => TranslatorHandler::translate('Pickup-point'),
                'plural' => TranslatorHandler::translate('Pickup-points'),
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

    /**
     * Text displayed when no pickup-points data is available
     *
     * @return void
     */
    public function no_items()
    {
        echo TranslatorHandler::translate('No pickup-points available.');
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param mixed $item
     * @param mixed $column_name
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
                $modalId = esc_attr(PickupPointForm::DELETE_MODAL_ID);
                $samedayId = esc_attr((string) (int) $item['sameday_id']);

                return '<a href="#" class="sameday_admin_button delete-pickup-point" role="button"'
                    . ' data-sameday-modal-open="' . $modalId . '"'
                    . ' data-id="' . $samedayId . '">Delete</a>';
            default:
                return $item[$column_name];
        }
    }

    /**
     * @param mixed $contactPersons
     *
     * @return string
     */
    private function parseContactPersons($contactPersons): string
    {
        $persons = array();
        foreach ($contactPersons as $contact_person) {
            $persons[] = "<strong>{$contact_person->getName()}</strong> <br/> {$contact_person->getPhone()}";
        }

        return implode(',', $persons);
    }

    /**
     * Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        return [
            'sameday_id' => TranslatorHandler::translate('Sameday ID'),
            'sameday_alias' => TranslatorHandler::translate('Name'),
            'city' => TranslatorHandler::translate('City'),
            'county' => TranslatorHandler::translate('County'),
            'address' => TranslatorHandler::translate('Address'),
            'contactPersons' => TranslatorHandler::translate('Contact Persons'),
            'default_pickup_point' => TranslatorHandler::translate('Is default '),
            'delete' => TranslatorHandler::translate('Actions'),
        ];
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return array(
            'sameday_id' => array('sameday_id', true),
            'sameday_alias' => array('sameday_alias', true)
        );
    }

    /**
     * @return void
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $searchParams = $this->get_search_params();
        [$likeFilters, $exactFilters] = $this->buildSearchFilters($searchParams);

        $per_page = $this->get_items_per_page('pickuppoints_per_page', self::GRID_PER_PAGE_VALUE);
        $current_page = $this->get_pagenum();

        $countQuery = GridQueryBuilder::buildCount(
            $this->samedayPickupPointRepository->getTableName(),
            (new CarrierSettingsServiceProvider())->get()->isTesting(),
            null,
            null,
            self::ACCEPTED_SEARCH_COLUMNS,
            $likeFilters,
            $exactFilters,
        );

        $total_items = (int) $this->dbHandler->getVar($countQuery['sql'], $countQuery['params']);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        $dataQuery = GridQueryBuilder::build(
            $this->samedayPickupPointRepository->getTableName(),
            (new CarrierSettingsServiceProvider())->get()->isTesting(),
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
     * @param array $searchParams
     *
     * @return array{0:
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

    /**
     * @return array
     */
    public function get_search_params(): array
    {
        return [
            'search_sameday_id' => sanitize_text_field(wp_unslash($_GET['search_sameday_id'] ?? '')),
            'search_sameday_alias' => sanitize_text_field(wp_unslash($_GET['search_sameday_alias'] ?? '')),
            'search_city' => sanitize_text_field(wp_unslash($_GET['search_city'] ?? '')),
            'search_county' => sanitize_text_field(wp_unslash($_GET['search_county'] ?? '')),
            'search_address' => sanitize_text_field(wp_unslash($_GET['search_address'] ?? '')),
            'search_contactPersons' => sanitize_text_field(wp_unslash($_GET['search_contactPersons'] ?? '')),
            'search_default_pickup_point' => sanitize_text_field(
                wp_unslash($_GET['search_default_pickup_point'] ?? '')
            ),
        ];
    }

    /**
     * @return void
     */
    public function render_request_preservation_fields(): void
    {
        $searchParams = $this->get_search_params();

        if ('' !== $searchParams['search_default_pickup_point']) {
            printf(
                '<input type="hidden" name="search_default_pickup_point" value="%s" />',
                esc_attr($searchParams['search_default_pickup_point'])
            );
        }

        $orderBy = RequestSanitizer::getOrderBy(self::ACCEPTED_ORDER_BY_COLUMNS);
        $order = RequestSanitizer::getOrder();

        if (null !== $orderBy) {
            printf('<input type="hidden" name="orderby" value="%s" />', esc_attr($orderBy));
        }

        if (null !== $order) {
            printf('<input type="hidden" name="order" value="%s" />', esc_attr($order));
        }
    }

    /**
     * @return array
     */
    protected function get_views()
    {
        $searchParams = $this->get_search_params();
        $currentDefault = $searchParams['search_default_pickup_point'];

        return $this->get_views_links([
            'all' => [
                'url' => $this->get_filter_url(['search_default_pickup_point' => '']),
                'label' => TranslatorHandler::translate('All'),
                'current' => '' === $currentDefault,
            ],
            'yes' => [
                'url' => $this->get_filter_url(['search_default_pickup_point' => 'yes']),
                'label' => TranslatorHandler::translate('Default'),
                'current' => 'yes' === $currentDefault,
            ],
            'no' => [
                'url' => $this->get_filter_url(['search_default_pickup_point' => 'no']),
                'label' => TranslatorHandler::translate('No'),
                'current' => 'no' === $currentDefault,
            ],
        ]);
    }

    /**
     * @param mixed $which
     *
     * @return void
     */
    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }

        $searchParams = $this->get_search_params();
        $textFilters = [
            'search_sameday_id' => TranslatorHandler::translate('Sameday ID'),
            'search_sameday_alias' => TranslatorHandler::translate('Name'),
            'search_city' => TranslatorHandler::translate('City'),
            'search_county' => TranslatorHandler::translate('County'),
            'search_address' => TranslatorHandler::translate('Address'),
            'search_contactPersons' => TranslatorHandler::translate('Contact Persons'),
        ];
        ?>
        <div class="alignleft actions">
            <?php foreach ($textFilters as $name => $label) : ?>
                <label class="screen-reader-text" for="<?php echo $name; ?>">
                    <?php echo $label; ?>
                </label>
                <input
                    type="search"
                    id="<?php echo $name; ?>"
                    name="<?php echo $name; ?>"
                    value="<?php echo esc_attr($searchParams[$name]); ?>"
                    placeholder="<?php echo $label; ?>"
                />
            <?php endforeach; ?>

            <?php submit_button(
                TranslatorHandler::translate('Filter'),
                '',
                'filter_action',
                false,
                ['id' => 'pickup-point-query-submit']
            ); ?>

            <?php if ($this->has_active_filters()) : ?>
                <a href="<?php echo esc_url($this->get_admin_page_url()); ?>"
                   class="button">
                    <?php esc_html_e('Reset', CarrierConstants::TEXT_DOMAIN); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @return bool
     */
    private function has_active_filters(): bool
    {
        return (bool) array_filter($this->get_search_params());
    }

    /**
     * @param array $overrides
     *
     * @return string
     */
    private function get_filter_url(array $overrides = []): string
    {
        $args = [];

        foreach ($this->get_search_params() as $key => $value) {
            if ('' !== $value) {
                $args[$key] = $value;
            }
        }

        foreach ($overrides as $key => $value) {
            if ('' === $value) {
                unset($args[$key]);
                continue;
            }

            $args[$key] = $value;
        }

        $orderBy = RequestSanitizer::getOrderBy(self::ACCEPTED_ORDER_BY_COLUMNS);
        $order = RequestSanitizer::getOrder();

        if (null !== $orderBy) {
            $args['orderby'] = $orderBy;
        }

        if (null !== $order) {
            $args['order'] = $order;
        }

        return $this->get_admin_page_url($args);
    }

    /**
     * @param array $queryArgs
     *
     * @return string
     */
    private function get_admin_page_url(array $queryArgs = []): string
    {
        return UrlsHandler::build(
            'edit.php',
            array_merge(
                [
                    'post_type' => self::PARENT_POST_TYPE,
                    'page' => self::PAGE_SLUG,
                ],
                $queryArgs
            )
        );
    }
}

