<?php

namespace Hareku\Bulk\BulkProcessor;

interface BulkProcessor
{
    public function insert(string $table, array $columns, array $records): void;
}
