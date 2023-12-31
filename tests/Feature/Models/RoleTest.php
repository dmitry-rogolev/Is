<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Contracts\Sluggable;
use dmitryrogolev\Is\Contracts\RoleHasRelations;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Тестируем модель роли.
 */
class RoleTest extends TestCase
{
    /**
     * Имя модели.
     */
    protected string $model;

    /**
     * Имя первичного ключа.
     */
    protected string $keyName;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
        $this->keyName = config('is.primary_key');
    }

    /**
     * Совпадает ли имя первичного ключа модели с конфигом?
     */
    public function test_primary_key(): void
    {
        $role = app($this->model);

        $this->assertEquals($this->keyName, $role->getKeyName());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     */
    public function test_timestamps(): void
    {
        $role = app($this->model);

        $this->assertEquals(config('is.uses.timestamps'), $role->usesTimestamps());
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

    /**
     * Подключены ли трейты "\Illuminate\Database\Eloquent\Concerns\HasUuids"
     * и "\Illuminate\Database\Eloquent\SoftDeletes" согласно конфигурации?
     */
    public function test_uses_traits(): void
    {
        $role = app($this->model);
        $traits = collect(class_uses_recursive($role));
        $hasUuids = $traits->contains(HasUuids::class);
        $softDeletes = $traits->contains(SoftDeletes::class);

        if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
            $this->assertTrue($hasUuids);
            $this->assertTrue($softDeletes);
        } elseif (config('is.uses.uuid')) {
            $this->assertTrue($hasUuids);
            $this->assertFalse($softDeletes);
        } elseif (config('is.uses.soft_deletes')) {
            $this->assertFalse($hasUuids);
            $this->assertTrue($softDeletes);
        } else {
            $this->assertFalse($hasUuids);
            $this->assertFalse($softDeletes);
        }
    }
}
