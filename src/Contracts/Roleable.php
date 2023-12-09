<?php

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Функционал ролей.
 */
interface Roleable extends Levelable
{
    /**
     * Модель относится к множеству ролей.
     */
    public function roles(): MorphToMany;

    /**
     * Возвращает роли.
     */
    public function getRoles(): Collection;

    /**
     * Подгружает отношение модели с ролями.
     */
    public function loadRoles(): static;

    /**
     * Присоединяет роль.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  ...$role
     */
    public function attachRole(...$role): bool;

    /**
     * Отсоединить роль.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  ...$role
     */
    public function detachRole(...$role): bool;

    /**
     * Отсоединить все роли.
     */
    public function detachAllRoles(): bool;

    /**
     * Синхронизировать роли.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  ...$roles
     */
    public function syncRoles(...$roles): void;

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  ...$role
     */
    public function hasOneRole(...$role): bool;

    /**
     * Проверяет наличие всех переданных ролей.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  ...$role
     */
    public function hasAllRoles(...$role): bool;

    /**
     * Проверяет наличие роли.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Support\Collection  $role
     * @param  bool  $all Проверить наличие всех ролей?
     */
    public function hasRole($role, bool $all = false): bool;
}
