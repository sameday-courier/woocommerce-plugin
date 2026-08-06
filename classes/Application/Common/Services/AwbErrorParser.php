<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbErrorParser
{
    /**
     * @param array<int, array<string, mixed>> $errors
     *
     * @return string
     */
    public function parse(array $errors): string
    {
        $allErrors = [];
        foreach ($errors as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $message) {
                    $allErrors[] = implode('.', $error['key']) . ': ' . $message;
                }
            } else {
                $allErrors[] = sprintf(
                    '%s : %s',
                    $error['code'] ?? 'Generic Error',
                    $error['message'] ?? 'Something went wrong'
                );
            }
        }

        return implode('<br/>', $allErrors);
    }
}
