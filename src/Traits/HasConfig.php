<?php 

namespace dmitryrogolev\Is\Traits;

/**
 * Конфигурация ролей.
 */
trait HasConfig 
{
    /**
     * Возвращает имя соединения к БД.
     *
     * @param string|null $value
     * @return string|null
     */
    public function connection(string|null $value = null): string|null
    {
        if (! is_null($value)) {
            config(['is.connection' => $value]);
        }

        return config('is.connection');
    }

    /**
     * Возвращает имя таблицы ролей.
     *
     * @param string|null $value
     * @return string
     */
    public function rolesTable(string|null $value = null): string
    {
        if (! is_null($value)) {
            config(['is.tables.roles' => $value]);
        }

        return config('is.tables.roles');
    }

    /**
     * Возвращает имя промежуточной таблицы ролей.
     *
     * @param string|null $value
     * @return string
     */
    public function roleablesTable(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.tables.roleables' => $value]);
        }

        return config('is.tables.roleables');
    }

    /**
     * Возвращает имя полиморфной связи.
     *
     * @param string|null $value
     * @return string
     */
    public function relationName(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.relations.roleable' => $value]);
        }

        return config('is.relations.roleable');
    }

    /**
     * Возвращает имя первичного ключа.
     *
     * @param string|null $value
     * @return string
     */
    public function primaryKey(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.primary_key' => $value]);
        }

        return config('is.primary_key');
    }

    /**
     * Возвращает имя модели роли.
     *
     * @param string|null $value
     * @return string
     */
    public function roleModel(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.models.role' => $value]);
        }

        return config('is.models.role');
    }

    /**
     * Возвращает имя промежуточной модели.
     *
     * @param string|null $value
     * @return string
     */
    public function roleableModel(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.models.roleable' => $value]);
        }

        return config('is.models.roleable');
    }

    /**
     * Возвращает имя модели пользователя.
     *
     * @param string|null $value
     * @return string
     */
    public function userModel(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.models.user' => $value]);
        }

        return config('is.models.user');
    }

    /**
     * Возвращает разделитель строк.
     *
     * @param string|null $value
     * @return string
     */
    public function separator(string|null $value = null): string 
    {
        if (! is_null($value)) {
            config(['is.separator' => $value]);
        }

        return config('is.separator');
    }

    /**
     * Используется ли в моделях UUID?
     *
     * @param bool|null $value
     * @return bool
     */
    public function usesUuid(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.uuid' => $value]);
        }

        return (bool) config('is.uses.uuid');
    }

    /**
     * Используется ли программное удаление моделей?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesSoftSeletes(bool|null $value = null): bool
    {
        if (! is_null($value)) {
            config(['is.uses.soft_deletes' => $value]);
        }

        return (bool) config('is.uses.soft_deletes');
    }

    /**
     * Используются ли временные метки в моделях?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesTimestamps(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.timestamps' => $value]);
        }

        return (bool) config('is.uses.timestamps');
    }

    /**
     * Используются ли миграции по умолчанию?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesMigrations(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.migrations' => $value]);
        }

        return (bool) config('is.uses.migrations');
    }

    /**
     * Используются ли сидеры по умолчанию?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesSeeders(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.seeders' => $value]);
        }

        return (bool) config('is.uses.seeders');
    }

    /**
     * Зарегистрированы ли директивы Blade?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesBlade(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.blade' => $value]);
        }

        return (bool) config('is.uses.blade');
    }

    /**
     * Зарегистрированы ли посредники?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesMiddlewares(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.middlewares' => $value]);
        }

        return (bool) config('is.uses.middlewares');
    }

    /**
     * Включена ли подгрузка отношений после изменения?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesLoadOnUpdate(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.load_on_update' => $value]);
        }

        return (bool) config('is.uses.load_on_update');
    }

    /**
     * Расширен ли метод "is" модели Eloquent?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesExtendIsMethod(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.extend_is_method' => $value]);
        }

        return (bool) config('is.uses.extend_is_method');
    }

    /**
     * Включена ли иерархия ролей?
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesLevels(bool|null $value = null): bool 
    {
        if (! is_null($value)) {
            config(['is.uses.levels' => $value]);
        }

        return (bool) config('is.uses.levels');
    }
}
