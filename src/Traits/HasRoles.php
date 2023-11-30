<?php

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Slug\Facades\Slug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

/**
 * Функционал ролей.
 */
trait HasRoles
{
    use HasLevels, ExtendIsMethod;

    /**
     * Модель относится к множеству ролей.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        $query = $this->morphToMany(Is::roleModel(), Is::relationName())->using(Is::roleableModel());

        return Is::usesTimestamps() ? $query->withTimestamps() : $query;
    }

    /**
     * Возвращает роли.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles(): Collection
    {
        return Is::all($this);
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
        $roles    = Arr::flatten($role);
        $attached = false;

        foreach ($roles as $v) {
            if (($model = Is::find($v)) && ! Is::has($model, $this)) {
                $this->roles()->attach($model);
                $attached = true;
            }
        }

        if (Is::usesLoadOnUpdate() && $attached) {
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
        $roles    = Arr::flatten($role);
        $detached = false;

        if (empty($roles)) {
            return $this->detachAllRoles();
        }

        foreach ($roles as $v) {
            if (($model = Is::find($v)) && Is::has($model, $this)) {
                $this->roles()->detach($model);
                $detached = true;
            }
        }

        if (Is::usesLoadOnUpdate() && $detached) {
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

        if (Is::usesLoadOnUpdate() && $detached) {
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
        $roles = Arr::flatten($role);

        foreach ($roles as $v) {
            if (Is::has($v, $this)) {
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
        $roles = Arr::flatten($role);

        foreach ($roles as $v) {
            if (! Is::has($v, $this)) {
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
            $slug = str($method)->after('is')->snake(Slug::separator())->toString();
            return $this->hasRole(Slug::from($slug));
        }

        return null;
    }
}
