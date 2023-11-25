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
    }

    /**
     * Возвращает все модели.
     *
     * @param \dmitryrogolev\Is\Contracts\Roleable $roleable
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Roleable $roleable = null): Collection
    {
        return $roleable ? $roleable->getRoles() : parent::index();
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
        if ($roleable && ! $roleable->hasRole($key)) {
            return null;
        }
        
        if ($key instanceof ($this->model) && $key->exists) {
            return $key;
        }

        if (is_int($key) || is_string($key)) {
            $model = app($this->model);
            return $this->model::where($model->getKeyName(), '=', $key)->orWhere($model->getSlugKey(), '=', $key)->first();
        }

        return null;
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
}
