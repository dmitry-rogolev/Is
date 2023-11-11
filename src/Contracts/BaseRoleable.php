<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface BaseRoleable 
{
    /**
     * Модель относится к множеству ролей
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany;

    /**
     * Подгружает роли
     * 
     * @return static
     */
    public function loadRoles(): static;

    /**
     * Присоединить роли
     * 
     * Можно передавать идентификатор, slug или модель роли.
     * 
     * @param mixed ...$role
     * @return bool
     */
    public function attachRole(...$role): bool;

    /**
     * Отсоединить роли
     * 
     * Можно передавать идентификатор, slug или модель роли.
     * Если ничего не передовать, то будут отсоединены все отношения
     * 
     * @param mixed ...$role
     * @return bool
     */
    public function detachRole(...$role): bool;

    /**
     * Отсоединить все роли
     *
     * @return boolean
     */
    public function detachAllRoles(): bool;

    /**
     * Синхронизирует роли
     *
     * @param mixed ...$roles
     * @return void
     */
    public function syncRoles(...$roles): void;

    /**
     * Проверяем наличие хотябы одной роли
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasOneRole(...$role): bool;

    /**
     * Проверяем наличие всех ролей
     *
     * @param array ...$role
     * @return boolean
     */
    public function hasAllRoles(...$role): bool;

    /**
     * Проверяем наличие хотябы одной роли. 
     * 
     * Если передать второй параметр, проверяет наличие всех ролей
     *
     * @param mixed $role
     * @param boolean $all
     * @return boolean
     */
    public function hasRole(mixed $role, bool $all = false): bool;
}