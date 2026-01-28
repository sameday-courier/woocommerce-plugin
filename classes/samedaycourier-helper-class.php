<?php

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\CityObject;
use Sameday\Objects\CountyObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;

if (! defined( 'ABSPATH' )) {
	exit;
}

class SamedayCourierHelperClass
{
	public const TRANSIENT_CACHE_KEY_FOR_CITIES = 'sameday_cities';
	public const DEFAULT_VALUE_LOCKER_MAX_ITEMS = 5;
	public const CASH_ON_DELIVERY = 'cod';
	public const LOCKER_NEXT_DAY_CODE = "LN";
    public const SAMEDAY_6H_CODE = "6H";
    public const STANDARD_24H_CODE = "24";
    public const STANDARD_CROSSBORDER_CODE = "XB";
    public const LOCKER_CROSSBORDER_CODE = "XL";
    public const PUDO_CODE = "PP";

    public const OOH_TYPES = [
        0 => self::LOCKER_NEXT_DAY_CODE,
        1 => self::PUDO_CODE,
    ];

    public const OOH_SERVICES = [
        self::LOCKER_NEXT_DAY_CODE,
        self::LOCKER_CROSSBORDER_CODE,
        self::PUDO_CODE,
    ];

    public const IN_USE_SERVICES = [
        self::SAMEDAY_6H_CODE,
        self::STANDARD_24H_CODE,
        self::LOCKER_NEXT_DAY_CODE,
        self::STANDARD_CROSSBORDER_CODE,
        self::LOCKER_CROSSBORDER_CODE,
    ];

    public const SAMEDAY_OOH_LABEL = 'Out of home delivery';

    public const OOH_SERVICES_LABELS = [
        self::API_HOST_LOCALE_RO => 'Ridicare Sameday Point/Easybox',
        self::API_HOST_LOCALE_BG => 'вземете от Sameday Point/Easybox',
        self::API_HOST_LOCALE_HU => 'felvenni től Sameday Point/Easybox',
    ];

    public const ELIGIBLE_SERVICES = [
        self::SAMEDAY_6H_CODE,
        self::STANDARD_24H_CODE,
        self::LOCKER_NEXT_DAY_CODE
    ];

    public const CROSSBORDER_ELIGIBLE_SERVICES = [
        self::STANDARD_CROSSBORDER_CODE,
        self::LOCKER_CROSSBORDER_CODE,
    ];

    public const ELIGIBLE_TO_6H_SERVICE = [
        'Bucuresti'
    ];

	public const PERSONAL_DELIVERY_OPTION_CODE = 'PDO';
	public const OPEN_PACKAGE_OPTION_CODE = 'OPCG';

	public const POST_META_SAMEDAY_SHIPPING_LOCKER = '_sameday_shipping_locker_id';
	public const POST_META_SAMEDAY_SHIPPING_HD_ADDRESS = '_sameday_shipping_hd_address';

    public const OOH_POPUP_TITLE = [
        self::API_HOST_LOCALE_RO => 'Optiunea Ridicare Personala include ambele servicii LockerNextDay, respectiv Pudo!',
        self::API_HOST_LOCALE_BG => 'Тази опция включва LockerNextDay и PUDO!',
        self::API_HOST_LOCALE_HU => 'Ez az opció magában foglalja a LockerNextDay és a PUDO szolgáltatást is!',
    ];

    public const CURRENCY_MAPPER = [
        self::API_HOST_LOCALE_RO => 'RON',
        self::API_HOST_LOCALE_BG => 'BGN',
        self::API_HOST_LOCALE_HU => 'HUF',
    ];

    public const EURO_CURRENCY = "EUR";

	public const TOGGLE_HTML_ELEMENT = [
		'show' => 'showElement',
		'hide' => 'hideElement',
	];

	public const API_PROD = 0;
	public const API_DEMO = 1;

	public const API_HOST_LOCALE_RO = 'RO';
	public const API_HOST_LOCALE_HU = 'HU';
	public const API_HOST_LOCALE_BG = 'BG';

	public const EAWB_INSTANCES = [
		self::API_HOST_LOCALE_RO => 'https://eawb.sameday.ro',
		self::API_HOST_LOCALE_HU => 'https://eawb.sameday.hu',
		self::API_HOST_LOCALE_BG => 'https://eawb.sameday.bg',
	];

	public const TEXT_DOMAIN = 'samedaycourier-shipping';

	private const ORDER_BY_TYPES = [
		'ASC',
		'DESC',
	];

    public const DEFAULT_COUNTRIES = [
        self::API_HOST_LOCALE_RO => ['value' => 187, 'label' => 'Romania'],
        self::API_HOST_LOCALE_BG => ['value' => 34, 'label' => 'Bulgaria'],
        self::API_HOST_LOCALE_HU => ['value' => 237, 'label' => 'Hungary'],
    ];

	public static function getSamedaySettings(): array
	{
		if (false === get_option('woocommerce_samedaycourier_settings')) {
			return [];
		}

		return get_option('woocommerce_samedaycourier_settings');
	}

	public static function getPathToSettingsPage(): string
	{
		return admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=samedaycourier';
	}

	public static function getEnvModes(): array
	{
		return [
			self::API_HOST_LOCALE_RO => [
				self::API_PROD => 'https://api.sameday.ro',
				self::API_DEMO => 'https://sameday-api.demo.zitec.com',
			],
			self::API_HOST_LOCALE_HU => [
				self::API_PROD => 'https://api.sameday.hu',
				self::API_DEMO => 'https://sameday-api-hu.demo.zitec.com',
			],
			self::API_HOST_LOCALE_BG => [
				self::API_PROD => 'https://api.sameday.bg',
				self::API_DEMO => 'https://sameday-api-bg.demo.zitec.com',
			],
		];
	}

	/**
	 * @return bool
	 */
	public static function isApplyFreeShippingAfterDiscount(): bool
	{
		$discountFreeShipping = self::getSamedaySettings()['discount_free_shipping'] ?? null;

		return ! (null === $discountFreeShipping || 'no' === $discountFreeShipping);
	}

	/**
	 * @return bool
	 */
	public static function isUseSamedayNomenclator(): bool
	{
		$useSamedayNomenclator = self::getSamedaySettings()['use_nomenclator'] ?? null;

		return ! (null === $useSamedayNomenclator || 'no' === $useSamedayNomenclator);
	}

	/**
	 * @return int
	 */
	public static function isTesting(): int
	{
		$isTesting = self::getSamedaySettings()['is_testing'] ?? null;

		return ($isTesting === 'yes' || $isTesting === '1') ? 1 : 0;
	}

	/**
	 * @return string
	 */
	public static function getHostCountry(): string
	{
		// The default will always be RO
		return self::getSamedaySettings()['host_country'] ?? self::API_HOST_LOCALE_RO;
	}

	/**
	 * @return string
	 */
	public static function getApiUrl(): string
	{
		return self::getEnvModes()[self::getHostCountry()][self::isTesting()];
	}

	/**
	 * @return array
	 */
	public static function getPackageTypeOptions(): array
	{
		return array(
			array(
				'name' => __("Parcel", self::TEXT_DOMAIN),
				'value' => PackageType::PARCEL
			),
			array(
				'name' => __("Envelope", self::TEXT_DOMAIN),
				'value' => PackageType::ENVELOPE
			),
			array(
				'name' => __("Large package", self::TEXT_DOMAIN),
				'value' => PackageType::LARGE
			)
		);
	}

	public static function getAwbPaymentTypeOptions(): array
	{
		return array(
			array(
				'name' => __("Client", self::TEXT_DOMAIN),
				'value' => AwbPaymentType::CLIENT
			)
		);
	}

	/**
	 * @param $countryCode
	 * @param $stateCode
	 *
	 * @return string
	 */
	public static function convertStateCodeToName($countryCode, $stateCode): string
	{
		if (! isset($countryCode, $stateCode) || ('' === $countryCode) || ('' === $stateCode)) {
			return '';
		}

		return html_entity_decode(WC()->countries->get_states()[$countryCode][$stateCode] ?? '');
	}

	public static function convertStateNameToCode($countryCode, $stateName): string
	{
		if (! isset($countryCode, $stateName) || ('' === $countryCode) || ('' === $stateName)) {
			return '';
		}

		$states = WC()->countries->get_states()[$countryCode];

		if ($states) {
			foreach ($states as $key => $value) {
				if (self::removeAccents($value) === self::removeAccents($stateName)) {
					return $key;
				}
			}
		}

		return '';
	}

	/**
	 * @param array $inputs
	 *
	 * @return array
	 */
	public static function sanitizeInputs(array $inputs): array
	{
		$data = [];
		foreach ($inputs as $key => $input) {
			if (is_int($input) || is_bool($input)) {
				$data[$key] = $input;
			}

			if (is_string($input)) {
				$data[$key] = self::sanitizeInput($input);
			}
		}

		return $data;
	}

    /**
     * @param array $locker
     *
     * @return string
     *
     */
    public static function sanitizeLocker(array $locker): string
    {
        if (!empty( $locker)) {
            foreach ($locker as $key => $value) {
                $locker[$key] = self::sanitizeInput($value);
            }
        }

        return json_encode($locker, JSON_UNESCAPED_UNICODE);
    }

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public static function sanitizeInput(string $input): string
	{
		return stripslashes(strip_tags(str_replace("'", '&#39;', $input)));
	}

	/**
	 * @param string $jsonString
	 *
	 * @return string
	 */
	public static function fixJson(string $jsonString): string
	{
		$pattern = '/(":\s*")([^"]*(?:"[^"]*)*?)("(?=\s*[,}\]]))/';

		return preg_replace_callback(
			$pattern,
			static function($matches) {
				return $matches[1] . str_replace('"', '\"', $matches[2]) . $matches[3];
			},
			$jsonString
		);
	}

	/**
	 * @param $orderId
	 *
	 * @return array|null
	 */
    public static function getShippingMethodSameday($orderId): ?array
    {
        $data = array();

        $shippingLines = wc_get_order($orderId)->get_data()['shipping_lines'];

        $serviceMethod = null;
        foreach ($shippingLines as $array) {
            $index = array_search($array, $shippingLines, true);
            $serviceMethod = $shippingLines[$index]->get_data()['method_id'];
        }

        if ($serviceMethod !== 'samedaycourier') {
            return null;
        }

        $awb = SamedayCourierQueryDb::getAwbForOrderId($orderId);

        if (!empty($awb)) {
            $data['awb_number'] = $awb->awb_number;
        }

        return $data;
    }

    /**
     * @param string $shippingMethodInput
     *
     * @return string
     */
    public static function parseShippingMethodCode(string $shippingMethodInput): string
    {
        $serviceCode = explode(":", $shippingMethodInput, 3);

        return $serviceCode[2] ?? '';
    }

    /**
     * @param array $errors
     *
     * @return string
     */
    public static function parseAwbErrors(array $errors): string
    {
        $allErrors = array();
        foreach ($errors as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $message) {
                    $allErrors[] = implode('.', $error['key']) . ': ' . $message;
                }
            } else {
                $allErrors[] = sprintf('%s : %s',
                    $error['code'] ?? 'Generic Error',
                    $error['message'] ?? 'Something went wrong'
                );
            }
        }

        return implode('<br/>', $allErrors);
    }

    /**
     * @param string $notice
     * @param string $notice_message
     * @param string $type
     * @param bool $dismissible
     *
     * @return void
     */
    public static function addFlashNotice(
		string $notice = "",
		string $notice_message = "",
		string $type = "warning",
		bool $dismissible = false
    ): void
    {
        update_option($notice, array(
                "message" => $notice_message,
                "type" => $type,
                "dismissible" => $dismissible
            )
        );
    }

    /**
     * @param $notice
     *
     * @return void
     */
    public static function showFlashNotice($notice): void
    {
        $notices = get_option($notice);
        if (! empty($notices)) {
            self::printFlashNotice($notices['type'], $notices['message'], $notices['dismissible']);

            // After show flash message in page, remove it from db.
            delete_option($notice);
        }
    }

    /**
     * @param $type
     * @param $dismissible
     * @param $message
     *
     * @return void
     */
    public static function printFlashNotice($type, $message, $dismissible): void
    {
        printf( '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $type,
            ($dismissible) ? "is-dismissible" : "",
            $message
        );
    }

    /**
     * @param $string
     *
     * @return string|string[]
     */
    public static function removeAccents($string)
    {
        $from = ['Ă', 'ă', 'Â', 'â', 'Î', 'î', 'Ș', 'ș', 'Ț', 'ț'];
        $to =   ['A', 'a', 'A', 'a', 'I', 'i', 'S', 's', 'T', 't'];

        return str_replace($from, $to, $string);
    }

	public static function buildGridQuery(
		string $tableName,
		bool $is_testing,
		array $filters,
		?int $perPage = null,
		?int $pageNumber = null
	): string
	{
		$sql = sprintf(
			"SELECT * FROM %s WHERE is_testing='%s' ",
            $tableName,
			$is_testing
		);

		$orderBy = $_REQUEST['orderby'] ?? null;
		$order = $_REQUEST['order'] ?? null;
		if (null !== $orderBy && in_array($orderBy, $filters, true)) {
			$sql .= sprintf(
				" ORDER BY %s ",
				esc_sql($orderBy)
			);
		}

		if (null !== $order && in_array(strtoupper($order), self::ORDER_BY_TYPES, true)) {
			$sql .= $order;
		}

        if (null !== $perPage && null !== $pageNumber) {
            $sql .= " LIMIT $perPage";
            $calculatePage = ($pageNumber - 1) * $perPage;
            $sql .= " OFFSET $calculatePage ";
        }

		return $sql;
	}

    /**
     * @param $orderId
     *
     * @param $locker
     *
     * @return void
     * @throws JsonException
     */
	public static function addLockerToOrderData($orderId, $locker): void
	{
        update_post_meta(
            $orderId,
            self::POST_META_SAMEDAY_SHIPPING_LOCKER,
            $locker,
            false
        );

        self::updateLockerOrderPostMeta($orderId);
	}

    /**
     * @param int $order_id
     *
     * @return void
     *
     * @throws JsonException
     */
	public static function updateLockerOrderPostMeta(int $order_id): void
	{
		$postMetaLocker = self::fixJson(
			self::sanitizeInput(
				(string) get_post_meta(
					$order_id,
					self::POST_META_SAMEDAY_SHIPPING_LOCKER,
					true
				)
			)
		);

        try {
            $lockerFields = json_decode($postMetaLocker, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) { return; }

        // If you don't use lockerMap but dropdown option
        if (!isset($lockerFields['name']) && !isset($lockerFields['city']) && !isset($lockerFields['county'])) {
            $lockerFields = SamedayCourierQueryDb::getLockerSameday($postMetaLocker, self::isTesting());

            if (null === $lockerFields) {
                return;
            }

            $lockerFields = (array) $lockerFields;
        }

		$postsMeta = $_POST;

		$shippingInputs = [];
		foreach ($postsMeta as $key => $value) {
            if (true === (bool) strpos("_" . $key, 'billing')) {
                $shippingInputs[sprintf("_%s", $key)] = $value ?? '';
            }
			if (true === (bool) strpos("_" . $key, 'shipping')) {
				$shippingInputs[sprintf("_%s", $key)] = $value ?? '';
			}
		}

		$country = $shippingInputs['shipping_country'] ?? $postsMeta['billing_country'] ?? self::getHostCountry();
		$firstName = $shippingInputs['shipping_first_name'] ?? $postsMeta['billing_first_name'] ?? '';
		$state = self::convertStateNameToCode(
			$country,
			$lockerFields['county']
		);

		self::updateAddressFields(
			$order_id,
			$lockerFields['address'],
			$lockerFields['name'],
			$firstName,
			$lockerFields['city'],
			$state,
			$lockerFields['postalCode'],
			$country
		);

		if ('' === self::getPostMetaSamedayShippingHDAddress($order_id)) {
            // Save HD Address
			update_post_meta(
				$order_id,
				self::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
				json_encode($shippingInputs, JSON_THROW_ON_ERROR),
				false
			);
		}
	}

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getPostMetaSamedayShippingHDAddress(int $orderId): string
    {
        return get_post_meta(
            $orderId,
            self::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            true
        );
    }

    /**
     * @param int $orderId
     *
     * @return ?array
     */
    public static function parsePostMetaSamedaycourierAddressHd(int $orderId): ?array
    {
        if ('' === $postMeta = self::getPostMetaSamedayShippingHDAddress($orderId)) {
            return null;
        }

        try {
            $postMeta = json_decode(
                $postMeta,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            return null;
        }

        $fieldsMapping = [
            'first_name',
            'last_name',
            'city',
            'state',
            'country',
            'postcode',
            'address_1',
            'address_2',
            'phone',
            'email',
            'method',
        ];

        $requiredFields = [
            'city',
            'state',
            'country',
            'postcode',
            'address_1',
            'address_2',
        ];

        $fields = [];
        foreach ($fieldsMapping as $field) {
            $fieldValue = $postMeta[sprintf("_shipping_%s", $field)]
                ?? ($postMeta[sprintf("_billing_%s", $field)] ?? null)
            ;

            $fields[$field] = $fieldValue;
        }

        foreach ($requiredFields as $field) {
            if (null === $fields[$field]) {
                $fields = null;
            }
        }

        return $fields;
    }

	/**
	 * @param $orderId
	 * @param $address1
	 * @param $address2
	 * @param $name
	 * @param $city
	 * @param $state
	 * @param $postalCode
	 * @param $country
	 *
	 * @return void
	 */
	public static function updateAddressFields(
		$orderId,
		$address1,
		$address2,
		$name,
		$city,
		$state,
		$postalCode,
		$country
	): void
	{
        $address1 = str_replace("\"", "", self::sanitizeInput($address1));
        $address2 = str_replace("\"", "", self::sanitizeInput($address2));
		$addressFieldsMapper = [
			'_shipping_address_1' => $address1,
			'_shipping_address_2' => $address2,
			'_shipping_city' => $city,
			'_shipping_state' => $state,
			'_shipping_postcode' => $postalCode,
			'_shipping_address_index' => sprintf(
				'%s %s %s %s %s %s %s',
				$name,
				$address1,
				$address2,
				$city,
				$state,
				$postalCode,
				$country
			)
		];

		foreach ($addressFieldsMapper as $key => $value) {
			update_post_meta($orderId, $key, $value, false);
		}

        SamedayCourierQueryDb::updateWcOrderAddress(
            $orderId,
            [
                'address_1' => $address1,
                'address_2' => $address2,
                'city' => $city,
                'state' => $state,
                'postcode' => $postalCode,
                'country' => $country,
            ]
        );
	}

    public static function convertWeight(float $weight): float
    {
        $weightUnit = get_option('woocommerce_weight_unit');

        switch ($weightUnit) {
            case 'g':
                return ($weight / 1000);
            case 'lbs':
                return ($weight * 0.45);
            case 'oz':
                return ($weight * 0.028);
            default:
                return $weight;
        }
    }

    public static function isOohDeliveryOption(string $samedayServiceCode): bool
    {
        return in_array($samedayServiceCode, self::OOH_SERVICES, true);
    }

    public static function isInUseServices(string $samedayServiceCode): bool
    {
        return in_array($samedayServiceCode, self::IN_USE_SERVICES, true);
    }

    /**
     * @return string
     */
    public static function getChosenShippingMethodCode(): string
    {
        if (null !== $chosenShippingMethod = WC()->session->get('chosen_shipping_methods')[0] ?? null) {
            return self::parseShippingMethodCode($chosenShippingMethod);
        }

        return '';
    }

	/**
	 * @param string $postalCode
	 * @param string $countyCode
	 *
	 * @return bool
	 */
	public static function validatePostalCode(string $postalCode, string $countyCode): bool
	{
		if (null === $code = SamedayCourierQueryDb::getPostalForSpecificCounty($countyCode, self::getHostCountry())) {
			return false;
		}

		if (mb_strlen($code) !== mb_strlen($postalCode)) {
			return false;
		}

		return $postalCode[0] === $code[0];
	}

    /**
     * @return array
     */
    public static function getCounties(): array
    {
        try {
            $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
                self::getSamedaySettings()['user'],
                self::getSamedaySettings()['password'],
                self::getApiUrl()
            ));
        } catch (SamedaySDKException|Exception $exception) {
            return [];
        }

        try{
            $samedayCounties = $sameday->getCounties(new Sameday\Requests\SamedayGetCountiesRequest(null))
                ->getCounties()
            ;
        } catch (Exception $e) {
            return [];
        }

        return array_map(static function(CountyObject $county){
            return ['id' => $county->getId(), 'name' => $county->getName()];
        }, $samedayCounties);
    }

    /**
     * @param $countyId
     *
     * @return array
     */
    public static function getCities($countyId): array {
        try {
            $sameday = new \Sameday\Sameday(SamedayCourierApi::initClient(
                self::getSamedaySettings()['user'],
	            self::getSamedaySettings()['password'],
	            self::getApiUrl()
            ));
        } catch (Exception $exception) {
            return [];
        }

        $page = 1;
	    $remoteCities = [];
        do {
            $request = new Sameday\Requests\SamedayGetCitiesRequest($countyId);
            $request->setPage($page++);

            try {
                $cities = $sameday->getCities($request);
            } catch (Exception $e) {
                return [];
            }

            foreach ($cities->getCities() as $city) {
                // Save as current sameday service.
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        if(!empty($remoteCities)){
            return array_map(static function(CityObject $city){
                return [
                    'id' => $city->getId(),
                    'name' => $city->getName()
                ];
            }, $remoteCities);
        }

        return [];
    }
}
