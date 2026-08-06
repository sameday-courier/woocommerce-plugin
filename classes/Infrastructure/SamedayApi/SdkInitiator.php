<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

if (!defined( 'ABSPATH')) {
    exit;
}

use Sameday\Exceptions\SamedaySDKException;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;

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
        if (null === $username) {
            $username = SamedaySettings::getUser();
        }
        if (null === $password) {
            $password = SamedaySettings::getPassword();
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
     * @return string
     */
    public static function getApiUrl(): string
    {
        return self::getEnvModes()[SamedaySettings::getHostCountry()][SamedaySettings::getTestingMode()];
    }
}