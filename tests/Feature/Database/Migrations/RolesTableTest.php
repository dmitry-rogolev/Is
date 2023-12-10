<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Migrations\Migration;

/**
 * Тестируем миграцию таблицы ролей.
 */
class RolesTableTest extends TestCase
{
    /**
     * Класс миграции.
     */
    protected Migration $migration;

    /**
     * Имя таблицы.
     */
    protected string $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->migration = require __DIR__.'/../../../../database/migrations/create_roles_table.php';
        $this->table = config('is.tables.roles');
    }

    /**
     * Запускается и откатывается ли миграция?
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => $this->schema()->hasTable($this->table);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем создание таблицы.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $this->migration->up();
        $this->assertTrue($checkTable());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем удаление таблицы.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $this->migration->down();
        $this->assertFalse($checkTable());
    }

    /**
     * Есть ли первичный ключ у таблицы?
     */
    public function test_has_id(): void
    {
        $this->migration->up();
        $hasPrimaryKey = $this->schema()->hasColumn($this->table, config('is.primary_key'));

        $this->assertTrue($hasPrimaryKey);
    }

    /**
     * Есть ли временные метки у таблицы?
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => $this->schema()
            ->hasColumn($this->table, app(config('is.models.role'))->getCreatedAtColumn());
        $hasUpdatedAt = fn () => $this->schema()
            ->hasColumn($this->table, app(config('is.models.role'))->getUpdatedAtColumn());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем наличие временных меток.                     ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => true]);
        $this->migration->up();
        $this->assertTrue($hasCreatedAt() && $hasUpdatedAt());
        $this->migration->down();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Подтверждаем отсутствие временных меток.                    ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => false]);
        $this->migration->up();
        $this->assertTrue(! $hasCreatedAt() && ! $hasUpdatedAt());
    }

    /**
     * Есть ли у таблицы поле программного удаления?
     */
    public function test_has_deleted_at(): void
    {
        $checkDeletedAt = fn () => $this->schema()
            ->hasColumn($this->table, app(config('is.models.role'))->getDeletedAtColumn());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем наличие временно метки программного удаления.           ||
        // ! ||--------------------------------------------------------------------------------||
        config(['is.uses.soft_deletes' => true]);
        $this->migration->up();
        $this->assertTrue($checkDeletedAt());
        $this->migration->down();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Подтверждаем отсутствие временной метки программного удаления.         ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.soft_deletes' => false]);
        $this->migration->up();
        $this->assertTrue(! $checkDeletedAt());
    }
}
