<?php 

namespace dmitryrogolev\Is\Facades;

use Illuminate\Support\Facades\Facade;

class Role extends Facade 
{
    /**
     * Получить зарегистрированное имя компонента.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'RoleService'; }
}
