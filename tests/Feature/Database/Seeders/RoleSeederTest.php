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
     *
     * @return void
     */
    public function test_get_roles(): void
    {
        $checkFields = collect(app(Is::roleSeeder())->getRoles())->every(
            fn ($item) =>
            array_key_exists('name', $item)
            && array_key_exists('slug', $item)
            && array_key_exists('description', $item)
            && array_key_exists('level', $item)
        );

        $this->assertTrue($checkFields);
    }

    /**
     * Создаются ли модели при запуске сидера?
     *
     * @return void
     */
    public function test_run(): void
    {
        $this->runLaravelMigrations();
        app(Is::roleSeeder())->run();

        $this->assertCount(count(app(Is::roleSeeder())->getRoles()), Is::all());
    }
}
