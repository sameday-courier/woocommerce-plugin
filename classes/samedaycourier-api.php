<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Api
 */
class Api
{
	/**
	 * @param null $username
	 * @param null $password
	 * @param null $is_testing
	 *
	 * @return \Sameday\SamedayClient
	 * @throws \Sameday\Exceptions\SamedaySDKException
	 */
	static function initClient( $username = null, $password = null, $is_testing = null )
	{
		return new \Sameday\SamedayClient(
			$username,
			$password,
			$is_testing ? 'https://sameday-api.demo.zitec.com' : 'https://api.sameday.ro',
			'WOOCOMMERCE ' . WC()->version,
			WC()->version
		);
	}
}