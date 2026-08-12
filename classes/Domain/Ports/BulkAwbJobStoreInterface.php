<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\BulkAwbJob;

if (!defined('ABSPATH')) {
    exit;
}

interface BulkAwbJobStoreInterface
{
    public function create(BulkAwbJob $job): void;

    public function get(string $jobId, int $userId): ?BulkAwbJob;

    public function save(BulkAwbJob $job): void;

    public function delete(string $jobId, int $userId): void;
}
