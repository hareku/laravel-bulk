<?php

namespace Hareku\Bulk\BulkProcessor;

use PDO;
use InvalidArgumentException;

class PdoBulkProcessor implements BulkProcessor
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(string $table, array $columns, array $records): void
    {
        if(count($columns) == 0) {
            throw new InvalidArgumentException('Columns must not be empty.');
        }

        if(count($records) == 0) {
            return;
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
        if(count($indices) == 0) {
            throw new InvalidArgumentException('Indices must not be empty.');
        }

        if(count($records) == 0) {
            return;
        }

        $valuesByColumn = [];
        foreach ($records as $recordKey => $record) {
            if(! is_array($record)) {
                throw new InvalidArgumentException('A record must be an array, ' . gettype($record) . ' given.');
            }

            foreach ($indices as $index) {
                if(! array_key_exists($index, $record)) {
                    throw new InvalidArgumentException("A record must contain a given index `{$index}`.");
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

        foreach ($valuesByColumn as $column => $valuesByRecordKey) {
            $sql .= " `{$column}` = CASE(";
            foreach ($valuesByRecordKey as $recordKey => $value) {
                foreach ($indices as $indexKey => $index) {
                    $sql .= $indexKey == 0 ? ' WHEN ' : ' AND ';
                    $sql .= "`{$index}` = :key_{$recordKey}_index_{$indexKey}";
                }
                $sql .= " THEN :key_{$recordKey}_column_{$column}";
            }
            $sql .= ')';
        }

        foreach ($indices as $indexKey => $index) {
            $sql .= " WHERE `{$index}` IN (";
            foreach ($records as $recordKey => $record) {
                if($recordKey > 0) {
                    $sql .= ',';
                }
                $sql .= ":key_{$recordKey}_index_{$indexKey}";
            }
            $sql .= ')';
        }

        $sql .= ';';

        $statement = $this->pdo->prepare($sql);

        foreach ($records as $recordKey => $record) {
            foreach ($indices as $indexKey => $index) {
                $statement->bindValue(":key_{$recordKey}_index_{$indexKey}", $record[$index]);
            }
            foreach ($record as $column => $value) {
                if(! in_array($column, $indices)) {
                    $statement->bindValue(":key_{$recordKey}_column_{$column}", $value);
                }
            }
        }

        $statement->execute();
    }
}
