<?php

namespace Hareku\Bulk\QueryBuilder;

interface QueryBuilder
{
    public function buildInsertion(string $table, array $columns, array $records): string;
}
