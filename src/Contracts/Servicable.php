<?php

namespace dmitryrogolev\Is\Contracts;

use ArrayAccess;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с таблицей.
 */
interface Servicable
{
    /**
     * Возвращает имя модели сервиса.
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Возвращает имя сидера модели.
     *
     * @return string
     */
    public function getSeeder(): string;

    /**
     * Возвращает имя фабрики модели.
     *
     * @return string
     */
    public function getFactory(): string;

    /**
     * Возвращает все модели.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(): Collection;

    /**
     * Возвращает все модели.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection;

    /**
     * Возвращает случайную модель из таблицы.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function random(): Model|null;

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show(int|string $id): Model|null;

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int|string $id): Model|null;

    /**
     * Проверяет наличие модели в таблице.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * Создать модель.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function make(array $attributes = []): Model;

    /**
     * Создать модель, только если она не существует в таблице.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function makeIfNotExists(array $attributes = []): Model|null;

    /**
     * Создать группу моделей.
     *
     * @param \ArrayAccess|array $group
     * @param boolean $ifNotExists
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function makeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих в таблице моделей.
     *
     * @param \ArrayAccess|array $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function makeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать модель и сохранить ее в таблицу.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function storeIfNotExists(array $attributes = []): Model|null;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function createIfNotExists(array $attributes = []): Model|null;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     *
     * @param \ArrayAccess|array $group
     * @param boolean $ifNotExists
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function storeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     *
     * @param \ArrayAccess|array $group
     * @param boolean $ifNotExists
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     *
     * @param \ArrayAccess|array $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function storeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     *
     * @param \ArrayAccess|array $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Возвращает фабрику модели.
     *
     * @param \Closure|array|integer|null $count
     * @param \Closure|array|null $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function factory($count = null, $state = []): Factory;

    /**
     * Генерирует модели с помощью фабрики.
     *
     * @param \Closure|array|integer|bool|null $attributes
     * @param \Closure|integer|bool|null $count
     * @param bool $create
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function generate($attributes = [], $count = null, bool $create = true): Model|Collection;

    /**
     * Обновляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Обновляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fill(Model $model, array $attributes): Model;

    /**
     * Удаляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function delete(Model $model): bool|null;

    /**
     * Очищает таблицу.
     *
     * @return void
     */
    public function truncate(): void;

    /**
     * Удаляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function forceDelete(Model $model): bool|null;

    /**
     * Восстанавливает модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function restore($model): bool;

    /**
     * Запускает сидер ролей.
     *
     * @return void
     */
    public function seed(): void;
}
