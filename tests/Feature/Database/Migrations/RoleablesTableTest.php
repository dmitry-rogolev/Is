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
     *
     * @return void
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
     * Есть ли вневший ключ таблицы ролей у таблицы??
     *
     * @return void
     */
    public function test_has_foreign_key(): void 
    {
        $this->migration->up();
        $foreignKey = app(config('is.models.role'))->getForeignKey();
        $this->assertTrue(Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $foreignKey));
    }

    /**
     * Есть ли столбцы полиморфной связи?
     *
     * @return void
     */
    public function test_has_morph_columns(): void 
    {
        $rolable_id = config('is.relations.roleable').'_id';
        $rolable_type = config('is.relations.roleable').'_type';
        $checkId = fn () => Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $rolable_id);
        $checkType = fn () => Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roleables'), $rolable_type);

        $this->assertTrue($checkId && $checkType);
    }

    /**
     * Есть ли временные метки у таблицы?
     *
     * @return void
     */
    public function test_has_timestamps(): void 
    {
        $model = app(config('is.models.roleable'));
        $checkCreatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roleables'), $model->getCreatedAtColumn());
        $checkUpdatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roleables'), $model->getUpdatedAtColumn());
        $self = $this;

        $TEST = function () use ($self, $checkCreatedAt, $checkUpdatedAt) {
            if (config('is.uses.timestamps')) {
                $self->assertTrue($checkCreatedAt() && $checkUpdatedAt());
            } else {
                $self->assertFalse($checkCreatedAt() && $checkUpdatedAt());
            }
        };

        // Конфигурация по умолчанию.
        $this->migration->up();
        $TEST();
        $this->migration->down();
        
        // Меняем конфигурацию на обратную.
        config(['is.uses.timestamps' => ! config('is.uses.timestamps')]);
        $this->migration->up();
        $TEST();
    }
}
