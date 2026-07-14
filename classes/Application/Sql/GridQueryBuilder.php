<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\SqlSanitizer;

if (!defined('ABSPATH')) {
    exit;
}

final class GridQueryBuilder
{
    /**
     * @param array<int, string> $allowedOrderByColumns
     * @param array<int, string>|null $whereInValues
     * @param array<int, string> $allowedSearchColumns
     * @param array<string, string> $likeFilters
     * @param array<string, int|string|bool> $exactFilters
     *
     * @return array{sql: string, params: array<int, mixed>}
     */
    public static function build(
        string $tableName,
        bool $isTesting,
        array $allowedOrderByColumns,
        ?string $orderBy = null,
        ?string $order = null,
        ?int $perPage = null,
        ?int $pageNumber = null,
        ?string $whereInColumn = null,
        ?array $whereInValues = null,
        array $allowedSearchColumns = [],
        array $likeFilters = [],
        array $exactFilters = []
    ): array {
        $where = self::buildWhereClause(
            $isTesting,
            $whereInColumn,
            $whereInValues,
            $allowedSearchColumns,
            $likeFilters,
            $exactFilters
        );

        $sql = sprintf('SELECT * FROM %s WHERE %s', $tableName, $where['conditions']);
        $params = $where['params'];

        if (null !== $orderBy && in_array($orderBy, $allowedOrderByColumns, true)) {
            $sql .= sprintf(' ORDER BY %s ', SqlSanitizer::escSql($orderBy));
        }

        if (null !== $order && in_array(strtoupper($order), SamedayConstants::ORDER_BY_TYPES, true)) {
            $sql .= strtoupper($order);
        }

        if (null !== $perPage && null !== $pageNumber) {
            $sql .= ' LIMIT %d OFFSET %d';
            $params[] = $perPage;
            $params[] = ($pageNumber - 1) * $perPage;
        }

        return [
            'sql' => $sql,
            'params' => $params,
        ];
    }

    /**
     * @param array<int, string>|null $whereInValues
     * @param array<int, string> $allowedSearchColumns
     * @param array<string, string> $likeFilters
     * @param array<string, int|string|bool> $exactFilters
     *
     * @return array{sql: string, params: array<int, mixed>}
     */
    public static function buildCount(
        string $tableName,
        bool $isTesting,
        ?string $whereInColumn = null,
        ?array $whereInValues = null,
        array $allowedSearchColumns = [],
        array $likeFilters = [],
        array $exactFilters = []
    ): array {
        $where = self::buildWhereClause(
            $isTesting,
            $whereInColumn,
            $whereInValues,
            $allowedSearchColumns,
            $likeFilters,
            $exactFilters
        );

        return [
            'sql' => sprintf('SELECT COUNT(*) FROM %s WHERE %s', $tableName, $where['conditions']),
            'params' => $where['params'],
        ];
    }

    /**
     * @param array<int, string>|null $whereInValues
     * @param array<int, string> $allowedSearchColumns
     * @param array<string, string> $likeFilters
     * @param array<string, int|string|bool> $exactFilters
     *
     * @return array{conditions: string, params: array<int, mixed>}
     */
    private static function buildWhereClause(
        bool $isTesting,
        ?string $whereInColumn = null,
        ?array $whereInValues = null,
        array $allowedSearchColumns = [],
        array $likeFilters = [],
        array $exactFilters = []
    ): array {
        $conditions = ['is_testing = %d'];
        $params = [(int) $isTesting];

        if (null !== $whereInColumn && null !== $whereInValues && [] !== $whereInValues) {
            $placeholders = implode(', ', array_fill(0, count($whereInValues), '%s'));
            $conditions[] = sprintf('%s IN (%s)', SqlSanitizer::escSql($whereInColumn), $placeholders);
            $params = array_merge($params, $whereInValues);
        }

        foreach ($likeFilters as $column => $value) {
            if ('' === $value || !in_array($column, $allowedSearchColumns, true)) {
                continue;
            }

            $conditions[] = sprintf('%s LIKE %%s', SqlSanitizer::escSql($column));
            $params[] = '%' . $value . '%';
        }

        foreach ($exactFilters as $column => $value) {
            if (!in_array($column, $allowedSearchColumns, true)) {
                continue;
            }

            $conditions[] = sprintf('%s = %%d', SqlSanitizer::escSql($column));
            $params[] = (int) $value;
        }

        return [
            'conditions' => implode(' AND ', $conditions),
            'params' => $params,
        ];
    }
}
