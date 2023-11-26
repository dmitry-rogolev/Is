<?php 

namespace dmitryrogolev\Is\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Функционал иерархии ролей.
 */
trait HasLevels 
{
    /**
     * Получить роль с наибольшим уровнем.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function role(): Model|null
    {
        return $this->roles->sortByDesc('level')->first();
    } 

    /**
     * Получить наибольший уровень ролей.
     *
     * @return int
     */
    public function level(): int 
    {
        return $this->role()?->level ?? 0;
    }
}
