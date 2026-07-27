<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayConstants
{
    public const PLUGIN_NAME = 'samedaycourier';
    public const TRANSIENT_CACHE_KEY_FOR_CITIES = 'sameday_cities';
    public const DEFAULT_VALUE_LOCKER_MAX_ITEMS = 5;
    public const CASH_ON_DELIVERY = 'cod';
    public const LOCKER_NEXT_DAY_CODE = "LN";
    public const SAMEDAY_6H_CODE = "6H";
    public const STANDARD_24H_CODE = "24";
    public const STANDARD_CROSSBORDER_CODE = "XB";
    public const LOCKER_CROSSBORDER_CODE = "XL";
    public const PUDO_CODE = "PP";
    public const OOH_TYPES = [
        0 => self::LOCKER_NEXT_DAY_CODE,
        1 => self::PUDO_CODE,
    ];
    public const OOH_SERVICES = [
        self::LOCKER_NEXT_DAY_CODE,
        self::LOCKER_CROSSBORDER_CODE,
        self::PUDO_CODE,
    ];
    public const IN_USE_SERVICES = [
        self::SAMEDAY_6H_CODE,
        self::STANDARD_24H_CODE,
        self::LOCKER_NEXT_DAY_CODE,
        self::STANDARD_CROSSBORDER_CODE,
        self::LOCKER_CROSSBORDER_CODE,
    ];
    public const SAMEDAY_OOH_LABEL = 'Out of home delivery';
    public const OOH_SERVICES_LABELS = [
        self::API_HOST_LOCALE_RO => 'Ridicare Sameday Point/Easybox',
        self::API_HOST_LOCALE_BG => 'вземете от Sameday Point/Easybox',
        self::API_HOST_LOCALE_HU => 'felvenni től Sameday Point/Easybox',
    ];
    public const ELIGIBLE_SERVICES = [
        self::SAMEDAY_6H_CODE,
        self::STANDARD_24H_CODE,
        self::LOCKER_NEXT_DAY_CODE
    ];
    public const CROSSBORDER_ELIGIBLE_SERVICES = [
        self::STANDARD_CROSSBORDER_CODE,
        self::LOCKER_CROSSBORDER_CODE,
    ];
    public const ELIGIBLE_TO_6H_SERVICE = [
        'Bucuresti'
    ];
    public const PERSONAL_DELIVERY_OPTION_CODE = 'PDO';
    public const OPEN_PACKAGE_OPTION_CODE = 'OPCG';
    public const POST_META_SAMEDAY_SHIPPING_LOCKER = '_sameday_shipping_locker_id';
    public const POST_META_SAMEDAY_SHIPPING_HD_ADDRESS = '_sameday_shipping_hd_address';
    public const POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION = '_sameday_shipping_open_package_option';
    public const OOH_POPUP_TITLE = [
        self::API_HOST_LOCALE_RO => 'Optiunea Ridicare Personala include ambele servicii LockerNextDay, respectiv Pudo!',
        self::API_HOST_LOCALE_BG => 'Тази опция включва LockerNextDay и PUDO!',
        self::API_HOST_LOCALE_HU => 'Ez az opció magában foglalja a LockerNextDay és a PUDO szolgáltatást is!',
    ];
    public const CURRENCY_MAPPER = [
        self::API_HOST_LOCALE_RO => 'RON',
        self::API_HOST_LOCALE_BG => 'BGN',
        self::API_HOST_LOCALE_HU => 'HUF',
    ];
    public const EURO_CURRENCY = "EUR";
    public const TOGGLE_HTML_ELEMENT = [
        'show' => 'showElement',
        'hide' => 'hideElement',
    ];
    public const API_PROD = 0;
    public const API_DEMO = 1;
    public const API_HOST_LOCALE_RO = 'RO';
    public const API_HOST_LOCALE_HU = 'HU';
    public const API_HOST_LOCALE_BG = 'BG';
    public const EAWB_INSTANCES = [
        self::API_HOST_LOCALE_RO => 'https://eawb.sameday.ro',
        self::API_HOST_LOCALE_HU => 'https://eawb.sameday.hu',
        self::API_HOST_LOCALE_BG => 'https://eawb.sameday.bg',
    ];
    public const TEXT_DOMAIN = 'samedaycourier-shipping';
    public const ORDER_BY_TYPES = [
        'ASC',
        'DESC',
    ];
    public const DEFAULT_COUNTRIES = [
        self::API_HOST_LOCALE_RO => ['value' => 187, 'label' => 'Romania'],
        self::API_HOST_LOCALE_BG => ['value' => 34, 'label' => 'Bulgaria'],
        self::API_HOST_LOCALE_HU => ['value' => 237, 'label' => 'Hungary'],
    ];
}
