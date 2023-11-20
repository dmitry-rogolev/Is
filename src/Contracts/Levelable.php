<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Levelable
{
    /**
     * Получить наибольший уровень ролей
     *
     * @return int
     */
    public function level(): int;

    /**
     * Получить роль с наибольшим уровнем
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function role(): Model|null;
}
