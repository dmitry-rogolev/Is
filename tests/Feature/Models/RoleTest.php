<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Contracts\Sluggable;
use dmitryrogolev\Is\Contracts\RoleHasRelations;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем модель роли.
 */
class RoleTest extends TestCase
{
    /**
     * Имя модели.
     */
    protected string $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     */
    public function test_table(): void
    {
        $role = app($this->model);

        $this->assertEquals(config('is.tables.roles'), $role->getTable());
    }

    /**
     * Реализует ли модель интерфейс отношений роли?
     */
    public function test_implements_role_has_relations(): void
    {
        $role = app($this->model);

        $this->assertInstanceOf(RoleHasRelations::class, $role);
    }

    /**
     * Реализует ли модель интерфейс функционала, облегчающего работу с аттрибутом "slug"?
     */
    public function test_implements_sluggable(): void
    {
        $role = app($this->model);

        $this->assertInstanceOf(Sluggable::class, $role);
    }

    /**
     * Совпадает ли фабрика модели с конфигурацией?
     */
    public function test_factory(): void
    {
        $this->assertEquals(config('is.factories.role'), $this->model::factory()::class);
    }
}
