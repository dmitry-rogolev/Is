<?php

namespace dmitryrogolev\Is\Traits;

/**
 * Конфигурация ролей.
 */
trait HasConfig
{
    /**
     * * Подключение к БД, которое должен использовать пакет.
     * 
     * Список возможных подключений определен в файле конфигурации "config/database.php".
     * По умолчанию используется подключение к приложению по умолчанию.
     * 
     * @link https://clck.ru/36LkBo Конфигурирование БД
     *
     * @param string|null $value
     * @return string|null
     */
    public function connection(string|null $value = null): string|null
    {
        if (!is_null($value)) {
            config(['is.connection' => $value]);
        }

        return config('is.connection');
    }

    /**
     * * Имя таблицы ролей.
     *
     * @param string|null $value
     * @return string
     */
    public function rolesTable(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.tables.roles' => $value]);
        }

        return config('is.tables.roles');
    }

    /**
     * * Имя промежуточной таблицы, которая соединяет модели, использующие трейт HasRoles, с ролями.
     * 
     * @link https://clck.ru/36JLPn Полиморфные отношения многие-ко-многим
     *
     * @param string|null $value
     * @return string
     */
    public function roleablesTable(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.tables.roleables' => $value]);
        }

        return config('is.tables.roleables');
    }

    /**
     * * Имя полиморфной связи моделей.
     * 
     * Используется в промежуточной таблице для полей {relation_name}_id и {relation_name}_type.
     * Например, roleable_id и roleable_type.
     * 
     * В поле {relation_name}_id указывается идентификатор модели, которая связывается с ролью.
     * В поле {relation_name}_type указывается полное название модели, 
     * например "\App\Models\Role", которая связывается с ролью.
     * 
     * @link https://clck.ru/36JLPn Полиморфные отношения многие-ко-многим
     *
     * @param string|null $value
     * @return string
     */
    public function relationName(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.relations.roleable' => $value]);
        }

        return config('is.relations.roleable');
    }

    /**
     * * Имя первичного ключа моделей
     * 
     * Первичный ключ - это поле в таблице, которое хранит уникальное значение, 
     * по которому можно явно идентфицировать ту или иную запись в таблице.
     * 
     * @link https://clck.ru/36Ln4n Первичный ключ модели Eloquent
     *
     * @param string|null $value
     * @return string
     */
    public function primaryKey(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.primary_key' => $value]);
        }

        return config('is.primary_key');
    }

    /**
     * * Имя модели роли.
     *
     * @param string|null $value
     * @return string
     */
    public function roleModel(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.models.role' => $value]);
        }

        return config('is.models.role');
    }

    /**
     * * Имя промежуточной модели.
     *
     * @param string|null $value
     * @return string
     */
    public function roleableModel(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.models.roleable' => $value]);
        }

        return config('is.models.roleable');
    }

    /**
     * * Имя модели пользователя.
     *
     * @param string|null $value
     * @return string
     */
    public function userModel(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.models.user' => $value]);
        }

        return config('is.models.user');
    }

    /**
     * * Имя фабрики роли.
     *
     * @param string|null|null $value
     * @return string
     */
    public function roleFactory(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.factories.role' => $value]);
        }

        return config('is.factories.role');
    }

    /**
     * * Имя сидера роли.
     *
     * @param string|null|null $value
     * @return string
     */
    public function roleSeeder(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.seeders.role' => $value]);
        }

        return config('is.seeders.role');
    }

    /**
     * * Строковый разделитель. 
     * 
     * Используется для раделения строк на подстроки для поля slug.
     *
     * @param string|null $value
     * @return string
     */
    public function separator(string|null $value = null): string
    {
        if (!is_null($value)) {
            config(['is.separator' => $value]);
        }

        return config('is.separator');
    }

    /**
     * * Использовать ли в моделях uuid вместо обычного id.
     * 
     * UUID — это универсальные уникальные буквенно-цифровые идентификаторы длиной 36 символов.
     * 
     * @link https://clck.ru/36JNiT UUID
     *
     * @param bool|null $value
     * @return bool
     */
    public function usesUuid(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.uuid' => $value]);
        }

        return (bool) config('is.uses.uuid');
    }

    /**
     * * Использовать ли программное удаление для моделей. 
     * 
     * Помимо фактического удаления записей из БД, 
     * Eloquent может выполнять «программное удаление» моделей. 
     * При таком удалении, они фактически не удаляются из БД.
     * Вместо этого для каждой модели устанавливается атрибут deleted_at, 
     * указывающий дату и время, когда она была «удалена».
     * 
     * @link https://clck.ru/36JNnr Программное удаление моделей
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesSoftDeletes(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.soft_deletes' => $value]);
        }

        return (bool) config('is.uses.soft_deletes');
    }

    /**
     * * Использовать ли временные метки для моделей. 
     * 
     * По умолчанию модели Eloquent определяют поля "created_at" и "updated_at", 
     * в которых хранятся дата и время создания и изменения модели соответственно.
     * 
     * Если вы не хотите, чтобы модели имели временные метки, установите данный флаг в false.
     * 
     * @link https://clck.ru/36JNke Временные метки моделей
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesTimestamps(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.timestamps' => $value]);
        }

        return (bool) config('is.uses.timestamps');
    }

    /**
     * * Использовать ли миграции по умолчанию.
     * 
     * Если вы не публикуете или не создаете свои миграции таблиц для этого пакета, 
     * то установите данный флаг в true.
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesMigrations(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.migrations' => $value]);
        }

        return (bool) config('is.uses.migrations');
    }

    /**
     * * Использовать ли сидеры по умолчанию.
     * 
     * Если вы хотитите использовать сидеры по умолчанию, установите данный флаг в true.
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesSeeders(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.seeders' => $value]);
        }

        return (bool) config('is.uses.seeders');
    }

    /**
     * * Регистрировать ли дериктивы blade (is, endis, role, endrole, level, endlevel).
     * 
     * Директивы is и role предоставляют одинаковый функционал. 
     * 
     * Эти дериктывы применимы только к модели пользователя, 
     * использующего трейт "\dmitryrogolev\Is\Traits\HasRoles".
     * 
     * @link https://clck.ru/36Ls42 Директивы Blade
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesBlade(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.blade' => $value]);
        }

        return (bool) config('is.uses.blade');
    }

    /**
     * * Регистировать ли посредники (is, role, level).
     * 
     * Посредники is и role предоставляют одинаковый функционал.
     * 
     * Эти посредники применимы только к модели пользователя, 
     * использующего трейт "\dmitryrogolev\Is\Traits\HasRoles".
     * 
     * @link https://clck.ru/36LsKF Посредники
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesMiddlewares(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.middlewares' => $value]);
        }

        return (bool) config('is.uses.middlewares');
    }

    /**
     * * Следует ли подгружать отношение модели после изменения. 
     * 
     * По умолчанию после подключения или удаления отношения(-ий) моделей с ролями, 
     * отношения будут подгружены заного. 
     * Это означает, что модель всегда будет хранить актуальные отношения, 
     * однако также это означает увеличение количества запросов к базе данных. 
     * 
     * Если вы делаете много опираций с ролями, 
     * рекомендуется отключить данную функцию для увеличения производительности.
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesLoadOnUpdate(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.load_on_update' => $value]);
        }

        return (bool) config('is.uses.load_on_update');
    }

    /**
     *  * Следует ли расширять метод "is" модели Eloquent.
     * 
     * Метод is по умолчанию сравнивает две модели. 
     * Трейт HasRoles расширяет данный метод. 
     * Это означает, что данным методом по прежнему можно будет пользоваться для сравнения моделей, 
     * но, если передать идентификатор, slug или модель роли, то будет вызван метод hasRole, 
     * проверяющий наличие роли у модели.
     * 
     * Если вы не хотите, чтобы данный метод был расширен, установите данный флаг в false.
     * 
     * @link https://clck.ru/36LeCR Метод is модели Eloquent
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesExtendIsMethod(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.extend_is_method' => $value]);
        }

        return (bool) config('is.uses.extend_is_method');
    }

    /**
     * * Использовать ли иерархию ролей на основе уровней. 
     * 
     * Иерархия подразумевает, что вышестоящая в иерархии роль иммеет допуск 
     * к функционалу нижестоящих относительно нее ролей.
     * Например, если модель имеет роль с уровенем 5, 
     * то проверка наличия роли с уровнем 3 будет положительна. 
     * 
     * $user->attachRole($admin); // level 3
     * 
     * $user->hasRole($moderator); // level 2 // true 
     * 
     * Если эта функция включена, то вам не придется добалять пользователю все роли, 
     * которые ему необходимы, а будет достаточно добавить только одну вышестоящую в иерархии роль.
     *
     * @param bool|null $value
     * @return boolean
     */
    public function usesLevels(bool|null $value = null): bool
    {
        if (!is_null($value)) {
            config(['is.uses.levels' => $value]);
        }

        return (bool) config('is.uses.levels');
    }
}
