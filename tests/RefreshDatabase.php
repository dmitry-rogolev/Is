<?php

namespace dmitryrogolev\Is\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as TestingRefreshDatabase;

use function Orchestra\Testbench\workbench_path;

trait RefreshDatabase
{
    use TestingRefreshDatabase;

    /**
     * Определите миграцию базы данных.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }
}
