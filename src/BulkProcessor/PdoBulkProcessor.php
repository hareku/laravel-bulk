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
        if(count($columns) === 0) {
            throw new InvalidArgumentException('Columns must not be empty.');
        }

        if(count($records) === 0) {
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
            if(count($record) !== count($columns)) {
                throw new InvalidArgumentException("A record should be the same size as the number of columns.");
            }
            array_push($params, ...$record);
        }

        $statement->execute($params);
    }

    public function update(string $table, array $indices, array $records): void
    {
        if(count($indices) === 0) {
            throw new InvalidArgumentException('Indices must not be empty.');
        }

        if(count($records) === 0) {
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
            $sql .= " `{$column}` = (CASE";
            foreach ($valuesByRecordKey as $recordKey => $value) {
                foreach ($indices as $indexKey => $index) {
                    $sql .= $indexKey === 0 ? ' WHEN ' : ' AND ';
                    $sql .= "`{$index}` = ?";
                    $params[] = $records[$recordKey][$index];
                }
                $sql .= " THEN ?";
                $params[] = $value;
            }
            $sql .= " ELSE `{$column}` END),";
        }
        $sql = rtrim($sql, ',');

        $sql .= ' WHERE ';
        foreach ($indices as $i => $index) {
            if($i > 0) {
                $sql .= " AND ";
            }

            $sql .= "`{$index}` IN (";
            foreach ($records as $record) {
                $sql .= "?,";
                $params[] = $record[$index];
            }
            $sql = rtrim($sql, ',');
            $sql .= ')';
        }

        $sql .= ';';

        $statement = $this->pdo->prepare($sql);

        $statement->execute($params);
    }
}
