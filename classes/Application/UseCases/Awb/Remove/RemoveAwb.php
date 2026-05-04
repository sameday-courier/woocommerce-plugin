<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceVerifier;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class RemoveAwb
{
    private RemoveAwbRequest $removeAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(RemoveAwbRequest $removeAwbRequest)
    {
        $this->removeAwbRequest = $removeAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
    }

    /**
     * @return bool False when the user is not allowed; otherwise redirects and exits.
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): bool
    {
        $awb = $this->removeAwbRequest->getAwb();
        $nonce = $this->removeAwbRequest->getNonce();

        if (!UserPermissionChecker::hasAllowedRole() || !NonceVerifier::verify($nonce, 'remove-awb')) {
            return false;
        }

        $sameday = new Sameday(SdkInitiator::init());

        try {
            $sameday->deleteAwb(new SamedayDeleteAwbRequest((string) $awb->getAwbNumber()));
            $this->samedayAwbRepository->deleteAwbAndParcels($awb);
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
            NoticerHandler::addFlashNotice('remove_awb_notice', Helper::parseAwbErrors($errors), 'error', true);

            Redirector::to('post.php', [
                'post' => $awb->getOrderId(),
                'action' => 'edit',
                'remove-awb' => 'error',
            ]);
        }

        Redirector::to('post.php', [
            'post' => $awb->getOrderId(),
            'action' => 'edit',
            'remove-awb' => 'success',
        ]);
    }
}
