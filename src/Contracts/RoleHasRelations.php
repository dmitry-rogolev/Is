<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Добавляет роли отношения с другими моделями.
 */
interface RoleHasRelations 
{
    /**
     * Роль относится к множеству моделей.
     *
     * @param string $related Имя модели
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roleables(string $related): MorphToMany;
}
