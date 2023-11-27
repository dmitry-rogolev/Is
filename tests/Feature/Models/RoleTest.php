<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Contracts\RoleHasRelations;
use dmitryrogolev\Is\Contracts\Sluggable;
use dmitryrogolev\Is\Facades\Is;
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
     *
     * @return void
     */
    public function test_extends_database(): void
    {
        $this->assertInstanceOf(Database::class, app(Is::roleModel()));
    }

    /**
     * Реализует ли модель интерфейс отношений роли?
     *
     * @return void
     */
    public function test_implements_role_has_relations(): void
    {
        $this->assertInstanceOf(RoleHasRelations::class, app(Is::roleModel()));
    }

    /**
     * Реализует ли модель интерфейс функционала, облегчающего работу с аттрибутом "slug"?
     *
     * @return void
     */
    public function test_implements_sluggable(): void
    {
        $this->assertInstanceOf(Sluggable::class, app(Is::roleModel()));
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     *
     * @return void
     */
    public function test_table(): void
    {
        $this->assertEquals(Is::rolesTable(), app(Is::roleModel())->getTable());
    }

    /**
     * Существует ли фабрика для модели?
     *
     * @return void
     */
    public function test_factory(): void
    {
        $this->runLaravelMigrations();

        $this->assertTrue(class_exists(Is::roleFactory()));
        $this->assertModelExists(Is::roleModel()::factory()->create());
    }

    /**
     * Подключены ли трейты "\Illuminate\Database\Eloquent\Concerns\HasUuids" 
     * и "\Illuminate\Database\Eloquent\SoftDeletes" согласно конфигурации?
     *
     * @return void
     */
    public function test_uses_traits(): void
    {
        $traits      = collect(class_uses_recursive(app(Is::roleModel())));
        $hasUuids    = $traits->contains(HasUuids::class);
        $softDeletes = $traits->contains(SoftDeletes::class);
        $hasTraits   = function () use ($hasUuids, $softDeletes) {
            if (Is::usesUuid() && Is::usesSoftDeletes()) {
                return $hasUuids && $softDeletes;
            }

            if (Is::usesUuid()) {
                return $hasUuids && ! $softDeletes;
            }

            if (Is::usesSoftDeletes()) {
                return ! $hasUuids && $softDeletes;
            }

            return ! $hasUuids && ! $softDeletes;
        };

        $this->assertTrue($hasTraits());
    }
}
