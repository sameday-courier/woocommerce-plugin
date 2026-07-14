<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Utils;

if (!defined( 'ABSPATH')) {
    exit;
}

use Exception;
use JsonException;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;

class Helper
{
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
		$discountFreeShipping = OptionsHandler::getSamedayOptions()['discount_free_shipping'] ?? null;

		return ! (null === $discountFreeShipping || 'no' === $discountFreeShipping);
	}

	/**
	 * @return bool
	 */
	public static function isUseSamedayNomenclator(): bool
	{
		$useSamedayNomenclator = OptionsHandler::getSamedayOptions()['use_nomenclator'] ?? null;

		return ! (null === $useSamedayNomenclator || 'no' === $useSamedayNomenclator);
	}

	/**
	 * @return bool
	 */
	public static function isTesting(): bool
	{
		$isTesting = OptionsHandler::getSamedayOptions()['is_testing'] ?? null;

		return $isTesting === 'yes' || $isTesting === '1';
	}

	/**
	 * @return string
	 */
	public static function getHostCountry(): string
	{
		// The default will always be RO
		return OptionsHandler::getSamedayOptions()['host_country'] ?? SamedayConstants::API_HOST_LOCALE_RO;
	}

	/**
	 * @return string
	 */
	public static function getApiUrl(): string
	{
		return self::getEnvModes()[self::getHostCountry()][self::isTesting()];
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

        $awb = (new SamedayAwbRepository)->getAwbForOrderId($orderId);

        if (null !== $awb && null !== $awb->getAwbNumber()) {
            $data['awb_number'] = $awb->getAwbNumber();
        }

        return $data;
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
			InputSanitizer::sanitizeInput(
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
            $lockerRepository = new SamedayLockerRepository();
            $lockerModel = $lockerRepository->getLockerSameday((int) $postMetaLocker);

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

		$postsMeta = InputSanitizer::sanitizeInputs($_POST);

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
		$state = WooStateCodeResolver::resolveFromName(
			$country,
			$lockerFields['county']
		);

		(new WooOrderShippingAddressUpdater(
			new WooOrderAddressRepository(),
		))->update(
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

}
