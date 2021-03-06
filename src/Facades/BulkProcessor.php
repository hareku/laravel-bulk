<?php

namespace Hareku\Bulk\Facades;

use Illuminate\Support\Facades\Facade;
use Hareku\Bulk\BulkProcessor\BulkProcessor as BulkProcessorInterface;

class BulkProcessor extends Facade
{
    /**
     * Get facade accessor to retrieve instance.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BulkProcessorInterface::class;
    }
}
