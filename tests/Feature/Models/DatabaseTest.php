<?php 

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестирование базового класса для всех моделей пакета.
 */
class DatabaseTest extends TestCase 
{
    /**
     * Совпадает ли имя соединения с БД в модели с конфигом?
     *
     * @return void
     */
    public function test_connection(): void 
    {
        $this->assertEquals(config('is.connection'), $this->model()->getConnectionName());
    }

    /**
     * Совпадает ли имя первичного ключа модели с конфигом?
     *
     * @return void
     */
    public function test_primary_key(): void 
    {
        $this->assertEquals(config('is.primary_key'), $this->model()->getKeyName());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     *
     * @return void
     */
    public function test_timestamps(): void 
    {
        $this->assertEquals(config('is.uses.timestamps'), $this->model()->usesTimestamps());
    }

    /**
     * Возвращает модель, которая наследуется от базовой модели Database.
     *
     * @return \dmitryrogolev\Is\Tests\Feature\Models\Item
     */
    public function model(): Item 
    {
        return new Item;
    }
}

class Item extends Database 
{

}
