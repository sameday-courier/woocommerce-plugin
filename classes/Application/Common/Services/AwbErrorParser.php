<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbErrorParser
{
    private const GENERIC_MESSAGE = 'Something went wrong.';
    private const SEPARATOR = '; ';

    /**
     * @param array<int, mixed> $errors
     *
     * @return string
     */
    public function parse(array $errors): string
    {
        $messages = [];

        foreach ($errors as $error) {
            foreach ($this->extractMessages($error) as $message) {
                $messages[] = $message;
            }
        }

        $messages = array_values(array_unique($messages));

        if ([] === $messages) {
            return self::GENERIC_MESSAGE;
        }

        return implode(self::SEPARATOR, $messages);
    }

    /**
     * @param mixed $error
     *
     * @return array<int, string>
     */
    private function extractMessages($error): array
    {
        if (is_string($error) || is_numeric($error)) {
            $message = $this->normalizeMessage($error);

            return null === $message ? [] : [$message];
        }

        if (!is_array($error)) {
            return [];
        }

        if (array_key_exists('errors', $error)) {
            return $this->extractValidationMessages($error);
        }

        $message = $this->normalizeMessage($error['message'] ?? null);
        if (null !== $message) {
            return [$message];
        }

        $message = $this->normalizeMessage($error['error'] ?? null);
        if (null !== $message) {
            return [$message];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $error
     *
     * @return array<int, string>
     */
    private function extractValidationMessages(array $error): array
    {
        $rawMessages = $error['errors'];
        if (!is_array($rawMessages)) {
            $rawMessages = [$rawMessages];
        }

        $keyPrefix = $this->formatKey($error['key'] ?? null);
        $messages = [];

        foreach ($rawMessages as $rawMessage) {
            $message = $this->normalizeMessage($rawMessage);
            if (null === $message) {
                continue;
            }

            $messages[] = '' !== $keyPrefix
                ? sprintf('%s: %s', $keyPrefix, $message)
                : $message;
        }

        return $messages;
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    private function formatKey($key): string
    {
        if (is_array($key)) {
            $parts = [];
            foreach ($key as $part) {
                if (!is_scalar($part)) {
                    continue;
                }

                $value = trim((string) $part);
                if ('' === $value) {
                    continue;
                }

                $parts[] = $value;
            }

            return implode('.', $parts);
        }

        if (is_scalar($key)) {
            return trim((string) $key);
        }

        return '';
    }

    /**
     * @param mixed $message
     *
     * @return string|null
     */
    private function normalizeMessage($message): ?string
    {
        if (!is_scalar($message)) {
            return null;
        }

        $text = trim((string) $message);
        if ('' === $text) {
            return null;
        }

        return $text;
    }
}
