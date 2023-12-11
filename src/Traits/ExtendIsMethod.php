<?php

namespace dmitryrogolev\Is\Traits;

use Illuminate\Contracts\Support\Arrayable;

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
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  $model
     */
    public function is($model, bool $all = false): bool
    {
        if (
            config('is.uses.extend_is_method') && (
                is_int($model)
                || is_string($model)
                || is_a($model, config('is.models.role'))
                || is_array($model)
                || $model instanceof Arrayable
            )
        ) {
            return $this->hasRole($model, $all);
        }

        return parent::is($model);
    }
}
