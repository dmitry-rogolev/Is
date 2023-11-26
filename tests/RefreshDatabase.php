<?php

namespace dmitryrogolev\Is\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as TestingRefreshDatabase;

trait RefreshDatabase
{
    use TestingRefreshDatabase;

    /**
     * Главный сидер, который запускает другие сидеры.
     *
     * @var string
     */
    protected string $seeder = \dmitryrogolev\Is\Tests\Database\Seeders\DatabaseSeeder::class;

    /**
     * Следует ли запускать сидеры после миграции?
     *
     * @var boolean
     */
    protected bool $seed = false;

    /**
     * Определите миграцию базы данных.
     *
     * @return void
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
