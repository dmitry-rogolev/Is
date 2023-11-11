<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Levelable extends BaseRoleable
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

    /**
     * Проверяем наличие хотябы одной роли
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasOneRole(...$role): bool;

    /**
     * Проверяем наличие всех ролей
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasAllRoles(...$role): bool;
}
