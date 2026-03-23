<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

class DbHandler
{
    /**
     * @param string $tableName
     *
     * @return string
     */
    public static function buildTableName(string $tableName): string
    {
        global $wpdb;

        return $wpdb->prefix . $tableName;
    }

    /**
     * @param string $queryString
     * @param array $params
     *
     * @return string
     */
    public static function prepareQuery(string $queryString, array $params = []): string
    {
        global $wpdb;

        return $wpdb->prepare($queryString, ...$params);
    }

    /**
     * @param string $queryString
     *
     * @return mixed
     */
    public static function getVar(string $queryString)
    {
        global $wpdb;

        return $wpdb->get_var($queryString);
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    public static function getRow(string $queryString): array
    {
        global $wpdb;

        return $wpdb->get_row($queryString, ARRAY_A) ?? [];
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    public static function getRows(string $queryString): array
    {
        global $wpdb;

        return $wpdb->get_results($queryString, ARRAY_A) ?? [];
    }

    /**
     * @param string $tableName
     * @param array $params
     *
     * @return void
     */
    public static function insertRow(string $tableName, array $params): void
    {
        global $wpdb;

        $wpdb->insert($tableName, $params, self::buildFormat($params));
    }

    /**
     * @param string $tableName
     * @param array $row
     * @param array $where [id => $id]
     *
     * @return void
     */
    public static function updateRow(string $tableName, array $row, array $where): void
    {
        global $wpdb;

        $wpdb->update($tableName, $row, $where);
    }

    /**
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public static function deleteRow(string $tableName, array $where): void
    {
        global $wpdb;

        $wpdb->delete($tableName, $where);
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public static function isTableExists(string $tableName): bool
    {
        global $wpdb;

        $queryString = self::prepareQuery(
            "SHOW TABLES LIKE %s",
            [
                $tableName
            ]
        );

        return (bool) $wpdb->get_var($queryString);
    }

    /**
     * @param string $tableName
     * 
     * @return void
     */
    public static function truncateTable(string $tableName): void
    {
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE $tableName");
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    public static function dropTable(string $tableName): void
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS $tableName");
    }

    /**
     * @param string $sql
     *
     * @return void
     */
    public static function executeQuery(string $sql): void
    {
        global $wpdb;

        $wpdb->query($sql);
    }

    /**
     * @return string
     */
    public static function getCharsetCollate(): string
    {
        global $wpdb;

        return $wpdb->get_charset_collate();
    }

    /**
     * @param array $params
     *
     * @return array [%s, %s, %d, ....]
     */
    private static function buildFormat(array $params): array
    {
        $format = [];
        foreach ($params as $value) {
            switch (gettype($value)) {
                case 'string' || 'NULL':
                    $format[] = '%s';
                break;
                case 'integer':
                    $format[] = '%d';
                break;
            }
        }

        return $format;
    }
}
