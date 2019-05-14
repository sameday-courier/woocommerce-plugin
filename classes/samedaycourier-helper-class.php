<?php

if (! defined( 'ABSPATH' ) ) {
	exit;
}

class HelperClass
{
	/**
	 * @return array
	 */
	public static function getPackageTypeOptions()
	{
		return array(
			array(
				'name' => __("Parcel"),
				'value' => \Sameday\Objects\Types\PackageType::PARCEL
			),
			array(
				'name' => __("Envelope"),
				'value' => \Sameday\Objects\Types\PackageType::ENVELOPE
			),
			array(
				'name' => __("Large package"),
				'value' => \Sameday\Objects\Types\PackageType::LARGE
			)
		);
	}

	public static function getAwbPaymentTypeOptions()
	{
		return array(
			array(
				'name' => __("Client"),
				'value' => \Sameday\Objects\Types\AwbPaymentType::CLIENT
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getDays()
	{
		return array(
			array(
				'position' => 7,
				'text' => __('Sunday'),
			),
			array(
				'position' => 1,
				'text' => __('Monday'),
			),
			array(
				'position' => 2,
				'text' => __('Tuesday')
			),
			array(
				'position' => 3,
				'text' => __('Wednesday')
			),
			array(
				'position' => 4,
				'text' => __('Thursday')
			),
			array(
				'position' => 5,
				'text' => __('Friday')
			),
			array(
				'position' => 6,
				'text' => __('Saturday')
			)
		);
	}

	/**
	 * @param $countryCode
	 * @param $stateCode
	 *
	 * @return string
	 */
	public static function convertStateCodeToName($countryCode, $stateCode)
	{
		return html_entity_decode(WC()->countries->get_states()[$countryCode][$stateCode]);
	}

	/**
	 * @param $inputs
	 *
	 * @return array
	 */
	public static function sanitizeInputs($inputs)
	{
		$sanitizedInputs = array();
		foreach ($inputs as $key => $val) {
			$sanitizedInputs[$key] = strip_tags($val);
		}

		return $sanitizedInputs;
	}

	/**
	 * @return array|null
	 */
	public static function getShippingMethodSameday($orderId)
	{
		$data = array();

		$shippingLines = wc_get_order($orderId)->get_data()['shipping_lines'];

		$serviceMethod = null;
		foreach ($shippingLines as $array) {
			$index = array_search($array, $shippingLines);
			$serviceMethod = $shippingLines[$index]->get_data()['method_id'];
		}

		if ($serviceMethod !== 'samedaycourier') {
			return null;
		}

		$awb = getAwbForOrderId($orderId);

		if (!empty($awb)) {
			$data['awb_number'] = $awb->awb_number;
		}

		return $data;
	}
}