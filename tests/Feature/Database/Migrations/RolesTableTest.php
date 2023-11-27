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
        $this->migration = require __DIR__ . '/../../../../database/migrations/create_roles_table.php';
    }

    /**
     * Запускается и откатывается ли миграция?
     *
     * @return void
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => Schema::connection(Is::connection())->hasTable(Is::rolesTable());

        $this->migration->up();
        $this->assertTrue($checkTable());

        $this->migration->down();
        $this->assertFalse($checkTable());
    }

    /**
     * Есть ли идентификатор у таблицы?
     *
     * @return void
     */
    public function test_has_id(): void
    {
        $this->migration->up();
        $this->assertTrue(Schema::connection(Is::connection())->hasColumn(Is::rolesTable(), Is::primaryKey()));
    }

    /**
     * Есть ли поле "level" у таблицы?
     *
     * @return void
     */
    public function test_has_level(): void
    {
        $this->migration->up();
        $this->assertTrue(Schema::connection(Is::connection())->hasColumn(Is::rolesTable(), 'level'));
    }

    /**
     * Есть ли временные метки у таблицы?
     *
     * @return void
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => Schema::connection(Is::connection())
            ->hasColumn(Is::rolesTable(), app(Is::roleModel())->getCreatedAtColumn());
        $hasUpdatedAt = fn () => Schema::connection(Is::connection())
            ->hasColumn(Is::rolesTable(), app(Is::roleModel())->getUpdatedAtColumn());

        // Включаем временные метки
        Is::usesTimestamps(true);
        $this->migration->up();
        $this->assertTrue($hasCreatedAt() && $hasUpdatedAt());
        $this->migration->down();

        // Отключаем временные метки.
        Is::usesTimestamps(false);
        $this->migration->up();
        $this->assertTrue(! $hasCreatedAt() && ! $hasUpdatedAt());
    }

    /**
     * Есть ли у таблицы поле программного удаления?
     *
     * @return void
     */
    public function test_has_deleted_at(): void
    {
        $checkDeletedAt = fn () => Schema::connection(Is::connection())
            ->hasColumn(Is::rolesTable(), app(Is::roleModel())->getDeletedAtColumn());

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
