<?php 

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Тестируем модель промежуточной таблицы ролей.
 */
class RoleableTest extends TestCase 
{
    /**
     * Расширяет ли модель класс "\Illuminate\Database\Eloquent\Relations\MorphPivot"?
     *
     * @return void
     */
    public function test_extends_morph_pivot(): void 
    {
        $this->assertInstanceOf(MorphPivot::class, app(config('is.models.roleable')));
    }

    /**
     * Совпадает ли имя соединения с БД в модели с конфигом?
     *
     * @return void
     */
    public function test_connection(): void 
    {
        $this->assertEquals(config('is.connection'), app(config('is.models.roleable'))->getConnectionName());
    }

    /**
     * Совпадает ли имя таблицы модели с конфигом?
     *
     * @return void
     */
    public function test_table(): void 
    {
        $this->assertEquals(config('is.tables.roleables'), app(config('is.models.roleable'))->getTable());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     *
     * @return void
     */
    public function test_timestamps(): void 
    {
        $this->assertEquals(config('is.uses.timestamps'), app(config('is.models.roleable'))->usesTimestamps());
    }
}
