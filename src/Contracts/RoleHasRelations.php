<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface RoleHasRelations 
{
    /**
     * Возвращает модели, которые имеют эту роль
     *
     * @param string $related Имя модели
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roleables(string $related): MorphToMany;
}
