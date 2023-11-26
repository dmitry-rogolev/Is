<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Функционал иерархии ролей.
 */
trait HasLevels 
{
    use AbstractHasRoles;

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

    /**
     * Возвращает все нижестоящие по уровню роли относительно той, 
     * которая привязанна к данной модели и имеет наибольший уровень.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles(): Collection
    {
        return config('is.models.role')::where('level', '<=', $this->level())->get();
    }

    /**
     * Присоединяет роль.
     * 
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function attachRole(...$role): bool
    {
        $attached = false;

        foreach (Helper::toArray($role) as $value) {
            if (! $this->checkLevel($value) && $model = $this->getRole($value)) {
                $this->roles()->attach($model);
                $attached = true;
            }
        }

        if (config('is.uses.load_on_update') && $attached) {
            $this->loadRoles();
        }

        return $attached;
    }

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function hasOneRole(...$role): bool 
    {
        foreach (Helper::toArray($role) as $value) {
            if ($this->checkLevel($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет наличие всех переданных ролей.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function hasAllRoles(...$role): bool 
    {
        foreach (Helper::toArray($role) as $value) {
            if (! $this->checkLevel($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяем наличие уровня доступа роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $role
     * @return bool
     */
    protected function checkLevel($role): bool 
    {
        if (! config('is.uses.levels')) {
            return $this->checkRole($role);
        }
        
        if (is_null($role = $this->getRole($role))) {
            return false;
        }
        
        return $this->level() >= $role->level;
    }
}
