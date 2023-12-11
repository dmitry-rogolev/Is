<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Тестируем миграцию промежуточной таблицы ролей.
 */
class RoleablesTableTest extends TestCase
{
    /**
     * Класс миграции.
     */
    protected Migration $migration;

    /**
     * Имя таблицы.
     */
    protected string $table;

    /**
     * Имя полиморфной связи.
     */
    protected string $relationName;

    /**
     * Имя модели.
     */
    protected string $model;

    /**
     * Имя временной метки создания записи.
     */
    protected string $createdAt;

    /**
     * Имя временной метки обновления записи.
     */
    protected string $updatedAt;

    public function setUp(): void
    {
        parent::setUp();

        $this->migration = require __DIR__.'/../../../../database/migrations/create_roleables_table.php';
        $this->table = config('is.tables.roleables');
        $this->relationName = config('is.relations.roleable');
        $this->model = config('is.models.roleable');
        $this->createdAt = app($this->model)->getCreatedAtColumn();
        $this->updatedAt = app($this->model)->getUpdatedAtColumn();
    }

    /**
     * Запускается и откатывается ли миграция?
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => Schema::hasTable($this->table);

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
     * Есть ли внешний ключ таблицы ролей?
     */
    public function test_has_foreign_key(): void
    {
        $this->migration->up();

        $foreignKey = app(config('is.models.role'))->getForeignKey();
        $hasForeignKey = Schema::hasColumn($this->table, $foreignKey);

        $this->assertTrue($hasForeignKey);
    }

    /**
     * Есть ли столбцы полиморфной связи?
     */
    public function test_has_morph_columns(): void
    {
        $this->migration->up();

        $roleable_id = $this->relationName.'_id';
        $roleable_type = $this->relationName.'_type';
        $checkId = Schema::hasColumn($this->table, $roleable_id);
        $checkType = Schema::hasColumn($this->table, $roleable_type);

        $this->assertTrue($checkId && $checkType);
    }

    /**
     * Есть ли временные метки у таблицы?
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => Schema::hasColumn($this->table, $this->createdAt);
        $hasUpdatedAt = fn () => Schema::hasColumn($this->table, $this->updatedAt);

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
}
