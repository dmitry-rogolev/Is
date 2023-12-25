<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Seeders;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем сидер роли.
 */
class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Имя класса сидера.
     */
    protected string $roleSeeder;

    /**
     * Имя slug'а.
     */
    protected string $slugName;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleSeeder = config('is.seeders.role');
        $this->slugName = app(config('is.models.role'))->getSlugName();
    }

    /**
     * Есть ли метод, возвращающий роли?
     */
    public function test_get_roles(): void
    {
        $roles = app($this->roleSeeder)->getRoles();
        $checkFields = collect($roles)->every(
            fn ($item) => array_key_exists('name', $item)
            && array_key_exists($this->slugName, $item)
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
        app($this->roleSeeder)->run();

        $count = count(app($this->roleSeeder)->getRoles());
        $this->assertCount($count, Is::all());
    }
}
