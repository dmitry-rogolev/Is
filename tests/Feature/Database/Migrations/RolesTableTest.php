<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * Тестируем миграцию таблицы ролей.
 */
class RolesTableTest extends TestCase
{
    protected $migration;

    public function setUp(): void
    {
        parent::setUp();
        $this->migration = require __DIR__.'/../../../../database/migrations/create_roles_table.php';
    }

    /**
     * Запускается и откатывается ли миграция?
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => Schema::connection(config('is.connection'))->hasTable(config('is.tables.roles'));

        $this->migration->up();
        $this->assertTrue($checkTable());

        $this->migration->down();
        $this->assertFalse($checkTable());
    }

    /**
     * Есть ли идентификатор у таблицы?
     */
    public function test_has_id(): void
    {
        $this->migration->up();
        $this->assertTrue(Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roles'), config('is.primary_key')));
    }

    /**
     * Есть ли поле "level" у таблицы?
     */
    public function test_has_level(): void
    {
        $this->migration->up();
        $this->assertTrue(Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roles'), 'level'));
    }

    /**
     * Есть ли временные метки у таблицы?
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roles'), app(config('is.models.role'))->getCreatedAtColumn());
        $hasUpdatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roles'), app(config('is.models.role'))->getUpdatedAtColumn());

        // Включаем временные метки
        config(['is.uses.timestamps' => true]);
        $this->migration->up();
        $this->assertTrue($hasCreatedAt() && $hasUpdatedAt());
        $this->migration->down();

        // Отключаем временные метки.
        config(['is.uses.timestamps' => false]);
        $this->migration->up();
        $this->assertTrue(! $hasCreatedAt() && ! $hasUpdatedAt());
    }

    /**
     * Есть ли у таблицы поле программного удаления?
     */
    public function test_has_deleted_at(): void
    {
        $checkDeletedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roles'), app(config('is.models.role'))->getDeletedAtColumn());

        // Включаем программное удаление.
        Is::usesSoftDeletes(true);
        $this->migration->up();
        $this->assertTrue($checkDeletedAt());
        $this->migration->down();

        // Отключаем программное удаление.
        Is::usesSoftDeletes(false);
        $this->migration->up();
        $this->assertTrue(! $checkDeletedAt());
    }
}
