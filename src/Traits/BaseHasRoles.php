<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait BaseHasRoles 
{
    /**
     * Модель относится к множеству ролей
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        $query = $this->morphToMany(config('is.models.role'), config('is.relations.roleable'))->using(config('is.models.roleable'));

        return config('is.uses.timestamps') ? $query->withTimestamps() : $query;
    }

    /**
     * Возвращает все роли модели. 
     * 
     * При включенной иерархии ролей, возвращает все нижестоящие и равные по уровню в иерархии роли.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allRoles(): Collection 
    {
        if (config('is.uses.levels')) {
            return config('is.models.role')::where('level', '<=', $this->level())->get();
        } 

        return $this->roles;
    }

    /**
     * Подгружает роли
     * 
     * @return static
     */
    public function loadRoles(): static 
    {
        return $this->load('roles');
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
        $roles = Helper::toArray($role);
        $attached = false;

        foreach ($roles as $role) {
            if (! $this->checkRole($role) && $model = $this->getRole($role)) {
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
     * Отсоединить роли
     * 
     * Можно передавать идентификатор, slug или модель роли.
     * Если ничего не передовать, то будут отсоединены все отношения
     * 
     * @param mixed ...$role
     * @return bool
     */
    public function detachRole(...$role): bool
    {
        $roles = Helper::toArray($role);
        $detached = false;

        if (empty($roles)) {
            return $this->detachAllRoles();
        }

        foreach ($roles as $role) {
            if ($this->checkRole($role) && $model = $this->getRole($role)) {
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
     * Отсоединить все роли
     *
     * @return boolean
     */
    public function detachAllRoles(): bool 
    {
        if ($this->roles->isNotEmpty()) {
            $this->roles()->detach();

            if (config('is.uses.load_on_update')) {
                $this->loadRoles();
            }

            return true;
        }

        return false;
    }

    /**
     * Синхронизирует роли
     *
     * @param mixed ...$roles
     * @return void
     */
    public function syncRoles(...$roles): void 
    {
        $this->detachAllRoles();
        $this->attachRole($roles);
    }

    /**
     * Проверяем наличие хотябы одной роли
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasOneRole(...$role): bool 
    {
        $roles = Helper::toArray($role);

        foreach ($roles as $role) {
            if ($this->checkRole($role)) {
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
        $roles = Helper::toArray($role);

        foreach ($roles as $role) {
            if (! $this->checkRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяем наличие хотябы одной роли. 
     * 
     * Если передать второй параметр, проверяет наличие всех ролей
     *
     * @param mixed $role
     * @param boolean $all
     * @return boolean
     */
    public function hasRole(mixed $role, bool $all = false): bool 
    {
        return $all ? $this->hasAllRoles($role) : $this->hasOneRole($role);
    }

    /**
     * Проверяем наличие роли
     * 
     * Например, isAdmin(), isUser() 
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
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
     * Проверяем наличие роли
     * 
     * Например, isAdmin(), isUser() 
     *
     * @param string $method
     * @param array $parameters
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
     * Проверяем наличие роли
     *
     * @param mixed $role
     * @return boolean
     */
    protected function checkRole(mixed $role): bool 
    {
        return $this->roles->contains(fn ($item) => $item->getKey() == $role || $item->slug == $role || $role instanceof (config('is.models.role')) && $item->is($role));
    } 

    /**
     * Получить роль по ее идентификатору или slug'у.
     *
     * @param mixed $role
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getRole(mixed $role): Model|null
    {
        if (is_int($role) || is_string($role)) {
            return config('is.models.role')::where(app(config('is.models.role'))->getKeyName(), $role)->orWhere('slug', $role)->first();
        }

        return $role instanceof (config('is.models.role')) ? $role : null;
    }
}
