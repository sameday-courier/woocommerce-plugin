<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Common\AwbErrorParser;
use SamedayCourier\Shipping\Application\UseCases\Awb\Common\AwbRemover;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveAwb
{
    private RemoveAwbRequest $removeAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    public function __construct(RemoveAwbRequest $removeAwbRequest)
    {
        $this->removeAwbRequest = $removeAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
        $this->awbErrorParser = $removeAwbRequest->getAwbErrorParser();
    }

    /**
     * @return RemoveAwbResponse
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): RemoveAwbResponse
    {
        $awb = $this->removeAwbRequest->getAwb();
        $awbRemover = new AwbRemover(new Sameday(SdkInitiator::init()), $this->samedayAwbRepository);

        try {
            $awbRemover->remove($awb);
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
