<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Blocks;

use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerChoicesProvider;

/**
 * WooCommerce Checkout Blocks integration for EasyBox / locker selection.
 *
 * Classic checkout uses ShowLockerFieldAction + lockers_sync.js.
 * Blocks checkout has no PHP shipping template hooks, so the UI is injected via JS.
 */
final class CheckoutBlocksIntegration extends AbstractBlocksIntegration
{
    public const NAME = 'sameday-checkout-blocks';

    private const SCRIPT_HANDLE = 'sameday-blocks-checkout-locker';

    /**
     * Shared with JsScriptsHandler so the locker SDK is never loaded twice.
     */
    private const LOCKER_SDK_HANDLE = 'sameday-lockerpluginsdk';

    private const LOCKER_SCRIPT = 'assets/js/blocks-checkout-locker.js';

    /**
     * @var CarrierSettingsServiceProvider $carrierSettingsServiceProvider
     */
    private CarrierSettingsServiceProvider $carrierSettingsServiceProvider;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @var LockerChoicesProvider $lockerChoicesProvider
     */
    private LockerChoicesProvider $lockerChoicesProvider;

    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param LockerDtoFactory|null $lockerDtoFactory
     * @param LockerChoicesProvider|null $lockerChoicesProvider
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?LockerDtoFactory $lockerDtoFactory = null,
        ?LockerChoicesProvider $lockerChoicesProvider = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider
            ?? new CarrierSettingsServiceProvider();
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
        $this->lockerChoicesProvider = $lockerChoicesProvider ?? new LockerChoicesProvider();
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return self::NAME;
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->registerScript(
            self::LOCKER_SDK_HANDLE,
            CarrierConstants::LOCKER_PLUGIN_SDK_URL,
            [],
            null,
            false
        );

        $this->registerHelperScript();

        $this->registerPluginScript(
            self::SCRIPT_HANDLE,
            self::LOCKER_SCRIPT,
            ['jquery', 'wc-settings', self::HELPER_HANDLE, self::LOCKER_SDK_HANDLE]
        );
    }

    /**
     * @return string[]
     */
    public function get_script_handles(): array
    {
        return [self::SCRIPT_HANDLE];
    }

    /**
     * Exposed to JS as the `sameday-checkout-blocks_data` setting.
     *
     * @return array<string, mixed>
     */
    public function get_script_data(): array
    {
        if (is_admin()) {
            return [];
        }

        $settings = $this->carrierSettingsServiceProvider->get();
        $useLockerMap = $settings->isLockersMapEnabled();
        $locker = $this->resolveSelectedLocker();

        return [
            'username' => $settings->getUser() ?? '',
            'country' => $settings->getHostCountry(),
            'clientId' => CarrierConstants::LOCKER_PLUGIN_CLIENT_ID,
            'oohServiceCodes' => CarrierConstants::OOH_SERVICES,
            'useLockerMap' => $useLockerMap,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'syncAction' => 'refresh_lockers_checkout',
            'syncNonce' => wp_create_nonce('refresh_lockers_checkout'),
            'syncTtl' => CarrierConstants::LOCKERS_SYNC_TTL,
            'syncTs' => $settings->getSamedaySyncLockersTs(),
            'buttonText' => TranslatorHandler::translate('Show Locations Map'),
            'selectLockerText' => TranslatorHandler::translate('Select easyBox'),
            'loadingText' => TranslatorHandler::translate('Please wait for easyBox list to be populated'),
            'shipToText' => TranslatorHandler::translate('Ship to'),
            'errorText' => TranslatorHandler::translate('Please choose your EasyBox Locker !'),
            'pluginName' => CarrierConstants::PLUGIN_NAME,
            'selectedLocker' => null !== $locker ? $locker->toArray() : null,
            'lockersByCity' => $useLockerMap
                ? []
                : $this->lockerChoicesProvider->groupedByCity(
                    null !== $locker ? $locker->getLockerId() : null
                ),
        ];
    }

    /**
     * @return LockerDto|null
     */
    private function resolveSelectedLocker(): ?LockerDto
    {
        return $this->lockerDtoFactory->fromInput(
            $this->sessionHandler->get(CarrierSessionKeys::LOCKER)
        );
    }
}
