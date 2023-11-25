<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

/**
 * Функционал ролей.
 */
trait AbstractHasRoles 
{
    /**
     * Модель относится к множеству ролей.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        $query = $this->morphToMany(config('is.models.role'), config('is.relations.roleable'))->using(config('is.models.roleable'));

        return config('is.uses.timestamps') ? $query->withTimestamps() : $query;
    }

    /**
     * Возвращает роли.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles(): Collection 
    {
        return $this->roles;
    }

    /**
     * Подгружает отношение модели с ролями.
     * 
     * @return static
     */
    public function loadRoles(): static 
    {
        return $this->load('roles');
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
            if (! $this->checkRole($value) && $model = $this->getRole($value)) {
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
     * Отсоединить роль.
     * 
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function detachRole(...$role): bool
    {
        $roles = Helper::toArray($role);
        $detached = false;

        if (empty($roles)) {
            return $this->detachAllRoles();
        }

        foreach ($roles as $value) {
            if ($this->checkRole($value) && $model = $this->getRole($value)) {
                $this->roles()->detach($model);
                $detached = true;
            }
        }

        if (config('is.uses.load_on_update') && $detached) {
            $this->loadRoles();
        }

        return $detached;
    }

    /**
     * Отсоединить все роли.
     *
     * @return bool
     */
    public function detachAllRoles(): bool 
    {
        $detached = false;

        if ($this->roles->isNotEmpty()) {
            $this->roles()->detach();
            $detached = true;
        }

        if (config('is.uses.load_on_update') && $detached) {
            $this->loadRoles();
        }

        return $detached;
    }

    /**
     * Синхронизировать роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$roles
     * @return void
     */
    public function syncRoles(...$roles): void 
    {
        $this->detachAllRoles();
        $this->attachRole($roles);
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
            if ($this->checkRole($value)) {
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
            if (! $this->checkRole($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет наличие роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection $role
     * @param bool $all Проверить наличие всех ролей?
     * @return bool
     */
    public function hasRole($role, bool $all = false): bool 
    {
        return $all ? $this->hasAllRoles($role) : $this->hasOneRole($role);
    }

    public function __call($method, $parameters) 
    {
        try {
            return parent::__call($method, $parameters); 
        } catch (\BadMethodCallException $e) {
            if (is_bool($is = $this->callMagicIsRole($method))) {
                return $is;
            }

            throw $e;
        }
    }

    /**
     * Магический метод. Проверяет наличие роли по его slug'у.
     * 
     * Пример вызова: isAdmin(), isUser().
     *
     * @param string $method
     * @return bool|null
     */
    protected function callMagicIsRole($method): bool|null
    {
        if (str_starts_with($method, 'is')) {
            return $this->hasRole(Helper::slug(Str::after($method, 'is')));
        }

        return null;
    }

    /**
     * Проверяет наличие роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $role
     * @return bool
     */
    protected function checkRole($role): bool 
    {
        return $this->roles->contains(fn ($item) => 
            $item->getKey() == $role 
            || $item->slug == $role 
            || ($role instanceof (config('is.models.role')) && $item->is($role))
        );
    } 

    /**
     * Получить роль по ее идентификатору или slug'у.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $role
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getRole($role): Model|null
    {
        if ($role instanceof (config('is.models.role'))) {
            return $role;
        }

        return config('is.models.role')
                ::where(app(config('is.models.role'))->getKeyName(), $role)
                ->orWhere('slug', $role)
                ->first();
    }
}
