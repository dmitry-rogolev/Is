<?php

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Eloquent\Model;

/**
 * Базовый класс для всех моделей пакета. 
 */
abstract class Database extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(Is::connection());
        $this->setKeyName(Is::primaryKey());
        $this->timestamps = Is::usesTimestamps();
    }
}
