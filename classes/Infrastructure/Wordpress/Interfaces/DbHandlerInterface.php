<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

interface DbHandlerInterface
{
    /**
     * @param string $tableName
     *
     * @return string
     */
    public function buildTableName(string $tableName): string;

    /**
     * @param string $queryString
     * @param array $queryParams
     *
     * @return null|string
     */
    public function getVar(string $queryString, array $queryParams = []): ?string;

    /**
     * @param string $queryString
     * @param array $queryParams
     *
     * @return array
     */
    public function getRow(string $queryString, array $queryParams): array;

    /**
     * @param string $queryString
     * @param array $queryParams
     *
     * @return array
     */
    public function getRows(string $queryString, array $queryParams): array;

    /**
     * @param string $tableName
     * @param array $params
     *
     * @return bool True when the row was inserted, false otherwise.
     */
    public function insertRow(string $tableName, array $params): bool;

    /**
     * @param string $tableName
     * @param array $row
     * @param array $where [id => $id]
     *
     * @return bool True when the update query succeeded, false otherwise.
     */
    public function updateRow(string $tableName, array $row, array $where): bool;

    /**
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteRow(string $tableName, array $where): void;

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isTableExists(string $tableName): bool;

    /**
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    public function columnExists(string $tableName, string $columnName): bool;

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function truncateTable(string $tableName): void;

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function dropTable(string $tableName): void;

    /**
     * @param string $sql
     *
     * @return void
     */
    public function executeQuery(string $sql): void;

    /**
     * @return string
     */
    public function getCharsetCollate(): string;
}
