<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class CourierLoginRequestDto
{
    private string $user;

    private string $pass;

    private string $apiUrl;

    /**
     * @param string $user
     * @param string $pass
     * @param string $apiUrl
     */
    public function __construct(string $user, string $pass, string $apiUrl)
    {
        $this->user = $user;
        $this->pass = $pass;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
