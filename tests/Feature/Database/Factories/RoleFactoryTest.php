<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Factories;

use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем фабрику роли.
 */
class RoleFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Имя фабрики ролей.
     */
    protected string $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = config('is.factories.role');
    }

    /**
     * Есть ли метод, возвращающий поля модели согласно конфигурации?
     */
    public function test_definition(): void
    {
        $state = app($this->factory)->definition();

        $hasFields = array_key_exists('name', $state)
            && array_key_exists('slug', $state)
            && array_key_exists('description', $state)
            && array_key_exists('level', $state);

        $this->assertTrue($hasFields);
    }

    /**
     * Создает ли фабрика модель?
     */
    public function test_created(): void
    {
        $role = app($this->factory)->create();
        $this->assertModelExists($role);
    }
}
