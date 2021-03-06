<?php

namespace Hareku\Bulk\EloquentBulk;

use Illuminate\Database\Eloquent\Model;

interface EloquentBulk
{
    public function insert(Model $model, array $columns, array $records): void;

    public function update(Model $model, array $indices, array $records): void;
}
