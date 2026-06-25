<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbResolutionFactory;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwb
{
    /**
     * @var GenerateAwbItem $awbItem
     */
    private GenerateAwbItem $awbItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var DbHandler $dbHandler
     */
    private DbHandler $dbHandler;

    /**
     * @var GenerateAwbValidator $validator
     */
    private GenerateAwbValidator $validator;

    /**
     * @var GenerateAwbResolutionFactory $resolutionFactory
     */
    private GenerateAwbResolutionFactory $resolutionFactory;

    public function __construct(GenerateAwbRequest $generateAwbRequest)
    {
        $this->awbItem = $generateAwbRequest->generateAwbItem;
        $this->sameday = $generateAwbRequest->sameday;
        $this->dbHandler = $generateAwbRequest->dbHandler;
        $this->samedayServiceRepository = $generateAwbRequest->samedayServiceRepository;
        $this->samedayAwbRepository = $generateAwbRequest->samedayAwbRepository;
        $this->validator = $generateAwbRequest->generateAwbValidator;
        $this->resolutionFactory = $generateAwbRequest->generateAwbResolutionFactory;
    }

    /**
     * @return GenerateAwbResponse
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    public function execute(): GenerateAwbResponse
    {
        $item = $this->awbItem;

        $validation = $this->validator->validate($item);
        if (!$validation->isValid()) {
            return new GenerateAwbResponse(
                implode('<br />', $validation->getErrors()),
                ResponseNoticeType::ERROR,
            );
        }

        $service = $this->samedayServiceRepository->getServiceSameday($item->getServiceId());

        $resolution = $this->resolutionFactory->create(
            GenerateAwbContextMapper::fromItem($item),
            $service
        );
        $recipient = $resolution->getRecipient();
        $oohDelivery = $resolution->getOohDelivery();

        $request = new SamedayPostAwbRequest(
            $item->getPickupPointId(),
            null,
            new PackageType($item->getPackageType()),
            $item->getParcelsDimensions(),
            $service->getSamedayId(),
            new AwbPaymentType($item->getAwbPayment()),
            new AwbRecipientEntityObject(
                $recipient->getCity(),
                $recipient->getCounty(),
                $recipient->getAddress(),
                $recipient->getName(),
                $recipient->getPhone(),
                $recipient->getEmail(),
                $recipient->getCompany(),
                $recipient->getPostalCode()
            ),
            $item->getInsuranceValue(),
            $item->getRepayment(),
            new CodCollectorType(CodCollectorType::CLIENT),
            null,
            $resolution->getServiceTaxIds(),
            null,
            $item->getClientReference(),
            $item->getObservation(),
            '',
            '',
            null,
            $oohDelivery->getLockerId(),
            null,
            $oohDelivery->getOohLastMile(),
            SamedayConstants::CURRENCY_MAPPER[$recipient->getCountry()]
        );

        $errors = null;
        $awb = null;
        try {
            $awb = $this->sameday->postAwb($request);
        } catch (SamedayBadRequestException $e) {
            $errors = $e->getErrors();
            if ($errors !== '') {
                try {
                    $rawResponse = $e->getRawResponse()->getBody();
                    $errorMessages = json_decode($rawResponse, false, 512, JSON_THROW_ON_ERROR)
                        ->errors
                        ->errors
                    ;
                    $errors[] = [
                        'key' => ['Validation Failed', ''],
                        'errors' => $errorMessages,
                    ];
                } catch (JsonException $exception) {
                    $errors[] = [
                        'key' => 'JSON Validation Failed',
                        'errors' => $exception->getMessage(),
                    ];
                }
            }
        } catch (SamedayOtherException $exception) {
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
            }

            if (null !== $parsedError = $error['error']) {
                $errors[] = $parsedError;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ('' === $message) {
                $message = 'The request could not be processed!';
            }
            $errors[] = [
                'code' => $e->getCode(),
                'message' => $message,
            ];
        }

        if (null !== $errors && null === $awb) {
            return new GenerateAwbResponse(
                $this->parseAwbErrors($errors),
                ResponseNoticeType::ERROR,
            );
        }

        $awbDetails = [
            'order_id' => $item->getOrderId(),
            'awb_number' => $awb->getAwbNumber(),
            'parcels' => serialize($awb->getParcels()),
            'awb_cost' => $awb->getCost(),
        ];

        $this->samedayAwbRepository->saveAwb($awbDetails);

        $samedayOrderItemId = null;
        $shippingLines = $item->getShippingLines();
        foreach ($shippingLines as $id => $shippingLine) {
            $samedayOrderItemId = $id;
            if (null !== $samedayOrderItemId) {
                break;
            }
        }

        $metas = [
            'service_id' => $service->getSamedayId(),
            'service_code' => $service->getSamedayCode(),
        ];

        try {
            Helper::updateAddressFields(
                $item->getOrderId(),
                $recipient->getAddress1(),
                $recipient->getAddress2(),
                $recipient->getName(),
                $recipient->getCity(),
                $recipient->getState(),
                $recipient->getPostalCode(),
                $recipient->getCountry()
            );
        } catch (Exception $exception) {}

        foreach ($metas as $key => $value) {
            $shippingLine->update_meta_data($key, $value);
        }
        $shippingLine->save_meta_data();

        $shippingLine->set_method_id('samedaycourier');
        $shippingLine->save();

        try {
            $this->dbHandler->updateRow(
                $this->dbHandler->buildTableName('woocommerce_order_items'),
                [
                    'order_item_name' => $service->getName() ?? $service->getSamedayName() ?? ''
                ],
                [
                    'order_item_id' => $samedayOrderItemId
                ]
            );
        } catch (Exception $exception) {
            return new GenerateAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GenerateAwbResponse(
            "Awb generated successfully.",
            ResponseNoticeType::SUCCESS,
        );
    }

    /**
     * @param array $errors
     *
     * @return string
     */
    private function parseAwbErrors(array $errors): string
    {
        $allErrors = array();
        foreach ($errors as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $message) {
                    $allErrors[] = implode('.', $error['key']) . ': ' . $message;
                }
            } else {
                $allErrors[] = sprintf('%s : %s',
                    $error['code'] ?? 'Generic Error',
                    $error['message'] ?? 'Something went wrong'
                );
            }
        }

        return implode('<br/>', $allErrors);
    }
}
