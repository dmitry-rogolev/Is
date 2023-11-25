<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Функционал иерархии ролей.
 */
interface Levelable extends AbstractRoleable
{
    /**
     * Получить роль с наибольшим уровнем.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function role(): Model|null;

    /**
     * Получить наибольший уровень ролей.
     *
     * @return int
     */
    public function level(): int;
}
