<?php

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Функционал ролей.
 */
interface Roleable
{
    /**
     * Модель относится к множеству ролей.
     */
    public function roles(): MorphToMany;

    /**
     * Возвращает коллекцию ролей.
     */
    public function getRoles(): Collection;

    /**
     * Подгружает отношение модели с ролями.
     */
    public function loadRoles(): static;

    /**
     * Присоединяет роль(-и) к модели.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @return bool Была ли присоединена хотябы одна роль?
     */
    public function attachRole(mixed ...$role): bool;

    /**
     * Отсоединяет роль(-и).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @return bool Была ли отсоединена хотябы одна роль?
     */
    public function detachRole(mixed ...$role): bool;

    /**
     * Отсоединяет все роли.
     *
     * @return bool Были ли отсоединены роли?
     */
    public function detachAllRoles(): bool;

    /**
     * Синхронизирует роли.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function syncRoles(mixed ...$roles): void;

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function hasOneRole(mixed ...$role): bool;

    /**
     * Проверяет наличие всех переданных ролей.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function hasAllRoles(mixed ...$role): bool;

    /**
     * Проверяет наличие роли(-ей).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  $role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @param  bool  $all Проверить наличие всех ролей?
     */
    public function hasRole(mixed $role, bool $all = false): bool;

    /**
     * Получить роль с наибольшим уровнем доступа.
     */
    public function role(): ?Model;

    /**
     * Получить наибольший уровень доступа из присоединенных ролей.
     */
    public function level(): int;
}
