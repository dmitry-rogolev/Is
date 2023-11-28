<?php

namespace dmitryrogolev\Is;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * Помощник.
 */
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
