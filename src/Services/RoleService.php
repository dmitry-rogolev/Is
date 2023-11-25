<?php 

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Contracts\RoleServicable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис работы с таблицей ролей.
 */
class RoleService extends Service implements RoleServicable
{
    public function __construct() 
    {
        $this->setModel(config('is.models.role'));
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
}
