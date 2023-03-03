<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

class SamedayCourierHelperClass
{
	public const CASH_ON_DELIVERY = 'cod';

	public const LOCKER_NEXT_DAY_CODE = "LN";

	public const PERSONAL_DELIVERY_OPTION_CODE = 'PDO';

	public const OPEN_PACKAGE_OPTION_CODE = 'OPCG';

	public const TOGGLE_HTML_ELEMENT = [
		'show' => 'showElement',
		'hide' => 'hideElement',
	];

	public const API_PROD = 0;
	public const API_DEMO = 1;

	public const API_HOST_LOCALE_RO = 'RO';
	public const API_HOST_LOCAL_HU = 'HU';
	public const API_HOST_LOCAL_BG = 'BG';

	public const EAWB_INSTANCES = [
		self::API_HOST_LOCALE_RO => 'https://eawb.sameday.ro',
		self::API_HOST_LOCAL_HU => 'https://eawb.sameday.hu',
		self::API_HOST_LOCAL_BG => 'https://eawb.sameday.bg',
	];

	public const TEXT_DOMAIN = 'samedaycourier-shipping';

	private const ORDER_BY_TYPES = [
		'ASC',
		'DESC',
	];

	public static function getSamedaySettings(): array
	{
		if (false === get_option('woocommerce_samedaycourier_settings')) {
			return [];
		}

		return get_option('woocommerce_samedaycourier_settings');
	}

	public static function getEnvModes(): array
	{
		return [
			self::API_HOST_LOCALE_RO => [
				self::API_PROD => 'https://api.sameday.ro',
				self::API_DEMO => 'https://sameday-api.demo.zitec.com',
			],
			self::API_HOST_LOCAL_HU => [
				self::API_PROD => 'https://api.sameday.hu',
				self::API_DEMO => 'https://sameday-api-hu.demo.zitec.com',
			],
			self::API_HOST_LOCAL_BG => [
				self::API_PROD => 'https://api.sameday.bg',
				self::API_DEMO => 'https://sameday-api-bg.demo.zitec.com',
			],
		];
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
	public static function getPackageTypeOptions()
	{
		return array(
			array(
				'name' => __("Parcel", self::TEXT_DOMAIN),
				'value' => \Sameday\Objects\Types\PackageType::PARCEL
			),
			array(
				'name' => __("Envelope", self::TEXT_DOMAIN),
				'value' => \Sameday\Objects\Types\PackageType::ENVELOPE
			),
			array(
				'name' => __("Large package", self::TEXT_DOMAIN),
				'value' => \Sameday\Objects\Types\PackageType::LARGE
			)
		);
	}

	public static function getAwbPaymentTypeOptions()
	{
		return array(
			array(
				'name' => __("Client", self::TEXT_DOMAIN),
				'value' => \Sameday\Objects\Types\AwbPaymentType::CLIENT
			)
		);
	}

	/**
	 * @param $countryCode
	 * @param $stateCode
	 *
	 * @return string
	 */
	public static function convertStateCodeToName($countryCode, $stateCode)
	{
		return html_entity_decode(WC()->countries->get_states()[$countryCode][$stateCode]);
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

	public static function sanitizeInput(string $input): string
	{
		return stripslashes(strip_tags(str_replace("'", '&#39;', $input)));
	}

    /**
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
     */
    public static function parseShippingMethodCode(string $shippingMethodInput)
    {
        $serviceCode = explode(":", $shippingMethodInput, 3);

        return $serviceCode[2] ?? null;
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
            foreach ($error['errors'] as $message) {
                $allErrors[] = implode('.', $error['key']) . ': ' . $message;
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
		string $table,
		bool $is_testing,
		array $filters,
		int $per_page,
		int $page_number
	): string
	{
		$sql = sprintf(
			"SELECT * FROM %s WHERE is_testing='%s' ",
			$table,
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

		$sql .= " LIMIT $per_page";

		$calculatePage = ($page_number - 1) * $per_page;

		$sql .= " OFFSET $calculatePage ";

		return $sql;
	}
}
