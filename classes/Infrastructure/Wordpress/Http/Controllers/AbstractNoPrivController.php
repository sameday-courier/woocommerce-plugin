<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\HandlesControllerAccessTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\JsonResponseTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;

abstract class AbstractNoPrivController implements ControllerInterface
{
    use JsonResponseTrait;
    use HandlesControllerAccessTrait;

    /**
     * @return void
     */
    public function handle(): void
    {
        $inputParams = InputSanitizer::sanitizeInputs($_POST);

        if (!NonceHandler::verify($inputParams['_wpnonce'] ?? '', $this->getAction())) {
            $this->denyAccess(
                TranslatorHandler::translate('Invalid nonce.')
            );
        }

        $this->processNoPrivAction($inputParams);
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    abstract protected function processNoPrivAction(array $inputParams): void;
}
