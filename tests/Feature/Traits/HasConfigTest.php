<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\TestCase;
use dmitryrogolev\Is\Traits\HasConfig;

/**
 * Тестируем функционал доступа к конфигурации.
 */
class HasConfigTest extends TestCase
{
    /**
     * Есть ли метод, возвращающий и изменяющий соединение к БД?
     *
     * @return void
     */
    public function test_connection(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.connection'), Config::connection());

        // Изменяем конфигурацию.
        $value = 'mysql';
        Config::connection($value);
        $this->assertEquals($value, Config::connection());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя таблицы ролей?
     *
     * @return void
     */
    public function test_roles_table(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.tables.roles'), Config::rolesTable());

        // Изменяем конфигурацию.
        $value = 'tables';
        Config::rolesTable($value);
        $this->assertEquals($value, Config::rolesTable());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя промежуточной таблицы ролей?
     *
     * @return void
     */
    public function test_roleables_table(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.tables.roleables'), Config::roleablesTable());

        // Изменяем конфигурацию.
        $value = 'tables';
        Config::roleablesTable($value);
        $this->assertEquals($value, Config::roleablesTable());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя полиморфной связи?
     *
     * @return void
     */
    public function test_relation_name(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.relations.roleable'), Config::relationName());

        // Изменяем конфигурацию.
        $value = 'relation';
        Config::relationName($value);
        $this->assertEquals($value, Config::relationName());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя первичного ключа моделей?
     *
     * @return void
     */
    public function test_primary_key(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.primary_key'), Config::primaryKey());

        // Изменяем конфигурацию.
        $value = 'key';
        Config::primaryKey($value);
        $this->assertEquals($value, Config::primaryKey());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя модели роли?
     *
     * @return void
     */
    public function test_role_model(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.models.role'), Config::roleModel());

        // Изменяем конфигурацию.
        $value = 'Role';
        Config::roleModel($value);
        $this->assertEquals($value, Config::roleModel());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя модели промежуточной таблицы?
     *
     * @return void
     */
    public function test_roleable_model(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.models.roleable'), Config::roleableModel());

        // Изменяем конфигурацию.
        $value = 'Roleable';
        Config::roleableModel($value);
        $this->assertEquals($value, Config::roleableModel());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя модели пользователя?
     *
     * @return void
     */
    public function test_user_model(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.models.user'), Config::userModel());

        // Изменяем конфигурацию.
        $value = 'User';
        Config::userModel($value);
        $this->assertEquals($value, Config::userModel());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя фабрики роли?
     *
     * @return void
     */
    public function test_role_factory(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.factories.role'), Config::roleFactory());

        // Изменяем конфигурацию.
        $value = 'RoleFactory';
        Config::roleFactory($value);
        $this->assertEquals($value, Config::roleFactory());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий имя сидера роли?
     *
     * @return void
     */
    public function test_role_seeder(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.seeders.role'), Config::roleSeeder());

        // Изменяем конфигурацию.
        $value = 'RoleSeeder';
        Config::roleSeeder($value);
        $this->assertEquals($value, Config::roleSeeder());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий разделитель строк?
     *
     * @return void
     */
    public function test_separator(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.separator'), Config::separator());

        // Изменяем конфигурацию.
        $value = '*';
        Config::separator($value);
        $this->assertEquals($value, Config::separator());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг использования UUID в моделях?
     *
     * @return void
     */
    public function test_uses_uuid(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.uuid'), Config::usesUuid());

        // Изменяем конфигурацию.
        $value = ! Config::usesUuid();
        Config::usesUuid($value);
        $this->assertEquals($value, Config::usesUuid());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг программного удаления моделей?
     *
     * @return void
     */
    public function test_uses_soft_deletes(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.soft_deletes'), Config::usesSoftDeletes());

        // Изменяем конфигурацию.
        $value = ! Config::usesSoftDeletes();
        Config::usesSoftDeletes($value);
        $this->assertEquals($value, Config::usesSoftDeletes());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг использования временных меток в моделях?
     *
     * @return void
     */
    public function test_uses_timestamps(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.timestamps'), Config::usesTimestamps());

        // Изменяем конфигурацию.
        $value = ! Config::usesTimestamps();
        Config::usesTimestamps($value);
        $this->assertEquals($value, Config::usesTimestamps());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг использования миграций по умолчанию?
     *
     * @return void
     */
    public function test_uses_migrations(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.migrations'), Config::usesMigrations());

        // Изменяем конфигурацию.
        $value = ! Config::usesMigrations();
        Config::usesMigrations($value);
        $this->assertEquals($value, Config::usesMigrations());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг использования сидеров по умолчанию?
     *
     * @return void
     */
    public function test_uses_seeders(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.seeders'), Config::usesSeeders());

        // Изменяем конфигурацию.
        $value = ! Config::usesSeeders();
        Config::usesSeeders($value);
        $this->assertEquals($value, Config::usesSeeders());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг регистрации директив blade'а?
     *
     * @return void
     */
    public function test_uses_blade(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.blade'), Config::usesBlade());

        // Изменяем конфигурацию.
        $value = ! Config::usesBlade();
        Config::usesBlade($value);
        $this->assertEquals($value, Config::usesBlade());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг регистрации посредников?
     *
     * @return void
     */
    public function test_uses_middlewares(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.middlewares'), Config::usesMiddlewares());

        // Изменяем конфигурацию.
        $value = ! Config::usesMiddlewares();
        Config::usesMiddlewares($value);
        $this->assertEquals($value, Config::usesMiddlewares());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг подгрузки отношений после изменения?
     *
     * @return void
     */
    public function test_uses_load_on_update(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.load_on_update'), Config::usesLoadOnUpdate());

        // Изменяем конфигурацию.
        $value = ! Config::usesLoadOnUpdate();
        Config::usesLoadOnUpdate($value);
        $this->assertEquals($value, Config::usesLoadOnUpdate());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг расширения метода "is" модели Eloquent?
     *
     * @return void
     */
    public function test_uses_extend_is_method(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.extend_is_method'), Config::usesExtendIsMethod());

        // Изменяем конфигурацию.
        $value = ! Config::usesExtendIsMethod();
        Config::usesExtendIsMethod($value);
        $this->assertEquals($value, Config::usesExtendIsMethod());
    }

    /**
     * Есть ли метод, возвращающий и изменяющий флаг использования иерархии ролей?
     *
     * @return void
     */
    public function test_uses_levels(): void
    {
        // Сравниваем возвращаемое значение с конфигурацией.
        $this->assertEquals(config('is.uses.levels'), Config::usesLevels());

        // Изменяем конфигурацию.
        $value = ! Config::usesLevels();
        Config::usesLevels($value);
        $this->assertEquals($value, Config::usesLevels());
    }
}

class Base
{
    use HasConfig;
}

class Config
{
    public static function __callStatic($method, $args)
    {
        return (new Base)->{$method}(...$args);
    }
}
