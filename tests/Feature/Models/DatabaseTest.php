<?php

namespace dmitryrogolev\Is\Tests\Feature\Models;

use dmitryrogolev\Is\Facades\Is;
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
        $this->assertEquals(Is::connection(), app(Item::class)->getConnectionName());
    }

    /**
     * Совпадает ли имя первичного ключа модели с конфигом?
     *
     * @return void
     */
    public function test_primary_key(): void
    {
        $this->assertEquals(Is::primaryKey(), app(Item::class)->getKeyName());
    }

    /**
     * Совпадает ли флаг включения временных меток в модели с конфигом?
     *
     * @return void
     */
    public function test_timestamps(): void
    {
        $this->assertEquals(Is::usesTimestamps(), app(Item::class)->usesTimestamps());
    }
}

class Item extends Database
{

}
