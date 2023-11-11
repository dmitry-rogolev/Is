<?php

namespace dmitryrogolev\Is\Traits;

trait ExtendIsMethod 
{
    /**
     * Определите, имеют ли две модели одинаковый идентификатор и принадлежат ли они к одной таблице.
     * 
     * Если передать идентификатор, slug или модель роли, то будет вызван метод hasRole, 
     * проверяющий наличие роли у модели.
     *
     * @param mixed $model
     * @param bool $all
     * @return bool
     */
    public function is(mixed $model, bool $all = false)
    {
        if (is_int($model) || is_string($model) || $model instanceof (config('is.models.role'))) {
            return $this->hasRole($model, $all);
        }

        return parent::is($model);
    }
}
