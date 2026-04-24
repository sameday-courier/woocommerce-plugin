<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Utils;

use Exception;
use JsonException;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;

if (!defined( 'ABSPATH' )) {
	exit;
}

class Helper
{
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
			SamedayConstants::API_HOST_LOCALE_RO => [
				SamedayConstants::API_PROD => 'https://api.sameday.ro',
				SamedayConstants::API_DEMO => 'https://sameday-api.demo.zitec.com',
			],
			SamedayConstants::API_HOST_LOCALE_HU => [
				SamedayConstants::API_PROD => 'https://api.sameday.hu',
				SamedayConstants::API_DEMO => 'https://sameday-api-hu.demo.zitec.com',
			],
			SamedayConstants::API_HOST_LOCALE_BG => [
				SamedayConstants::API_PROD => 'https://api.sameday.bg',
				SamedayConstants::API_DEMO => 'https://sameday-api-bg.demo.zitec.com',
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
		return self::getSamedaySettings()['host_country'] ?? SamedayConstants::API_HOST_LOCALE_RO;
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
				'name' => __("Parcel", SamedayConstants::TEXT_DOMAIN),
				'value' => PackageType::PARCEL
			),
			array(
				'name' => __("Envelope", SamedayConstants::TEXT_DOMAIN),
				'value' => PackageType::ENVELOPE
			),
			array(
				'name' => __("Large package", SamedayConstants::TEXT_DOMAIN),
				'value' => PackageType::LARGE
			)
		);
	}

    /**
     * @return array[]
     */
	public static function getAwbPaymentTypeOptions(): array
	{
		return array(
			array(
				'name' => __("Client", SamedayConstants::TEXT_DOMAIN),
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

    /**
     * @param string $countryCode
     * @param string $stateName
     *
     * @return string
     */
	public static function convertStateNameToCode(string $countryCode, string $stateName): string
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

        $awb = SamedayAwbRepository::getAwbForOrderId($orderId);

        if (null !== $awb && null !== $awb->getAwbNumber()) {
            $data['awb_number'] = $awb->getAwbNumber();
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

		if (null !== $order && in_array(strtoupper($order), SamedayConstants::ORDER_BY_TYPES, true)) {
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
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
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
					SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
					true
				)
			)
		);

        try {
            $lockerFields = json_decode($postMetaLocker, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) { return; }

        // If you don't use lockerMap but dropdown option
        if (!isset($lockerFields['name']) && !isset($lockerFields['city']) && !isset($lockerFields['county'])) {
            $lockerModel = SamedayLockerRepository::getLockerSameday((int) $postMetaLocker);

            if (null === $lockerModel) {
                return;
            }

            $lockerFields = [
                'name' => $lockerModel->getName(),
                'city' => $lockerModel->getCity(),
                'county' => $lockerModel->getCounty(),
                'address' => $lockerModel->getAddress(),
                'postalCode' => $lockerModel->getPostalCode(),
            ];
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
				SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
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
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
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

        WooOrderAddressRepository::updateWcOrderAddress(
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
        return in_array($samedayServiceCode, SamedayConstants::OOH_SERVICES, true);
    }

    public static function isInUseServices(string $samedayServiceCode): bool
    {
        return in_array($samedayServiceCode, SamedayConstants::IN_USE_SERVICES, true);
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
		if (null === $code = SamedayCityRepository::getPostalForSpecificCounty($countyCode, self::getHostCountry())) {
			return false;
		}

		if (mb_strlen($code) !== mb_strlen($postalCode)) {
			return false;
		}

		return $postalCode[0] === $code[0];
	}
}
