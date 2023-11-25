<?php

namespace dmitryrogolev\Is\Traits;

use Illuminate\Support\Collection;

/**
 * Расширение метода "is", добавляющий ему проверку наличия роли у модели.
 */
trait ExtendIsMethod 
{
    /**
     * Определите, имеют ли две модели одинаковый идентификатор и принадлежат ли они к одной таблице.
     * 
     * Если передать роль, то будет вызван метод hasRole, проверяющий наличие роли у модели.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection $model
     * @param bool $all
     * @return bool
     */
    public function is($model, bool $all = false): bool
    {
        if (
            is_int($model) 
            || is_string($model) 
            || $model instanceof (config('is.models.role')) 
            || is_array($model) 
            || $model instanceof Collection
        ) {
            return $this->hasRole($model, $all);
        }

        return parent::is($model);
    }
}
