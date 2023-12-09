<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Seeders;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем сидер роли.
 */
class RoleSeederTest extends TestCase
{
    /**
     * Есть ли метод, возвращающий роли?
     */
    public function test_get_roles(): void
    {
        $checkFields = collect(app(config('is.seeders.role'))->getRoles())->every(
            fn ($item) => array_key_exists('name', $item)
            && array_key_exists('slug', $item)
            && array_key_exists('description', $item)
            && array_key_exists('level', $item)
        );

        $this->assertTrue($checkFields);
    }

    /**
     * Создаются ли модели при запуске сидера?
     */
    public function test_run(): void
    {
        $this->runLaravelMigrations();
        app(config('is.seeders.role'))->run();

        $this->assertCount(count(app(config('is.seeders.role'))->getRoles()), Is::all());
    }
}
