<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

/**
 * Class Api
 */
final class SdkInitiator
{
    /**
     * @param string|null $username
     * @param string|null $password
     * @param string|null $apiUrl
     *
     * @return SamedayClient
     * @throws SamedaySDKException
     */
	public static function init(
        ?string $username = null,
        ?string $password = null,
        ?string $apiUrl = null
    ): SamedayClient
	{
        $settings = (new CarrierSettingsServiceProvider())->get();
        if (null === $username) {
            $username = $settings->getUser();
        }
        if (null === $password) {
            $password = $settings->getPassword();
        }
        if (null === $apiUrl) {
            $apiUrl = self::getApiUrl();
        }

        if (null === $username || null === $password || null === $apiUrl) {
            throw new SamedaySDKException("Please provide a valid credentials.");
        }

        $wooHandler = new WooHandler();
        $platformVersion = $wooHandler->getPlatformVersion();

		return new SamedayClient(
			$username,
			$password,
			$apiUrl,
			'WOOCOMMERCE ' . $platformVersion,
			$platformVersion,
			'curl',
			new PersistenceHandler()
		);
	}

    /**
     * @return array<string, array<int, string>>
     */
    public static function getEnvModes(): array
    {
        return [
            CarrierConstants::API_HOST_LOCALE_RO => [
                CarrierConstants::API_PROD => 'https://api.sameday.ro',
                CarrierConstants::API_DEMO => 'https://sameday-api.demo.zitec.com',
            ],
            CarrierConstants::API_HOST_LOCALE_HU => [
                CarrierConstants::API_PROD => 'https://api.sameday.hu',
                CarrierConstants::API_DEMO => 'https://sameday-api-hu.demo.zitec.com',
            ],
            CarrierConstants::API_HOST_LOCALE_BG => [
                CarrierConstants::API_PROD => 'https://api.sameday.bg',
                CarrierConstants::API_DEMO => 'https://sameday-api-bg.demo.zitec.com',
            ],
        ];
    }

    /**
     * @return string
     */
    public static function getApiUrl(): string
    {
        $settings = (new CarrierSettingsServiceProvider())->get();

        return self::getEnvModes()[$settings->getHostCountry()][$settings->getTestingMode()];
    }
}