<?php

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Contracts\Resourcable as ResourcableContract;
use dmitryrogolev\Services\Service;
use dmitryrogolev\Traits\Resourcable;

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
}
