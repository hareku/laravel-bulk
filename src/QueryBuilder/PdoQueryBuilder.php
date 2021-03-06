<?php

namespace Hareku\Bulk\QueryBuilder;

use PDO;

class PdoQueryBuilder implements QueryBuilder
{
    /**
     * @return void
     */
    public function __construct(public PDO $pdo)
    {
        //
    }

    public function buildInsertion(string $table, array $columns, array $records): string
    {
        $query = "INSERT INTO {$table}";

        $query .= ' (' . $this->implodeWithEscape(', ', $columns) . ')';

        $query .= ' VALUES';

        foreach ($records as $record) {
            $query .= ' (' . $this->implodeWithEscape(', ', $record) . ')';
        }

        $query .= ";";

        return $query;
    }

    protected function implodeWithEscape(string $glue, array $array): string
    {
        $result = "";

        foreach ($array as $value) {
            $result .= $value . $glue;
        }

        return rtrim($result, $glue);
    }
}
