<?php

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

if (!defined( 'ABSPATH')) {
    exit;
}

use Sameday\Exceptions\SamedaySDKException;
use Sameday\SamedayClient;
use SamedayCourier\Shipping\Utils\Helper;

if (! defined( 'ABSPATH' ) ) {
	exit;
}

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
            $username = Helper::getSamedaySettings()['user'];
        }
        if (null === $password) {
            $password = Helper::getSamedaySettings()['password'];
        }
        if (null === $apiUrl) {
            $apiUrl = Helper::getApiUrl();
        }

        if (null === $username || null === $password || null === $apiUrl) {
            throw new SamedaySDKException("Please provide a valid credentials.");
        }

		return new SamedayClient(
			$username,
			$password,
			$apiUrl,
			'WOOCOMMERCE ' . WC()->version,
			WC()->version,
			'curl',
			new PersistenceHandler()
		);
	}
}