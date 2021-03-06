<?php

namespace Hareku\Bulk\BulkProcessor;

use PDO;

class PdoBulkProcessor implements BulkProcessor
{
    /**
     * @return void
     */
    public function __construct(protected PDO $pdo)
    {
        //
    }

    public function insert(string $table, array $columns, array $records): void
    {
        if(count($columns) == 0) {
            // todo: throw argument error
        }
        if(count($records) == 0) {
            // todo: throw argument error
        }

        $sql = "INSERT INTO `{$table}`";
        $sql .= ' (' . implode(',', $columns) . ')';

        $valuesPlaceholder = '(' . rtrim(str_repeat('?,', count($records[0])), ',') . '),';
        $sql .= ' VALUES ' . rtrim(str_repeat($valuesPlaceholder, count($records)), ',');
        $sql .= ";";

        $statement = $this->pdo->prepare($sql);

        $params = [];
        foreach ($records as $record) {
            array_push($params, ...$record);
        }

        $statement->execute($params);
    }

    public function update(string $table, array $indices, array $records): void
    {
        $sql = "UPDATE `{$table}`";
        $recordValues = [];
        $mergedIndices = [];

        foreach ($records as $recordKey => $record) {
            $recordIndices = [];
            foreach ($indices as $index) {
                if(! array_key_exists($index, $record)) {
                    // TODO: throw argument error
                }
                $recordIndices[] = $record[$index];
                $mergedIndices[$index][] = $record[$index];
            }

            foreach ($record as $column => $value) {
                $recordValues[$column][$recordKey] = $value;
            }
        }

        $sql = "UPDATE `{$table}`";
        foreach ($recordValues as $column => $recordValues) {
            $sql .= " SET `{$column}` = CASE(";
        }
    }
}
