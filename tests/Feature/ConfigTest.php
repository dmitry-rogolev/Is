<?php

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;

/**
 * Тестируем параметры конфигурации.
 */
class ConfigTest extends TestCase
{
    /**
     * Совпадает ли количество тестов с количеством переменных конфигурации?
     */
    public function test_count(): void
    {
        $FUNCTION = __FUNCTION__;
        $count = count(Arr::flatten(config('is')));
        $methods = (new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array_values(array_filter($methods, fn ($method) => str_starts_with($method->name, 'test_') && $method->name !== $FUNCTION
        ));

        $this->assertCount($count, $methods);
    }

    /**
     * Есть ли подключение к базе данных?
     */
    public function test_connection(): void
    {
        if (! config('is.connection')) {
            $this->markTestSkipped('Отсутствует конфигурация "is.connection"');
        }

        $result = DB::connection(config('is.connection'))->select('select "test" as test');
        $this->assertEquals('test', $result[0]->test);
    }

    /**
     * Есть ли конфигурация имени таблицы ролей?
     */
    public function test_tables_roles(): void
    {
        $this->assertTrue(is_string(config('is.tables.roles')));
        $this->assertNotEmpty(config('is.tables.roles'));
    }

    /**
     * Есть ли конфигурация имени промежуточной таблицы ролей?
     */
    public function test_tables_roleables(): void
    {
        $this->assertTrue(is_string(config('is.tables.roleables')));
        $this->assertNotEmpty(config('is.tables.roleables'));
    }

    /**
     * Есть ли конфигурация имени полиморфной связи промежуточной таблицы?
     */
    public function test_relations_roleable(): void
    {
        $this->assertTrue(is_string(config('is.relations.roleable')));
        $this->assertNotEmpty(config('is.relations.roleable'));
    }

    /**
     * Есть ли конфигурация имени первичного ключа?
     */
    public function test_primary_key(): void
    {
        $this->assertTrue(is_string(config('is.primary_key')));
        $this->assertNotEmpty(config('is.primary_key'));
    }

    /**
     * Есть ли конфигурация разделителя строк?
     */
    public function test_separator(): void
    {
        $this->assertTrue(is_string(config('is.separator')));
        $this->assertNotEmpty(config('is.separator'));
    }

    /**
     * Есть ли конфигурация имени модели роли?
     */
    public function test_models_role(): void
    {
        $this->assertTrue(class_exists(config('is.models.role')));
    }

    /**
     * Есть ли конфигурация имени модели промежуточной таблицы?
     */
    public function test_models_roleable(): void
    {
        $this->assertTrue(class_exists(config('is.models.roleable')));
    }

    /**
     * Есть ли конфигурация имени модели пользователя?
     */
    public function test_models_user(): void
    {
        $this->assertTrue(class_exists(config('is.models.user')));
    }

    /**
     * Есть ли конфигурация имени фабрики модели роли?
     */
    public function test_factories_role(): void
    {
        $this->assertTrue(class_exists(config('is.factories.role')));
    }

    /**
     * Есть ли конфигурация имени сидера модели роли?
     */
    public function test_seeders_role(): void
    {
        $this->assertTrue(class_exists(config('is.seeders.role')));
    }

    /**
     * Есть ли конфигурация флага использования UUID?
     */
    public function test_uses_uuid(): void
    {
        $this->assertTrue(is_bool(config('is.uses.uuid')));
    }

    /**
     * Есть ли конфигурация флага программного удаления моделей?
     */
    public function test_uses_soft_deletes(): void
    {
        $this->assertTrue(is_bool(config('is.uses.soft_deletes')));
    }

    /**
     * Есть ли конфигурация флага временных меток моделей?
     */
    public function test_uses_timestamps(): void
    {
        $this->assertTrue(is_bool(config('is.uses.timestamps')));
    }

    /**
     * Есть ли конфигурация флага регистрации миграций?
     */
    public function test_uses_migrations(): void
    {
        $this->assertTrue(is_bool(config('is.uses.migrations')));
    }

    /**
     * Есть ли конфигурация флага регистрации сидеров?
     */
    public function test_uses_seeders(): void
    {
        $this->assertTrue(is_bool(config('is.uses.seeders')));
    }

    /**
     * Есть ли конфигурация флага регистрации директив blade'а?
     */
    public function test_uses_blade(): void
    {
        $this->assertTrue(is_bool(config('is.uses.blade')));
    }

    /**
     * Есть ли конфигурация флага регистрации посредников?
     */
    public function test_uses_middlewares(): void
    {
        $this->assertTrue(is_bool(config('is.uses.middlewares')));
    }

    /**
     * Есть ли конфигурация флага подгрузки отношений после обновления?
     */
    public function test_uses_load_on_update(): void
    {
        $this->assertTrue(is_bool(config('is.uses.load_on_update')));
    }

    /**
     * Есть ли конфигурация флага расширения метода "is"?
     */
    public function test_uses_extend_is_method(): void
    {
        $this->assertTrue(is_bool(config('is.uses.extend_is_method')));
    }

    /**
     * Есть ли конфигурация флага использования иерархии ролей?
     */
    public function test_uses_levels(): void
    {
        $this->assertTrue(is_bool(config('is.uses.levels')));
    }
}
