<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Функционал ролей.
 */
interface AbstractRoleable 
{
    /**
     * Модель относится к множеству ролей.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany;

    /**
     * Возвращает роли.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles(): Collection;

    /**
     * Подгружает отношение модели с ролями.
     * 
     * @return static
     */
    public function loadRoles(): static;

    /**
     * Присоединяет роль.
     * 
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function attachRole(...$role): bool;

    /**
     * Отсоединить роль.
     * 
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function detachRole(...$role): bool;

    /**
     * Отсоединить все роли.
     *
     * @return bool
     */
    public function detachAllRoles(): bool;

    /**
     * Синхронизировать роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$roles
     * @return void
     */
    public function syncRoles(...$roles): void;

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function hasOneRole(...$role): bool;

    /**
     * Проверяет наличие всех переданных ролей.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection ...$role
     * @return bool
     */
    public function hasAllRoles(...$role): bool;

    /**
     * Проверяет наличие роли.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection $role
     * @param bool $all Проверить наличие всех ролей?
     * @return bool
     */
    public function hasRole($role, bool $all = false): bool;
}
