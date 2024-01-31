<?php

namespace dmitryrogolev\Is\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Промежуточная модель полиморфного отношения многие-ко-многим.
 *
 * @link https://clck.ru/36JLPn Полиморфные отношения многие-ко-многим
 */
class Roleable extends MorphPivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('is.tables.roleables'));
    }
}
