<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

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
     * @return string
     */
    public function prepareQuery(string $queryString, array $queryParams = []): string;

    /**
     * @param string $queryString
     *
     * @return null|string
     */
    public function getVar(string $queryString): ?string;

    /**
     * @param string $queryString
     *
     * @return array
     */
    public function getRow(string $queryString): array;

    /**
     * @param string $queryString
     *
     * @return array
     */
    public function getRows(string $queryString): array;

    /**
     * @param string $tableName
     * @param array $params
     *
     * @return void
     */
    public function insertRow(string $tableName, array $params): void;

    /**
     * @param string $tableName
     * @param array $row
     * @param array $where [id => $id]
     *
     * @return void
     */
    public function updateRow(string $tableName, array $row, array $where): void;

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
