<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshService;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshServiceRequest;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class AllImportController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'all_import';

    /**
     * @var array<string, string>
     */
    private const SETTINGS_REDIRECT_ARGS = [
        'page' => 'wc-settings',
        'tab' => 'shipping',
        'section' => SamedayConstants::PLUGIN_NAME,
    ];

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'admin.php',
                self::SETTINGS_REDIRECT_ARGS
            );
        }

        /** @var array<int, array{label: string, message: string, manualImportHint: string}> $failedSteps */
        $failedSteps = [];

        $this->runStep(
            $failedSteps,
            'Services',
            TranslatorHandler::translate('Go to Services and use the Refresh button to import them manually.'),
            fn () => (new RefreshService(
                new RefreshServiceRequest($samedayApiClient, new SamedayServiceRepository())
            ))->execute(),
            'Failed to refresh services.'
        );

        $this->runStep(
            $failedSteps,
            'Pickup points',
            TranslatorHandler::translate('Go to Pickup-point and use the Refresh button to import them manually.'),
            fn () => (new RefreshPickupPoint(
                new RefreshPickupPointRequest($samedayApiClient, new SamedayPickupPointRepository())
            ))->execute(),
            'Failed to refresh pickup points.'
        );

        $this->runStep(
            $failedSteps,
            'Lockers',
            TranslatorHandler::translate('Go to Lockers and use the Refresh button to import them manually.'),
            fn () => (new RefreshLocker(
                new RefreshLockerRequest(new SamedayLockerRepository(), $samedayApiClient)
            ))->execute(),
            'Failed to refresh lockers.'
        );

        $dbHandler = new DbHandler();
        $this->runStep(
            $failedSteps,
            'Cities',
            TranslatorHandler::translate('Use the Import Cities button on this page to import them manually.'),
            fn () => (new RefreshCity(
                new RefreshCityRequest(
                    new SchemaHandler(),
                    $dbHandler,
                    new SamedayCityRepository($dbHandler),
                    new CacheHandler(),
                    new WooCountriesHandler(),
                )
            ))->execute(),
            'Failed to import cities.'
        );

        $this->dispatchNotice($failedSteps);
        Redirector::to(
            'admin.php',
            self::SETTINGS_REDIRECT_ARGS
        );
    }

    /**
     * @param array<int, array{label: string, message: string, manualImportHint: string}> $failedSteps
     * @param string $label
     * @param string $manualImportHint
     * @param callable(): ResponseInterface $execute
     * @param string $defaultErrorMessage
     *
     * @return void
     */
    private function runStep(
        array &$failedSteps,
        string $label,
        string $manualImportHint,
        callable $execute,
        string $defaultErrorMessage
    ): void {
        $result = $execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->recordFailure(
                $failedSteps,
                $label,
                $result->getNoticeMessage() ?? $defaultErrorMessage,
                $manualImportHint
            );
        }
    }

    /**
     * @param array<int, array{label: string, message: string, manualImportHint: string}> $failedSteps
     * @param string $label
     * @param string $message
     * @param string $manualImportHint
     *
     * @return void
     */
    private function recordFailure(
        array &$failedSteps,
        string $label,
        string $message,
        string $manualImportHint
    ): void {
        $failedSteps[] = [
            'label' => $label,
            'message' => $message,
            'manualImportHint' => $manualImportHint,
        ];
    }

    /**
     * @param array<int, array{label: string, message: string, manualImportHint: string}> $failedSteps
     *
     * @return void
     */
    private function dispatchNotice(array $failedSteps): void
    {
        if ([] === $failedSteps) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate('Process complete, all data is imported.'),
                ResponseNoticeType::SUCCESS,
            );

            return;
        }

        $failedLabels = array_map(
            static fn (array $failedStep): string => $failedStep['label'],
            $failedSteps
        );

        $message = TranslatorHandler::translate(
            sprintf(
                'Import process completed with errors. The following could not be imported: %s.',
                implode(', ', $failedLabels)
            )
        );

        foreach ($failedSteps as $failedStep) {
            $message .= sprintf(
                '<br><strong>%s:</strong> %s. %s',
                $failedStep['label'],
                TranslatorHandler::translate($failedStep['message']),
                $failedStep['manualImportHint']
            );
        }

        NoticerHandler::addFlashNotice(
            $message,
            ResponseNoticeType::ERROR,
        );
    }
}
