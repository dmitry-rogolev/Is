<?php

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Contracts\Resourcable as ResourcableContract;
use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Services\Service;
use dmitryrogolev\Traits\Resourcable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с таблицей ролей.
 */
class RoleService extends Service implements ResourcableContract
{
    use Resourcable;

    public function __construct()
    {
        parent::__construct();

        $this->setModel(config('is.models.role'));
        $this->setSeeder(config('is.seeders.role'));
    }

    /**
     * Возвращает коллекцию всех моделей таблицы.
     */
    public function index(?Roleable $roleable = null): Collection
    {
        return is_null($roleable) ? $this->all() : $this->getAllRoles($roleable);
    }

    // /**
    //  * Возвращает модель по ее идентификатору.
    //  *
    //  * @param  int|string|\Illuminate\Database\Eloquent\Model  $key
    //  */
    // public function show($key, ?Roleable $roleable = null): ?Model
    // {
    //     $role = $this->role($key);

    //     if ($role && $roleable && ! $this->checkRole($roleable, $role)) {
    //         return null;
    //     }

    //     return $role;
    // }

    // /**
    //  * Создать модель и сохранить ее в таблицу, если ее не существует.
    //  */
    // public function makeIfNotExists(array $attributes = []): ?Model
    // {
    //     $model = app($this->model);

    //     if (array_key_exists($model->getSlugName(), $attributes) && $this->has($attributes[$model->getSlugName()])) {
    //         return null;
    //     }

    //     return $this->make($attributes);
    // }

    // /**
    //  * Создать модель и сохранить ее в таблицу, если ее не существует.
    //  */
    // public function storeIfNotExists(array $attributes = []): ?Model
    // {
    //     $model = app($this->model);

    //     if (array_key_exists($model->getSlugName(), $attributes) && $this->has($attributes[$model->getSlugName()])) {
    //         return null;
    //     }

    //     return $this->store($attributes);
    // }

    // /**
    //  * Проверяет наличие роли у модели.
    //  *
    //  * @param  int|string|\Illuminate\Database\Eloquent\Model  $key
    //  */
    // public function has($key, ?Roleable $roleable = null): bool
    // {
    //     return (bool) $this->show($key, $roleable);
    // }

    /**
     * Проверить наличие роли у модели.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $role
     */
    private function checkRole(Roleable $roleable, $role): bool
    {
        if (config('is.uses.levels')) {
            return $this->checkLevel($roleable, $role);
        }

        return $roleable->getRoles()->contains(
            fn ($item) => $item->getKey() == $role
            || $item->getSlug() == $role
            || ($role instanceof ($this->model) && $item->is($role))
        );
    }

    /**
     * Проверить уровень доступа модели.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $role
     */
    private function checkLevel(Roleable $roleable, $role): bool
    {
        if (! config('is.uses.levels')) {
            return $this->checkRole($roleable, $role);
        }

        if (! ($role instanceof Model) && is_null($role = $this->role($role))) {
            return false;
        }

        return $roleable->level() >= $role->level;
    }

    /**
     * Возвращает все роли модели.
     */
    private function getAllRoles(Roleable $roleable): Collection
    {
        return config('is.uses.levels') ? $this->model::where('level', '<=', $roleable->level())->get() : $roleable->roles;
    }
}
