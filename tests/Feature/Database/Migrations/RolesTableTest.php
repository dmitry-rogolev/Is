<?php 

namespace dmitryrogolev\Is\Tests\Feature\Database\Migrations;

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
     *
     * @return void
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
     *
     * @return void
     */
    public function test_has_id(): void 
    {
        $this->migration->up();
        $this->assertTrue(Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roles'), config('is.primary_key')));
    }

    /**
     * Есть ли поле "level" у таблицы?
     *
     * @return void
     */
    public function test_has_level(): void 
    {
        $checkColumn = fn () => Schema::connection(config('is.connection'))->hasColumn(config('is.tables.roles'), 'level');
        $TEST = fn () => config('is.uses.levels') ? $this->assertTrue($checkColumn()) : $this->assertFalse($checkColumn());

        // Конфигурация по умолчанию.
        $this->migration->up();
        $TEST();
        $this->migration->down();

        // Меняем конфигурацию на обратную.
        config(['is.uses.levels' => ! config('is.uses.levels')]);
        $this->migration->up();
        $TEST();
    }

    /**
     * Есть ли временные метки у таблицы?
     *
     * @return void
     */
    public function test_has_timestamps(): void 
    {
        $model = app(config('is.models.role'));
        $checkCreatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roles'), $model->getCreatedAtColumn());
        $checkUpdatedAt = fn () => Schema::connection(config('is.connection'))
            ->hasColumn(config('is.tables.roles'), $model->getUpdatedAtColumn());
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

    /**
     * Есть ли у таблицы поле программного удаления?
     *
     * @return void
     */
    public function test_has_deleted_at(): void 
    {
        $model = app(config('is.models.role'));
        $checkDeletedAt = fn () => Schema::connection(config('is.connection'))
        ->hasColumn(config('is.tables.roles'), $model->getDeletedAtColumn());

        $TEST = fn () => config('is.uses.soft_deletes') ? $this->assertTrue($checkDeletedAt()) : $this->assertFalse($checkDeletedAt());

        // Конфигурация по умолчанию.
        $this->migration->up();
        $TEST();
        $this->migration->down();
        
        // Меняем конфигурацию на обратную.
        config(['is.uses.soft_deletes' => ! config('is.uses.soft_deletes')]);
        $this->migration->up();
        $TEST();
    }
}
