<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

use \Sameday\PersistentData\SamedayPersistentDataInterface;
use \Sameday\SamedayClient;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;

class PersistenceHandler implements SamedayPersistentDataInterface
{
	public const KEYS = [
		SamedayClient::KEY_TOKEN => 'token',
		SamedayClient::KEY_TOKEN_EXPIRES => 'expires_at'
	];

	public function get($key)
	{
		return OptionsHandler::getOption("woocommerce_samedaycourier_settings_{$key}", [])[self::KEYS[$key]];
	}

	public function set($key, $value): void
	{
		OptionsHandler::setOption(
			'woocommerce_samedaycourier_settings_' . self::KEYS[$key],
			[self::KEYS[$key] => $value]
		);
	}
}