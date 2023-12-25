<?php

namespace dmitryrogolev\Is\Traits;

use BadMethodCallException;
use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Функционал ролей.
 */
trait HasRoles
{
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
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @return bool Была ли присоединена хотябы одна роль?
     */
    public function attachRole(mixed ...$role): bool
    {
        // Получаем модели ролей из переданных параметров.
        // При этом переданные идентификаторы и slug'и будут заменены на модели.
        //
        // Затем фильтруем роли, оставляя те, которые еще не были присоединены к модели.
        //
        // Если используется иерархия ролей, из всех переданных ролей,
        // будет присоединена одна с наибольшим уровнем доступа.
        //
        // Наконец, заменяем модели на их идентификаторы,
        // так как метод attach ожидает массив идентификаторов.
        $roles = $this->getRolesForAttach($role);

        if (empty($roles)) {
            return false;
        }

        // Присоединяем роли.
        $this->roles()->attach($roles);

        // Обновляем роли, если данная опция включена.
        if (config('is.uses.load_on_update')) {
            $this->loadRoles();
        }

        return true;
    }

    /**
     * Отсоединяет роль(-и).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @return bool Была ли отсоединена хотябы одна роль?
     */
    public function detachRole(mixed ...$role): bool
    {
        $roles = $this->toFlattenArray($role);

        // Если ничего не передано, отсоединяем все роли.
        if (empty($roles)) {
            return $this->detachAllRoles();
        }

        // Получаем модели ролей из переданных параметров.
        // При этом переданные идентификаторы и slug'и будут заменены на модели.
        //
        // Затем фильтруем роли, оставляя те, которые фактически присоединены к модели.
        //
        // Наконец, заменяем модели на их идентификаторы,
        // так как метод detach ожидает массив идентификаторов.
        $roles = $this->getRolesForDetach($roles);

        if (empty($roles)) {
            return false;
        }

        // Отсоединяем роли.
        $this->roles()->detach($roles);

        // Обновляем роли, если данная опция включена.
        if (config('is.uses.load_on_update')) {
            $this->loadRoles();
        }

        return true;
    }

    /**
     * Отсоединяет все роли.
     *
     * @return bool Были ли отсоединены роли?
     */
    public function detachAllRoles(): bool
    {
        if ($this->roles->isEmpty()) {
            return false;
        }

        // Отсоединяем все роли.
        $this->roles()->detach();

        // Обновляем роли, если данная опция включена.
        if (config('is.uses.load_on_update')) {
            $this->loadRoles();
        }

        return true;
    }

    /**
     * Синхронизирует роли.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function syncRoles(mixed ...$roles): void
    {
        $this->detachAllRoles();
        $this->attachRole($roles);
    }

    /**
     * Проверяет наличие хотябы одной роли из переданных.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function hasOneRole(mixed ...$role): bool
    {
        // Получаем модели ролей из переданных параметров.
        // При этом переданные идентификаторы и slug'и будут заменены на модели.
        $roles = $this->parseRoles($role);

        // Возвращаем true, если хотябы одна роль присоединена.
        foreach ($roles as $role) {
            if ($this->checkRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет наличие всех переданных ролей.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  ...$role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     */
    public function hasAllRoles(mixed ...$role): bool
    {
        // Получаем модели ролей из переданных параметров.
        // При этом переданные идентификаторы и slug'и будут заменены на модели.
        $roles = $this->parseRoles($role);

        if (empty($roles)) {
            return false;
        }

        // Возвращаем false, если хотябы одна роль не присоединена.
        foreach ($roles as $role) {
            if (! $this->checkRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет наличие роли(-ей).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  $role Идентификатор(-ы), slug(-и) или модель(-и) роли(-ей).
     * @param  bool  $all Проверить наличие всех ролей?
     */
    public function hasRole(mixed $role, bool $all = false): bool
    {
        return $all ? $this->hasAllRoles($role) : $this->hasOneRole($role);
    }

    /**
     * Возвращает роль с наибольшим уровнем доступа.
     */
    public function role(): ?Model
    {
        return $this->roles->sortByDesc('level')->first();
    }

    /**
     * Возвращает наибольший уровень доступа присоединенных ролей.
     */
    public function level(): int
    {
        return $this->role()?->level ?? 0;
    }

    /**
     * Определите, имеют ли две модели одинаковый идентификатор и принадлежат ли они к одной таблице.
     *
     * Если передать роль, то будет вызван метод hasRole, проверяющий наличие роли у модели.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Database\Eloquent\Model|string|int  $model
     */
    public function is($model, bool $all = false): bool
    {
        if (
            ! config('is.uses.extend_is_method') ||
            $model instanceof Model &&
            ! is_a($model, config('is.models.role'))
        ) {
            return parent::is($model);
        }

        return $this->hasRole($model, $all);
    }

    public function __call($method, $parameters)
    {
        try {
            return parent::__call($method, $parameters);
        } catch (BadMethodCallException $e) {
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
     */
    protected function callMagicIsRole(string $method): ?bool
    {
        if (str_starts_with($method, 'is')) {
            $slug = str($method)->after('is')->snake(config('is.separator'))->toString();

            return $this->hasOneRole($slug);
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

        return $this->roles->contains(fn ($item) => $item->is($role));
    }

    /**
     * Проверяет уровень доступа модели.
     */
    protected function checkLevel(Model $role): bool
    {
        if (! config('is.uses.levels')) {
            return $this->checkRole($role);
        }

        return $this->level() >= $role->level;
    }

    /**
     * Проверяет, является ли переданное значение идентификатором.
     */
    protected function isId(mixed $value): bool
    {
        return is_int($value) || is_string($value) && (Str::isUuid($value) || Str::isUlid($value));
    }

    /**
     * Приводит переданное значение в выравненному массиву.
     *
     * @return array<int, mixed>
     */
    protected function toFlattenArray(mixed $value): array
    {
        return Arr::flatten([$value]);
    }

    /**
     * Заменяет модели на их идентификаторы.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return array<int, mixed>
     */
    protected function modelsToIds(array $models): array
    {
        return collect($models)->pluck($this->getKeyName())->all();
    }

    /**
     * Заменяет идентификаторы и slug'и на модели.
     *
     * @param  array<int, mixed>  $roles
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function replaceIdsWithRoles(array $roles): array
    {
        // Сортируем переданный массив. Складываем модели в один массив,
        // а идентификаторы и slug'и в другой массив.
        [$result, $ids] = $this->sortRolesAndIds($roles);

        // Если были переданы идентификаторы и(или) slug'и, получаем по ним модели,
        // а затем добавляем их в результирующий массив.
        if (! empty($ids)) {
            $models = Is::whereUniqueKey($ids);
            $result = array_merge($result, $models->all());
        }

        return $result;
    }

    /**
     * Сортируем переданный массив на модели ролей и на идентификаторы и slug'и.
     *
     * @param  array<int, mixed>  $roles
     * @return array<int, array<int, mixed>>
     */
    protected function sortRolesAndIds(array $roles): array
    {
        $ids = [];
        $models = [];

        foreach ($roles as $role) {
            if (is_a($role, config('is.models.role')) && $role->exists) {
                $models[] = $role;
            } elseif ($this->isId($role) || is_string($role)) {
                $ids[] = $role;
            }
        }

        return [$models, $ids];
    }

    /**
     * Возвращает модели ролей из переданных значений.
     *
     * @param  array<int, mixed>  $roles
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function parseRoles(mixed $roles): array
    {
        return $this->replaceIdsWithRoles(
            $this->toFlattenArray($roles)
        );
    }

    /**
     * Возвращает только те роли, которых нет у модели.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $roles
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function notAttachedRolesFilter(array $roles): array
    {
        return array_values(array_filter($roles, fn ($role) => ! $this->checkRole($role)));
    }

    /**
     * Возвращает только те роли, которые присоединены к модели.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $roles
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function attachedRolesFilter(array $roles): array
    {
        return array_values(array_filter(
            $roles,
            fn ($role) => $this->roles->contains(fn ($item) => $item->is($role))
        ));
    }

    /**
     * Возвращает роль с максимальным уровнем доступа из переданных моделей.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $roles
     */
    protected function getRoleWithMaxLevel(array $roles): ?Model
    {
        return collect($roles)->sortByDesc('level')->first();
    }

    /**
     * Если включена иерархия ролей, возвращает массив с одной ролью, имеющей максимальный уровень доступа, иначе возвращает роли без изменения.
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $roles
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function useLevelsFilter(array $roles): array
    {
        return config('is.uses.levels') && ! empty($roles) ? [$this->getRoleWithMaxLevel($roles)] : $roles;
    }

    /**
     * Возвращает модели ролей, которые могут быть присоединены к модели.
     *
     * @param  array<int, mixed>  $roles
     * @return array<int, mixed>
     */
    protected function getRolesForAttach(array $roles): array
    {
        return $this->modelsToIds(
            $this->useLevelsFilter(
                $this->notAttachedRolesFilter(
                    $this->parseRoles($roles)
                )
            )
        );
    }

    /**
     * Возвращает модели ролей, которые могут быть отсоединены от модели.
     *
     * @param  array<int, mixed>  $roles
     * @return array<int, mixed>
     */
    protected function getRolesForDetach(array $roles): array
    {
        return $this->modelsToIds(
            $this->attachedRolesFilter(
                $this->parseRoles($roles)
            )
        );
    }
}
