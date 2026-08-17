<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostRemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use Throwable;

final class PostRemoveAwbServiceProvider implements PostRemoveAwbServiceProviderInterface
{
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(?SamedayAwbRepository $samedayAwbRepository = null)
    {
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository();
    }

    /**
     * @param PostRemoveAwbRequestDto $postRemoveAwbRequestDto
     *
     * @return PostRemoveAwbResponseDto
     */
    public function apply(PostRemoveAwbRequestDto $postRemoveAwbRequestDto): PostRemoveAwbResponseDto
    {
        try {
            $this->samedayAwbRepository->deleteAwbAndParcels($postRemoveAwbRequestDto->getAwb());
        } catch (Throwable $exception) {
            return new PostRemoveAwbResponseDto(
                false,
                $exception->getMessage()
            );
        }

        return new PostRemoveAwbResponseDto(
            true,
            'AWB removed locally with success.'
        );
    }
}
