<?php 

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Is\Contracts\Roleable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с таблицей ролей.
 */
class RoleService extends Service
{
    public function __construct() 
    {
        $this->setModel(config('is.models.role'));
        $this->setSeeder(config('is.seeders.role'));
        $this->setFactory(config('is.factories.role'));
    }

    /**
     * Возвращает имя соединения к БД.
     *
     * @return string|null
     */
    public function connection(): string|null
    {
        return config('is.connection', null);
    }

    /**
     * Возвращает имя таблицы ролей.
     *
     * @return string
     */
    public function tableRoles(): string
    {
        return config('is.tables.roles');
    }

    /**
     * Возвращает имя промежуточной таблицы ролей.
     *
     * @return string
     */
    public function tableRoleables(): string 
    {
        return config('is.tables.roleables');
    }

    /**
     * Возвращает имя полиморфной связи.
     *
     * @return string
     */
    public function relationName(): string 
    {
        return config('is.relations.roleable');
    }

    /**
     * Возвращает имя первичного ключа.
     *
     * @return string
     */
    public function primaryKey(): string 
    {
        return config('is.primary_key');
    }

    /**
     * Возвращает имя промежуточной модели.
     *
     * @return string
     */
    public function getRoleableModel(): string 
    {
        return config('is.models.roleable');
    }

    /**
     * Возвращает имя модели пользователя.
     *
     * @return string
     */
    public function getUserModel(): string 
    {
        return config('is.models.user');
    }

    /**
     * Возвращает разделитель строк.
     *
     * @return string
     */
    public function separator(): string 
    {
        return config('is.separator');
    }

    /**
     * Используется ли в моделях UUID?
     *
     * @return bool
     */
    public function usesUuid(): bool 
    {
        return (bool) config('is.uses.uuid');
    }

    /**
     * Используется ли программное удаление моделей?
     *
     * @return boolean
     */
    public function usesSoftSeletes(): bool
    {
        return (bool) config('is.uses.soft_deletes');
    }

    /**
     * Используются ли временные метки в моделях?
     *
     * @return boolean
     */
    public function usesTimestamps(): bool 
    {
        return (bool) config('is.uses.timestamps');
    }

    /**
     * Включена ли подгрузка отношений после изменения?
     *
     * @return boolean
     */
    public function usesLoadOnUpdate(): bool 
    {
        return (bool) config('is.uses.load_on_update');
    }

    /**
     * Включена ли иерархия ролей?
     *
     * @return boolean
     */
    public function usesLevels(): bool 
    {
        return (bool) config('is.uses.levels');
    }

    /**
     * Возвращает все модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Roleable $roleable = null): Collection
    {
        return $roleable ? $this->getAllRoles($roleable) : parent::index();
    }

    /**
     * Возвращает все модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(Roleable $roleable = null): Collection
    {
        return $this->index($roleable);
    }

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($key, Roleable $roleable = null): Model|null 
    {
        $role = $this->role($key);

        if ($role && $roleable && ! $this->checkRole($roleable, $role)) {
            return null;
        }

        return $role;
    }

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($key, Roleable $roleable = null): Model|null 
    {
        return $this->show($key);
    }

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function makeIfNotExists(array $attributes = []): Model|null 
    {
        $model = app($this->model);

        if (array_key_exists($model->getSlugKey(), $attributes) && $this->has($attributes[$model->getSlugKey()])) {
            return null;
        }

        return $this->make($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function storeIfNotExists(array $attributes = []): Model|null 
    {
        $model = app($this->model);

        if (array_key_exists($model->getSlugKey(), $attributes) && $this->has($attributes[$model->getSlugKey()])) {
            return null;
        }

        return $this->store($attributes);
    }

    /**
     * Проверяет наличие роли у модели.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return bool
     */
    public function has($key, Roleable $roleable = null): bool 
    {
        return (bool) $this->show($key, $roleable);
    }

    /**
     * Проверить наличие роли у модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @param int|string|\Illuminate\Database\Eloquent\Model $role
     * @return bool
     */
    private function checkRole(Roleable $roleable, $role): bool 
    {
        if ($this->usesLevels()) {
            return $this->checkLevel($roleable, $role);
        }

        return $roleable->getRoles()->contains(fn ($item) => 
            $item->getKey() == $role 
            || $item->getSlug() == $role 
            || ($role instanceof ($this->model) && $item->is($role))
        );
    }

    /**
     * Проверить уровень доступа модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @param int|string|\Illuminate\Database\Eloquent\Model $role
     * @return boolean
     */
    private function checkLevel(Roleable $roleable, $role): bool 
    {
        if (! $this->usesLevels()) {
            return $this->checkRole($roleable, $role);
        }

        if (is_null($role = $this->role($role))) {
            return false;
        }

        return $roleable->level() >= $role->level;
    }

    /**
     * Возвращает роль по идентификатору или slug'у.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function role($key): Model|null 
    {
        $role = $key;

        if (is_int($key) || is_string($key)) {
            $model = app($this->model);
            $role = $this->model
                    ::where($model->getKeyName(), '=', $key)
                    ->orWhere($model->getSlugKey(), '=', $key)
                    ->first();
        }

        return $role instanceof ($this->model) && $role->exists ? $role : null;
    }

    /**
     * Возвращает все роли модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAllRoles(Roleable $roleable): Collection
    {
        return $this->usesLevels() ? $this->model::where('level', '<=', $roleable->level())->get() : $roleable->roles;
    }
}
