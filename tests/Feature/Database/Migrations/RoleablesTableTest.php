<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * Тестируем миграцию промежуточной таблицы ролей.
 */
class RoleablesTableTest extends TestCase
{
    protected $migration;

    public function setUp(): void
    {
        parent::setUp();
        $this->migration = require __DIR__.'/../../../../database/migrations/create_roleables_table.php';
    }

    /**
     * Запускается и откатывается ли миграция?
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => Schema::connection(config('is.connection'))->hasTable(config('is.tables.roleables'));

        $this->migration->up();
        $this->assertTrue($checkTable());

        $this->migration->down();
        $this->assertFalse($checkTable());
    }

    /**
     * Есть ли внешний ключ таблицы ролей у таблицы?
     */
    public function test_has_foreign_key(): void
    {
        $this->migration->up();
        $foreignKey = app(config('is.models.role'))->getForeignKey();
        $this->assertTrue(Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $foreignKey));
    }

    /**
     * Есть ли столбцы полиморфной связи?
     */
    public function test_has_morph_columns(): void
    {
        $this->migration->up();

        $roleable_id = config('is.relations.roleable').'_id';
        $roleable_type = config('is.relations.roleable').'_type';
        $checkId = Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $roleable_id);
        $checkType = Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $roleable_type);

        $this->assertTrue($checkId && $checkType);
    }

    /**
     * Есть ли временные метки у таблицы?
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roleables'), app(config('is.models.roleable'))->getCreatedAtColumn());
        $hasUpdatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roleables'), app(config('is.models.roleable'))->getUpdatedAtColumn());

        // Включаем временные метки.
        config(['is.uses.timestamps' => true]);
        $this->migration->up();
        $this->assertTrue($hasCreatedAt() && $hasUpdatedAt());
        $this->migration->down();

        // Отключаем временные метки.
        config(['is.uses.timestamps' => false]);
        $this->migration->up();
        $this->assertTrue(! $hasCreatedAt() && ! $hasUpdatedAt());
    }
}
