<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\SamedayApi;

use Exception;
use JsonException;
use RuntimeException;
use Sameday\Http\SamedayRawResponse;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use Sameday\Responses\SamedayGetParcelStatusHistoryResponse;
use Sameday\Sameday;

final class ParcelStatusHistoryService
{
    private const FALLBACK_STATUS_DATE = '1970-01-01 00:00:00';

    /**
     * @param Sameday $sameday
     * @param string $awbNumber
     *
     * @return SamedayGetParcelStatusHistoryResponse
     * @throws Exception
     */
    public function get(Sameday $sameday, string $awbNumber): SamedayGetParcelStatusHistoryResponse
    {
        $request = new SamedayGetParcelStatusHistoryRequest($awbNumber);
        $rawResponse = $sameday->getClient()->sendRequest($request->buildRequest());
        $body = $this->normalizeResponseBody($rawResponse->getBody(), $awbNumber);
        $patchedResponse = new SamedayRawResponse(
            $rawResponse->getHeaders(),
            $body,
            $rawResponse->getHttpStatusCode()
        );

        $response = new SamedayGetParcelStatusHistoryResponse($request, $patchedResponse);

        if (null === $response->getSummary()) {
            throw new RuntimeException(
                sprintf('Parcel status history response is empty for AWB %s.', $awbNumber)
            );
        }

        return $response;
    }

    /**
     * @param string $body
     * @param string $awbNumber
     *
     * @return string
     * @throws JsonException
     */
    private function normalizeResponseBody(string $body, string $awbNumber): string
    {
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($json)) {
            return $body;
        }

        $json['parcelSummary'] = $this->normalizeParcelSummary(
            is_array($json['parcelSummary'] ?? null) ? $json['parcelSummary'] : [],
            $awbNumber
        );
        $json['expeditionStatus'] = $this->normalizeExpeditionStatus(
            is_array($json['expeditionStatus'] ?? null) ? $json['expeditionStatus'] : []
        );
        $json['parcelHistory'] = $this->normalizeParcelHistory(
            is_array($json['parcelHistory'] ?? null) ? $json['parcelHistory'] : []
        );

        return (string) json_encode($json, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $summary
     * @param string $awbNumber
     *
     * @return array<string, mixed>
     */
    private function normalizeParcelSummary(array $summary, string $awbNumber): array
    {
        return array_merge(
            [
                'delivered' => false,
                'canceled' => false,
                'deliveryAttempts' => 0,
                'parcelAwbNumber' => $awbNumber,
                'parcelWeight' => 0,
                'isPickedUp' => false,
            ],
            $summary
        );
    }

    /**
     * @param array $expeditionStatus
     *
     * @return array<string, mixed>
     */
    private function normalizeExpeditionStatus(array $expeditionStatus): array
    {
        $expeditionStatus = array_merge(
            [
                'statusId' => 0,
                'status' => '',
                'statusLabel' => '',
                'statusState' => null,
                'statusDate' => self::FALLBACK_STATUS_DATE,
                'county' => null,
                'reason' => '',
                'transitLocation' => '',
                'expeditionDetails' => '',
            ],
            $expeditionStatus
        );

        $expeditionStatus['statusDate'] = $this->normalizeStatusDate(
            isset($expeditionStatus['statusDate']) ? (string) $expeditionStatus['statusDate'] : null
        );

        return $expeditionStatus;
    }

    /**
     * @param array $parcelHistory
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeParcelHistory(array $parcelHistory): array
    {
        $normalizedHistory = [];

        foreach ($parcelHistory as $history) {
            if (!is_array($history)) {
                continue;
            }

            $normalizedHistory[] = array_merge(
                [
                    'statusId' => 0,
                    'status' => '',
                    'statusLabel' => null,
                    'statusState' => null,
                    'county' => null,
                    'reason' => '',
                    'transitLocation' => '',
                    'inReturn' => null,
                ],
                $history,
                [
                    'statusDate' => $this->normalizeStatusDate(
                        isset($history['statusDate']) ? (string) $history['statusDate'] : null
                    ),
                ]
            );
        }

        return $normalizedHistory;
    }

    /**
     * @param ?string $statusDate
     *
     * @return string
     */
    private function normalizeStatusDate(?string $statusDate): string
    {
        if (null === $statusDate || '' === $statusDate) {
            return self::FALLBACK_STATUS_DATE;
        }

        return $statusDate;
    }
}
