<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayOtherException;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Domain\Exceptions\AwbNotFoundForOrderException;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveAwb
{
    /**
     * @var RemoveAwbItem $removeAwbItem
     */
    private RemoveAwbItem $removeAwbItem;

    /**
     * @var AwbRemover $awbRemover
     */
    private AwbRemover $awbRemover;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @param RemoveAwbRequest $removeAwbRequest
     */
    public function __construct(
        RemoveAwbRequest $removeAwbRequest
    )
    {
        $this->removeAwbItem = $removeAwbRequest->getRemoveAwbItem();
        $this->awbRemover = $removeAwbRequest->getAwbRemover();
        $this->awbErrorParser = $removeAwbRequest->getAwbErrorParser();
    }

    /**
     * @return RemoveAwbResponse
     *
     * @throws JsonException
     */
    public function execute(): RemoveAwbResponse
    {
        try {
            $this->awbRemover->remove($this->removeAwbItem->getOrderId());
        } catch (AwbNotFoundForOrderException $exception) {
            return new RemoveAwbResponse(
                "Invalid or inexistent an AWB for this OrderID: {$exception->getOrderId()}",
                ResponseNoticeType::ERROR,
            );
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $errors[] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        if (isset($errors)) {

            return new RemoveAwbResponse(
                $this->awbErrorParser->parse($errors),
                ResponseNoticeType::ERROR,
            );
        }

        return new RemoveAwbResponse(
            "Awb removed with success.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
