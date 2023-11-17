<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\TestCase;

class RoleTest extends TestCase
{
    /**
     * Расширяет ли модель базовый класс для всех моделей?
     *
     * @return void
     */
    public function test_extends_database(): void 
    {
        $this->assertInstanceOf(Database::class, config('is.models.role'));
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     *
     * @return void
     */
    public function test_table(): void 
    {
        $this->assertEquals(config('is.tables.roles'), app(config('is.models.role'))->getTable());
    }

    /**
     * Получаем роль по ее slug
     *
     * @return void
     */
    public function test_get_a_role_by_slug(): void
    {
        $this->assertModelExists($this->role::user());
        $this->assertModelExists($this->role::admin());
        $this->assertModelExists($this->role::moderator());

        $this->expectException(\BadMethodCallException::class);
        $this->role::undefined();
    }

    /**
     * Проверяем наличие фабрики
     *
     * @return void
     */
    public function test_factory(): void 
    {
        $this->assertEquals(config('is.factories.role'), $this->role::factory()::class);
        $this->assertModelExists($this->role::factory()->create());
    }

    /**
     * Проверяем полиморфную связь многие-ко-многим
     *
     * @return void
     */
    public function test_roleables(): void 
    {
        $moderator = $this->role::moderator();
        
        $this->assertTrue($moderator->roleables(config('is.models.user'))->get()->isNotEmpty());
        $this->assertTrue($moderator->users->isNotEmpty());
    }
}
