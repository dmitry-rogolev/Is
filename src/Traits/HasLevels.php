<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Model;

trait HasLevels 
{
    /**
     * Получить наибольший уровень ролей
     *
     * @return int
     */
    public function level(): int 
    {
        return $this->role()?->level ?? 0;
    }

    /**
     * Получить роль с наибольшим уровнем
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function role(): Model|null
    {
        return $this->roles->sortByDesc('level')->first();
    } 

    /**
     * Присоединить роли
     * 
     * Можно передавать идентификатор, slug или модель роли.
     * 
     * @param mixed ...$role
     * @return bool
     */
    public function attachRole(...$role): bool
    {
        $roles = Helper::arrayFrom($role);
        $attached = false;

        foreach ($roles as $role) {
            if (! $this->checkLevel($role) && $model = $this->getRole($role)) {
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
     * Проверяем наличие хотябы одной роли
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasOneRole(...$role): bool 
    {
        $roles = Helper::arrayFrom($role);

        foreach ($roles as $role) {
            if ($this->checkLevel($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяем наличие всех ролей
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasAllRoles(...$role): bool 
    {
        $roles = Helper::arrayFrom($role);

        foreach ($roles as $role) {
            if (! $this->checkLevel($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяем наличие уровня доступа роли
     *
     * @param mixed $role
     * @return boolean
     */
    protected function checkLevel(mixed $role): bool 
    {
        if (is_null($role = $this->getRole($role))) {
            return false;
        }
        
        return $this->level() >= $role->level;
    }
}
