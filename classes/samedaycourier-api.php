<?php

use Sameday\Exceptions\SamedaySDKException;
use Sameday\SamedayClient;

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Api
 */
class SamedayCourierApi
{
	/**
	 * @param $username
	 * @param $password
	 * @param $apiUrl
	 *
	 * @return SamedayClient
	 * @throws SamedaySDKException
	 */
	public static function initClient($username, $password, $apiUrl): SamedayClient
	{
		return new SamedayClient(
			$username,
			$password,
			$apiUrl,
			'WOOCOMMERCE ' . WC()->version,
			WC()->version,
			'curl',
			new PersistenceDataHander()
		);
	}
}