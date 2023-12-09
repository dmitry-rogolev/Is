<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Contracts\Sluggable;
use dmitryrogolev\Is\Contracts\RoleHasRelations;
use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Тестируем модель роли.
 */
class RoleTest extends TestCase
{
    /**
     * Расширяет ли модель базовый класс для всех моделей?
     */
    public function test_extends_database(): void
    {
        $this->assertInstanceOf(Database::class, app(config('is.models.role')));
    }

    /**
     * Реализует ли модель интерфейс отношений роли?
     */
    public function test_implements_role_has_relations(): void
    {
        $this->assertInstanceOf(RoleHasRelations::class, app(config('is.models.role')));
    }

    /**
     * Реализует ли модель интерфейс функционала, облегчающего работу с аттрибутом "slug"?
     */
    public function test_implements_sluggable(): void
    {
        $this->assertInstanceOf(Sluggable::class, app(config('is.models.role')));
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     */
    public function test_table(): void
    {
        $this->assertEquals(config('is.tables.roles'), app(config('is.models.role'))->getTable());
    }

    /**
     * Существует ли фабрика для модели?
     */
    public function test_factory(): void
    {
        $this->runLaravelMigrations();

        $this->assertTrue(class_exists(config('is.factories.role')));
        $this->assertModelExists(config('is.models.role')::factory()->create());
    }

    /**
     * Подключены ли трейты "\Illuminate\Database\Eloquent\Concerns\HasUuids"
     * и "\Illuminate\Database\Eloquent\SoftDeletes" согласно конфигурации?
     */
    public function test_uses_traits(): void
    {
        $traits = collect(class_uses_recursive(app(config('is.models.role'))));
        $hasUuids = $traits->contains(HasUuids::class);
        $softDeletes = $traits->contains(SoftDeletes::class);
        $hasTraits = function () use ($hasUuids, $softDeletes) {
            if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
                return $hasUuids && $softDeletes;
            }

            if (config('is.uses.uuid')) {
                return $hasUuids && ! $softDeletes;
            }

            if (config('is.uses.soft_deletes')) {
                return ! $hasUuids && $softDeletes;
            }

            return ! $hasUuids && ! $softDeletes;
        };

        $this->assertTrue($hasTraits());
    }
}
