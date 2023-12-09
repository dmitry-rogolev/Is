<?php

namespace dmitryrogolev\Is\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Фасад для работы с таблицей ролей.
 */
class Is extends Facade
{
    /**
     * Получить зарегистрированное имя компонента.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \dmitryrogolev\Is\Services\RoleService::class;
    }
}
