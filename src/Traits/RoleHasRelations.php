<?php

namespace dmitryrogolev\Is\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait RoleHasRelations 
{
    /**
     * Возвращает модели, которые имеют данную роль
     *
     * @param string $related Имя модели
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roleables(string $related): MorphToMany
    {
        $query = $this->morphedByMany($related, config('is.relations.roleable'))->using(config('is.models.roleable'));

        return config('is.uses.timestamps') ? $query->withTimestamps() : $query;
    }
}
