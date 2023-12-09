<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Factories;

use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем фабрику роли.
 */
class RoleFactoryTest extends TestCase
{
    /**
     * Есть ли метод, возвращающий поля модели согласно конфигурации?
     */
    public function test_definition(): void
    {
        $state = app(config('is.factories.role'))->definition();

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
        $this->runLaravelMigrations();

        $this->assertModelExists(app(config('is.factories.role'))->create());
    }
}
