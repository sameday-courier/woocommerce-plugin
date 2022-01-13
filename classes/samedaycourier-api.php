<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Api
 */
class SamedayCourierApi
{
	/**
	 * @param null $username
	 * @param null $password
	 * @param null $is_testing
	 *
	 * @return \Sameday\SamedayClient
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 */
	static function initClient($username, $password, $is_testing)
	{
		return new \Sameday\SamedayClient(
			$username,
			$password,
			$is_testing ? 'https://sameday-api.demo.zitec.com' : 'https://api.sameday.ro',
			'WOOCOMMERCE ' . WC()->version,
			WC()->version,
			'curl',
			new PersistenceDataHander()
		);
	}
}