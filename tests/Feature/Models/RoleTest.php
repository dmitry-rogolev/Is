<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

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
        // TODO
    }

    public function test_implements_role_has_relations(): void 
    {
        // TODO
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
    public function test_call_static_get_role(): void
    {
        // TODO
    }

    /**
     * Проверяем наличие фабрики
     *
     * @return void
     */
    public function test_factory(): void 
    {
        // TODO
    }
}
