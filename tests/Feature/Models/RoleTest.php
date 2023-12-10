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
     * Совпадает ли имя соединения с БД в модели с конфигом?
     */
    public function test_connection(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertEquals(config('is.connection'), $role->getConnectionName());
    }

    /**
     * Совпадает ли имя первичного ключа модели с конфигом?
     */
    public function test_primary_key(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertEquals(config('is.primary_key'), $role->getKeyName());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     */
    public function test_timestamps(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertEquals(config('is.uses.timestamps'), $role->usesTimestamps());
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     */
    public function test_table(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertEquals(config('is.tables.roles'), $role->getTable());
    }

    /**
     * Реализует ли модель интерфейс отношений роли?
     */
    public function test_implements_role_has_relations(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertInstanceOf(RoleHasRelations::class, $role);
    }

    /**
     * Реализует ли модель интерфейс функционала, облегчающего работу с аттрибутом "slug"?
     */
    public function test_implements_sluggable(): void
    {
        $role = $this->generate(config('is.models.role'), false);

        $this->assertInstanceOf(Sluggable::class, $role);
    }

    /**
     * Совпадает ли фабрика модели с конфигурацией?
     */
    public function test_factory(): void
    {
        $this->assertEquals(config('is.factories.role'), config('is.models.role')::factory()::class);
    }

    /**
     * Подключены ли трейты "\Illuminate\Database\Eloquent\Concerns\HasUuids"
     * и "\Illuminate\Database\Eloquent\SoftDeletes" согласно конфигурации?
     */
    public function test_uses_traits(): void
    {
        $role = app(config('is.models.role'));
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
