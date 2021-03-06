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

        $valuesByIndex = []; // for WHERE IN
        $valuesByColumn = [];

        foreach ($records as $recordKey => $record) {
            foreach ($indices as $index) {
                if(! array_key_exists($index, $record)) {
                    // TODO: throw argument error
                }
                $valuesByIndex[$index][] = $record[$index];
            }

            foreach ($record as $column => $value) {
                if(! in_array($column, $indices, true)) {
                    $valuesByColumn[$column][$recordKey] = $value;
                }
            }
        }

        $sql = "UPDATE `{$table}` SET";
        $params = [];

        foreach ($valuesByColumn as $column => $valuesByRecordKey) {
            $sql .= " `{$column}` = CASE(";
            foreach ($valuesByRecordKey as $recordKey => $value) {
                foreach ($indices as $indexKey => $index) {
                    $sql .= $indexKey == 0 ? ' WHEN ' : ' AND ';
                    $sql .= "`{$index}` = :key_{$recordKey}_index_{$indexKey}";
                }
                $sql .= ' THEN ?';
                $params[] = $value;
            }
            $sql .= ')';
        }
        $sql .= ';';

        $statement = $this->pdo->prepare($sql);

        foreach ($records as $recordKey => $record) {
            foreach ($indices as $indexKey => $index) {
                $statement->bindValue(":key_{$recordKey}_index_{$indexKey}", $record[$index]);
            }
        }

        $statement->execute($params);
    }
}
