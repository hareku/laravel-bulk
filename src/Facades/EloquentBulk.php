<?php

namespace Hareku\Bulk\Facades;

use Illuminate\Support\Facades\Facade;
use Hareku\Bulk\EloquentBulk\EloquentBulk as EloquentBulkInterface;

class EloquentBulk extends Facade
{
    /**
     * Get facade accessor to retrieve instance.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return EloquentBulkInterface::class;
    }
}
