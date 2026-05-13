<?php

/**
 * Provides reusable SQL query builder utilities.
 */
class QueryHelper {
    /**
     * Build dynamic update query with parameters.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  array  $allowedFields
     * @param  array  $whereConditions
     * @return array|null
     */
    public static function buildUpdateQuery($table, $data, $allowedFields, $whereConditions) {
        $fields = [];
        $params = [];
        $types = '';

        // SET part
        foreach ($allowedFields as $field => $type) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }

        if (empty($fields)) {
            return null;
        }

        // WHERE part
        $whereParts = [];

        foreach ($whereConditions as $field => $condition) {
            $whereParts[] = "$field = ?";
            $params[] = $condition['value'];
            $types .= $condition['type'];
        }

        $query = "UPDATE $table 
                  SET ".implode(', ', $fields).'
                  WHERE '.implode(' AND ', $whereParts);

        return [
            'query' => $query,
            'types' => $types,
            'params' => $params,
        ];
    }

    /**
     * Execute prepared query data.
     *
     * @param  mysqli  $conn
     * @param  array|null  $queryData
     * @return int
     *
     * @throws Exception
     */
    public static function execute($conn, $queryData) {
        if (! $queryData) {
            return 0;
        }

        $stmt = $conn->prepare($queryData['query']);

        $stmt->bind_param(
            $queryData['types'],
            ...$queryData['params']
        );

        if (! $stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $stmt->affected_rows;
    }
}
