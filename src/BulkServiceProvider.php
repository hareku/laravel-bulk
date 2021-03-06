<?php

namespace Hareku\Bulk;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\DatabaseManager;
use Hareku\Bulk\BulkProcessor\BulkProcessor;
use Hareku\Bulk\BulkProcessor\PdoBulkProcessor;
use Hareku\Bulk\EloquentBulk\EloquentBulk;
use Hareku\Bulk\EloquentBulk\ConcreteEloquentBulk;

class BulkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(BulkProcessor::class, function ($app) {
            $pdo = $app->make(DatabaseManager::class)->connection()->getPdo();
            return new PdoBulkProcessor($pdo);
        });

        $this->app->singleton(EloquentBulk::class, function ($app) {
            return new ConcreteEloquentBulk($app->make(BulkProcessor::class));
        });
    }

    public function provides()
    {
        return [
            BulkProcessor::class,
            EloquentBulk::class,
        ];
    }
}
