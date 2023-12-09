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
     * @param  string  $related Имя модели
     */
    public function roleables(string $related): MorphToMany;
}
