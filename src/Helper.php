<?php 

namespace dmitryrogolev\Is;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Helper 
{
    /**
     * Преобразует значение в объект "\Illuminate\Support\Stringable".
     *
     * @param mixed $value
     * @return \Illuminate\Support\Stringable
     */
    public static function str($value = ''): Stringable 
    {
        $value = value($value);

        if ($value instanceof Stringable) {
            return $value;
        }

        return is_string($value) ? Str::of($value) : Str::of('');
    }

    /**
     * Преобразует строку в slug
     *
     * @param  mixed $value
     * @return string
     */
    public static function slug($value): string 
    {
        return static::str($value)->camel()->snake(config('is.separator'))->toString();
    }

    /**
     * Разбивает строку на массив по регулярному выражению.
     *
     * @param   mixed $value
     * @param   string $delimiter
     * @return  array
     */
    public static function split($value, string $delimiter = '/[,|\s_.-]+/'): array 
    {
        return static::str($value)->split($delimiter)->toArray();
    }

    /**
     * Приводит значение к массиву.
     *
     * @param  mixed $value
     * @return array
     */
    public static function toArray($value): array
    {
        $value = value($value);

        if (is_string($value) || $value instanceof Stringable) {
            $value = static::split($value);
        }

        return Arr::flatten(Arr::wrap($value));
    }
}
