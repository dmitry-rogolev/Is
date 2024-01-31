<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Тестируем модель промежуточной таблицы ролей.
 */
class RoleableTest extends TestCase
{
    /**
     * Имя модели.
     */
    protected string $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.roleable');
    }

    /**
     * Расширяет ли модель класс "\Illuminate\Database\Eloquent\Relations\MorphPivot"?
     */
    public function test_extends_morph_pivot(): void
    {
        $roleable = app($this->model);

        $this->assertInstanceOf(MorphPivot::class, $roleable);
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     */
    public function test_table(): void
    {
        $roleable = app($this->model);

        $this->assertEquals(config('is.tables.roleables'), $roleable->getTable());
    }
}
