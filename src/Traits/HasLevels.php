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
     */
    public function role(): ?Model
    {
        return $this->roles->sortByDesc('level')->first();
    }

    /**
     * Получить наибольший уровень ролей.
     */
    public function level(): int
    {
        return $this->role()?->level ?? 0;
    }
}
