<?php

namespace Hareku\Bulk\BulkProcessor;

interface BulkProcessor
{
    public function insert(string $table, array $columns, array $records): void;

    public function update(string $table, array $indices, array $records): void;
}
