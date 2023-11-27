<?php

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Добавляет роли отношения с другими моделями.
 */
trait RoleHasRelations
{
    /**
     * Роль относится к множеству моделей.
     *
     * @param string $related Имя модели
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roleables(string $related): MorphToMany
    {
        $query = $this->morphedByMany($related, Is::relationName())->using(Is::roleableModel());

        return Is::usesTimestamps() ? $query->withTimestamps() : $query;
    }
}
