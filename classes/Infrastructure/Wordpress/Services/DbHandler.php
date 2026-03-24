<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

class DbHandler implements DbHandlerInterface
{
    /**
     * @var $db
     */
    private $db;
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function buildTableName(string $tableName): string
    {
        return $this->db->prefix . $tableName;
    }

    /**
     * @param string $queryString
     * @param array $queryParams
     *
     * @return string
     */
    public function prepareQuery(string $queryString, array $queryParams = []): string
    {
        return $this->db->prepare($queryString, ...$queryParams);
    }

    /**
     * @param string $queryString
     *
     * @return null|string
     */
    public function getVar(string $queryString): ?string
    {
        return $this->db->get_var($queryString);
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    public function getRow(string $queryString): array
    {
        return $this->db->get_row($queryString, ARRAY_A) ?? [];
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    public function getRows(string $queryString): array
    {
        return $this->db->get_results($queryString, ARRAY_A) ?? [];
    }

    /**
     * @param string $tableName
     * @param array $params
     *
     * @return void
     */
    public function insertRow(string $tableName, array $params): void
    {
        $this->db->insert($tableName, $params, $this->buildFormat($params));
    }

    /**
     * @param string $tableName
     * @param array $row
     * @param array $where [id => $id]
     *
     * @return void
     */
    public function updateRow(string $tableName, array $row, array $where): void
    {
        $this->db->update($tableName, $row, $where);
    }

    /**
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteRow(string $tableName, array $where): void
    {
        $this->db->delete($tableName, $where);
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isTableExists(string $tableName): bool
    {
        $queryString = $this->prepareQuery(
            "SHOW TABLES LIKE %s",
            [
                $tableName
            ]
        );

        return (bool) $this->db->get_var($queryString);
    }

    /**
     * @param string $tableName
     * 
     * @return void
     */
    public function truncateTable(string $tableName): void
    {
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE $tableName");
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        $this->db->query("DROP TABLE IF EXISTS $tableName");
    }

    /**
     * @param string $sql
     *
     * @return void
     */
    public function executeQuery(string $sql): void
    {
        $this->db->query($sql);
    }

    /**
     * @return string
     */
    public function getCharsetCollate(): string
    {
        return $this->db->get_charset_collate();
    }

    /**
     * @param array $params
     *
     * @return array [%s, %s, %d, ....]
     */
    private function buildFormat(array $params): array
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
