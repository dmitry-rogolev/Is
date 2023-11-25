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
     *
     * @return void
     */
    public function test_definition(): void 
    {
        $checkFields = function () { 
            $state = app(config('is.factories.role'))->definition();
            return array_key_exists('name', $state) 
                    && array_key_exists('slug', $state) 
                    && array_key_exists('definition', $state) 
                    && config('is.uses.levels') ? array_key_exists('level', $state) : true;
        };

        config(['is.uses.levels' => false]);
        $this->assertTrue($checkFields());

        config(['is.uses.levels' => true]);
        $this->assertTrue($checkFields());
    }

    /**
     * Создает ли фабрика модель?
     *
     * @return void
     */
    public function test_created(): void 
    {
        $this->runLaravelMigrations();

        $this->assertModelExists(app(config('is.factories.role'))->create());
    }
}
