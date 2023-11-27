<?php

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

use dmitryrogolev\Is\Facades\Is;
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
        $this->migration = require __DIR__ . '/../../../../database/migrations/create_roleables_table.php';
    }

    /**
     * Запускается и откатывается ли миграция?
     *
     * @return void
     */
    public function test_up_down(): void
    {
        $checkTable = fn () => Schema::connection(Is::connection())->hasTable(Is::roleablesTable());

        $this->migration->up();
        $this->assertTrue($checkTable());

        $this->migration->down();
        $this->assertFalse($checkTable());
    }

    /**
     * Есть ли внешний ключ таблицы ролей у таблицы?
     *
     * @return void
     */
    public function test_has_foreign_key(): void
    {
        $this->migration->up();
        $foreignKey = app(Is::roleModel())->getForeignKey();
        $this->assertTrue(Schema::connection(Is::connection())->hasColumn(Is::roleablesTable(), $foreignKey));
    }

    /**
     * Есть ли столбцы полиморфной связи?
     *
     * @return void
     */
    public function test_has_morph_columns(): void
    {
        $this->migration->up();

        $roleable_id   = Is::relationName() . '_id';
        $roleable_type = Is::relationName() . '_type';
        $checkId       = Schema::connection(Is::connection())->hasColumn(Is::roleablesTable(), $roleable_id);
        $checkType     = Schema::connection(Is::connection())->hasColumn(Is::roleablesTable(), $roleable_type);

        $this->assertTrue($checkId && $checkType);
    }

    /**
     * Есть ли временные метки у таблицы?
     *
     * @return void
     */
    public function test_has_timestamps(): void
    {
        $hasCreatedAt = fn () => Schema::connection(Is::connection())
            ->hasColumn(Is::roleablesTable(), app(Is::roleableModel())->getCreatedAtColumn());
        $hasUpdatedAt = fn () => Schema::connection(Is::connection())
            ->hasColumn(Is::roleablesTable(), app(Is::roleableModel())->getUpdatedAtColumn());

        // Включаем временные метки.
        Is::usesTimestamps(true);
        $this->migration->up();
        $this->assertTrue($hasCreatedAt() && $hasUpdatedAt());
        $this->migration->down();

        // Отключаем временные метки.
        Is::usesTimestamps(false);
        $this->migration->up();
        $this->assertTrue(! $hasCreatedAt() && ! $hasUpdatedAt());
    }
}
