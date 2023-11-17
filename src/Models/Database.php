<?php 

namespace dmitryrogolev\Is\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Базовый класс для всех моделей пакета. 
 */
abstract class Database extends Model 
{
    public function __construct(array $attributes = []) 
    {
        parent::__construct($attributes);

        $this->connection = config('is.connection', null);

        if (! config('is.primary_key')) {
            logger('Отсутствует конфигурация is.primary_key');
        }

        $this->primaryKey = config('is.primary_key', 'id');
        $this->timestamps = config('is.uses.timestamps', true);
    }
}
