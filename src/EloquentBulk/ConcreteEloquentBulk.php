<?php

namespace Hareku\Bulk\EloquentBulk;

use Hareku\Bulk\BulkProcessor\BulkProcessor;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ConcreteEloquentBulk implements EloquentBulk
{
    /**
     * The bulk processor instance.
     *
     * @var BulkProcessor
     */
    protected $processor;

    /**
     * @return void
     */
    public function __construct(BulkProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function insert(Model $model, array $columns, array $records): void
    {
        if($model->usesTimestamps()) {
            $createdAtColumn = $model->getCreatedAtColumn();
            if(in_array($createdAtColumn, $columns, true)) {
                throw new InvalidArgumentException("Columns must not have `created at` column ({$createdAtColumn}), it will be auto filled.");
            }

            $updatedAtColumn = $model->getUpdatedAtColumn();
            if(in_array($updatedAtColumn, $columns, true)) {
                throw new InvalidArgumentException("Columns must not have `updated at` column ({$updatedAtColumn}), it will be auto filled.");
            }

            $columns[] = $createdAtColumn;
            $columns[] = $updatedAtColumn;

            $timestamp = $model->freshTimestamp()->format($model->getDateFormat());
            foreach ($records as &$record) {
                $record[] = $timestamp;
                $record[] = $timestamp;
            }
        }

        $this->processor->insert($this->resolveTableName($model), $columns, $records);
    }

    public function update(Model $model, array $indices, array $records): void
    {
        if($model->usesTimestamps()) {
            $timestamp = $model->freshTimestamp()->format($model->getDateFormat());

            $column = $model->getUpdatedAtColumn();
            foreach ($records as &$record) {
                $record[$column] = $timestamp;
            }
        }

        $this->processor->update($this->resolveTableName($model), $indices, $records);
    }

    /**
     * Get the full table name of the given model.
     */
    protected function resolveTableName(Model $model): string
    {
        return $model->getConnection()->getTablePrefix() . $model->getTable();
    }
}
