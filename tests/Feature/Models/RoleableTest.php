<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Тестируем модель промежуточной таблицы ролей.
 */
class RoleableTest extends TestCase
{
    /**
     * Расширяет ли модель класс "\Illuminate\Database\Eloquent\Relations\MorphPivot"?
     *
     * @return void
     */
    public function test_extends_morph_pivot(): void
    {
        $this->assertInstanceOf(MorphPivot::class, app(Is::roleableModel()));
    }

    /**
     * Совпадает ли имя соединения с БД в модели с конфигом?
     *
     * @return void
     */
    public function test_connection(): void
    {
        $this->assertEquals(Is::connection(), app(Is::roleableModel())->getConnectionName());
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     *
     * @return void
     */
    public function test_table(): void
    {
        $this->assertEquals(Is::roleablesTable(), app(Is::roleableModel())->getTable());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     *
     * @return void
     */
    public function test_timestamps(): void
    {
        $this->assertEquals(Is::usesTimestamps(), app(Is::roleableModel())->usesTimestamps());
    }
}
