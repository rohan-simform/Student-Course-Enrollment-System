<?php

/**
 * Reusable server-side DataTable helper.
 *
 * Supports:
 * - pagination
 * - global search
 * - ordering
 * - total records
 * - filtered records
 */
class DataTableHelper {
    /**
     * Build DataTable response.
     *
     * @param  mysqli  $conn
     * @param  array  $config
     * @param  array  $request
     * @return array
     */
    public static function make($conn, $config, $request) {
        $draw = isset($request['draw'])
            ? (int) $request['draw']
            : 1;

        $start = isset($request['start'])
            ? max(0, (int) $request['start'])
            : 0;

        $length = isset($request['length'])
            ? max(1, (int) $request['length'])
            : 10;

        $searchValue = trim($request['search']['value'] ?? '');

        $baseWhere = trim($config['where'] ?? '');

        $searchWhere = '';
        $searchParams = [];
        $searchTypes = '';

        if ($searchValue !== '' && ! empty($config['searchable'])) {

            $searchParts = [];

            foreach ($config['searchable'] as $column) {
                $searchParts[] = "$column LIKE ?";
                $searchParams[] = '%'.$searchValue.'%';
                $searchTypes .= 's';
            }

            $searchWhere = '('.implode(' OR ', $searchParts).')';
        }

        $whereParts = [];

        if ($baseWhere !== '') {
            $whereParts[] = '('.$baseWhere.')';
        }

        if ($searchWhere !== '') {
            $whereParts[] = $searchWhere;
        }

        $finalWhere = '';

        if (! empty($whereParts)) {
            $finalWhere = ' WHERE '.implode(' AND ', $whereParts);
        }

        $groupBy = '';
        if (isset($config['groupBy']) && trim($config['groupBy']) !== '') {
            $groupBy = " GROUP BY {$config['groupBy']}";
        }

        $orderBy = $config['defaultOrder'] ?? '1 DESC';

        if (
            isset($request['order'][0]['column']) &&
            isset($request['order'][0]['dir'])
        ) {

            $columnIndex = (int) $request['order'][0]['column'];

            $dir = strtolower($request['order'][0]['dir']) === 'asc'
                ? 'ASC'
                : 'DESC';

            if (isset($config['sortable'][$columnIndex])) {

                $column = $config['sortable'][$columnIndex];

                $orderBy = "$column $dir";
            }
        }

        $query = "
            SELECT
                {$config['select']}
            FROM
                {$config['table']}
            {$finalWhere}
            {$groupBy}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($query);

        if (! $stmt) {
            return [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $conn->error,
            ];
        }

        $params = $searchParams;
        $types = $searchTypes;

        $params[] = $length;
        $params[] = $start;

        $types .= 'ii';

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (isset($config['groupBy']) && trim($config['groupBy']) !== '') {
            $countField = $config['countField'] ?? 'DISTINCT 1';
            $totalQuery = "
                SELECT COUNT(*) as total
                FROM (
                    SELECT {$countField}
                    FROM {$config['table']}
            ";
            if ($baseWhere !== '') {
                $totalQuery .= " WHERE {$baseWhere}";
            }
            $totalQuery .= " GROUP BY {$config['groupBy']} ) counted";
        } else {
            $totalQuery = "
                SELECT COUNT(*) as total
                FROM {$config['table']}
            ";
            if ($baseWhere !== '') {
                $totalQuery .= " WHERE {$baseWhere}";
            }
        }

        $totalResult = $conn
            ->query($totalQuery)
            ->fetch_assoc();

        $recordsTotal = (int) $totalResult['total'];

        if (isset($config['groupBy']) && trim($config['groupBy']) !== '') {
            $countField = $config['countField'] ?? 'DISTINCT 1';
            $filteredQuery = "
                SELECT COUNT(*) as total
                FROM (
                    SELECT {$countField}
                    FROM {$config['table']}
                    {$finalWhere}
                    GROUP BY {$config['groupBy']}
                ) counted
            ";
        } else {
            $filteredQuery = "
                SELECT COUNT(*) as total
                FROM {$config['table']}
                {$finalWhere}
            ";
        }

        $stmt = $conn->prepare($filteredQuery);

        if (! $stmt) {
            return [
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $conn->error,
            ];
        }

        if (! empty($searchParams)) {
            $stmt->bind_param($searchTypes, ...$searchParams);
        }

        $stmt->execute();

        $filteredResult = $stmt
            ->get_result()
            ->fetch_assoc();

        $recordsFiltered = (int) $filteredResult['total'];


        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}
