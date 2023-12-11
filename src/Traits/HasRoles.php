<?php

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Slug\Facades\Slug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

/**
 * Функционал ролей.
 */
trait HasRoles
{
    use ExtendIsMethod, HasLevels;

    /**
     * Модель относится к множеству ролей.
     */
    public function roles(): MorphToMany
    {
        $query = $this->morphToMany(config('is.models.role'), config('is.relations.roleable'))->using(config('is.models.roleable'));

        return config('is.uses.timestamps') ? $query->withTimestamps() : $query;
    }

    /**
     * Возвращает коллекцию ролей.
     */
    public function getRoles(): Collection
    {
        return config('is.uses.levels') ? Is::where('level', '<=', $this->level()) : $this->roles;
    }

    /**
     * Подгружает отношение модели с ролями.
     */
    public function loadRoles(): static
    {
        return $this->load('roles');
    }

    /**
     * Присоединяет роль(-и) к модели.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role
     */
    public function attachRole(mixed ...$role): bool
    {
        $roles = Arr::flatten($role);
        $attached = false;

        foreach ($roles as $role) {
            // Т.к. пользователь может передать slug, то требуется получить модель из таблицы.
            $model = $this->getRole($role);

            // Присоединяем те роли, которых нет у модели.
            if (! is_null($model) && ! $this->checkRole($model)) {
                $this->roles()->attach($model);
                $attached = true;
            }
        }

        // Подгружаем отношение, если были изменения и включена данная опция.
        if (config('is.uses.load_on_update') && $attached) {
            $this->loadRoles();
        }

        return $attached;
    }

    /**
     * Отсоединить роль.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  ...$role
     */
    public function detachRole(...$role): bool
    {
        $roles = Arr::flatten($role);
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
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  ...$roles
     */
    public function syncRoles(...$roles): void
    {
        $this->detachAllRoles();
        $this->attachRole($roles);
    }

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  ...$role
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
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  ...$role
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
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  $role
     * @param  bool  $all Проверить наличие всех ролей?
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
     * @param  string  $method
     */
    protected function callMagicIsRole($method): ?bool
    {
        if (str_starts_with($method, 'is')) {
            $slug = str($method)->after('is')->snake(Slug::separator())->toString();

            return $this->hasRole(Slug::from($slug));
        }

        return null;
    }

    /**
     * Проверяет наличие роли у модели.
     */
    protected function checkRole(Model $role): bool
    {
        if (config('is.uses.levels')) {
            return $this->checkLevel($role);
        }

        return $this->roles->contains(
            fn ($item) => $item->is($role)
        );
    }

    /**
     * Проверяет уровень доступа модели.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $role
     */
    protected function checkLevel(Model $role): bool
    {
        if (! config('is.uses.levels')) {
            return $this->checkRole($role);
        }

        return $this->level() >= $role->level;
    }

    /**
     * Возвращает роль по ее идентификатору или slug'у.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $role
     */
    protected function getRole(mixed $role): ?Model
    {
        return ! is_a($role, config('is.models.role')) ? Is::firstWhereUniqueKey($role) : $role;
    }
}
