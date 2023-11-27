<?php

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Traits\HasConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с таблицей ролей.
 */
class RoleService extends Service
{
    use HasConfig;

    public function __construct()
    {
        $this->setModel(config('is.models.role'));
        $this->setSeeder(config('is.seeders.role'));
        $this->setFactory(config('is.factories.role'));
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
        return $this->show($key, $roleable);
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

        return $roleable->getRoles()->contains(
            fn ($item) =>
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
            $role  = $this->model
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
