<?php

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Функционал иерархии ролей.
 */
interface Levelable
{
    /**
     * Получить роль с наибольшим уровнем.
     */
    public function role(): ?Model;

    /**
     * Получить наибольший уровень ролей.
     */
    public function level(): int;
}
