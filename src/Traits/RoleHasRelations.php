<?php

namespace dmitryrogolev\Is\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Добавляет роли отношения с другими моделями.
 */
trait RoleHasRelations
{
    /**
     * Роль относится к множеству моделей.
     *
     * @param  string  $related  Имя модели.
     */
    public function roleables(string $related): MorphToMany
    {
        return $this->morphedByMany($related, config('is.relations.roleable'))->using(config('is.models.roleable'))->withTimestamps();
    }
}
