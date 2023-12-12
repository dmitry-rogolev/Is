<?php

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Slug\Facades\Slug;
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
        $roles = $this->getModelsForAttach($role);

        if (empty($roles)) {
            return false;
        }

        // Присоединяем роли.
        array_walk($roles, fn ($role) => $this->roles()->attach($role));

        // Обновляем роли, если данная опция включена.
        if (config('is.uses.load_on_update')) {
            $this->loadRoles();
        }

        return true;
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

    /**
     * Получить роль с наибольшим уровнем.
     */
    public function role(): ?Model
    {
        return $this->roles->sortByDesc('level')->first();
    }

    /**
     * Получить наибольший уровень ролей.
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
     * @param  int|string|\Illuminate\Database\Eloquent\Model|array|\Illuminate\Contracts\Support\Arrayable  $model
     */
    public function is($model, bool $all = false): bool
    {
        if (
            config('is.uses.extend_is_method') && (
                is_int($model)
                || is_string($model)
                || is_a($model, config('is.models.role'))
                || is_array($model)
                || $model instanceof Arrayable
            )
        ) {
            return $this->hasRole($model, $all);
        }

        return parent::is($model);
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
     * Заменяет идентификаторы и slug'и на модели.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function replaceIdsWithModels(array $roles): array
    {
        // Сортируем переданный массив. Складываем модели в один массив,
        // а идентификаторы и slug'и в другой массив.
        [$result, $ids] = $this->sortModelsAndIds($roles);

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
     * @return array<int, array<int, mixed>>
     */
    protected function sortModelsAndIds(array $roles): array
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
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function parseRoles(mixed $roles): array
    {
        return $this->replaceIdsWithModels(
            $this->toFlattenArray($roles)
        );
    }

    /**
     * Возвращает только те роли, которых нет у модели.
     */
    protected function notAttachedFilter(array $roles): array
    {
        return array_values(array_filter($roles, fn ($role) => ! $this->checkRole($role)));
    }

    /**
     * Возвращает роль с максимальным уровнем доступа из переданных моделей.
     */
    protected function getModelWithMaxLevel(array $roles): ?Model
    {
        return collect($roles)->sortByDesc('level')->first();
    }

    /**
     * Если включена иерархия ролей, возвращает массив с одной ролью, имеющей максимальный уровень доступа, иначе возвращает роли без изменения.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function useLevelsFilter(array $roles): array
    {
        return config('is.uses.levels') && ! empty($roles) ? [$this->getModelWithMaxLevel($roles)] : $roles;
    }

    /**
     * Возвращает модели ролей, которые могут быть присоединены к модели.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    protected function getModelsForAttach(array $roles): array
    {
        return $this->useLevelsFilter(
            $this->notAttachedFilter(
                $this->parseRoles($roles)
            )
        );
    }
}
