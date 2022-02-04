<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

use \Sameday\PersistentData\SamedayPersistentDataInterface;
use \Sameday\SamedayClient;

class PersistenceDataHander implements SamedayPersistentDataInterface
{
	const KEYS = [
		SamedayClient::KEY_TOKEN => 'token',
		SamedayClient::KEY_TOKEN_EXPIRES => 'expires_at'
	];

	public function get($key)
	{
		return get_option("woocommerce_samedaycourier_settings_{$key}")[self::KEYS[$key]];
	}

	public function set($key, $value): void
	{
		update_option('woocommerce_samedaycourier_settings_' . self::KEYS[$key], [self::KEYS[$key] => $value]);
	}
}