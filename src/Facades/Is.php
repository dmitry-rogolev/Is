<?php

namespace dmitryrogolev\Is\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Фасад работы с сервисом ролей.
 */
class Is extends Facade
{
    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \dmitryrogolev\Is\Services\RoleService::class;
    }
}
