<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services\RenderAddAwbFormHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\ShowAsPdfAwbMapper;

final class RenderAddAwbFormController extends AbstractController
{
    private const ACTION = 'render-add-awb-form';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $params = new ShowAsPdfAwbMapper($inputParams);
        $result = (new RenderAddAwbFormHandler())->renderForOrderId($params->orderId());

        if (isset($result['error'])) {
            $this->sendJsonErrorResponse($result['error']);
        }

        $this->sendJsonSuccessResponse([
            'html' => $result['html'],
            'modalId' => $result['modalId'],
        ]);
    }
}
