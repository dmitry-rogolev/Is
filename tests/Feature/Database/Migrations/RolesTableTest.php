<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Имя модели ролей.
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

    /**
     * Имя временной метки удаления записи.
     */
    protected string $deletedAt;

    public function setUp(): void
    {
        parent::setUp();

        $this->migration = require __DIR__.'/../../../../database/migrations/create_roles_table.php';
        $this->table = config('is.tables.roles');
        $this->model = config('is.models.role');
        $this->createdAt = app($this->model)->getCreatedAtColumn();
        $this->updatedAt = app($this->model)->getUpdatedAtColumn();
        $this->deletedAt = app($this->model)->getDeletedAtColumn();
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
     * Есть ли первичный ключ у таблицы?
     */
    public function test_has_id(): void
    {
        $this->migration->up();
        $hasPrimaryKey = Schema::hasColumn($this->table, 'id');

        $this->assertTrue($hasPrimaryKey);
    }
}
