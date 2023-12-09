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
     */
    public function getModel(): string;

    /**
     * Возвращает имя сидера модели.
     */
    public function getSeeder(): string;

    /**
     * Возвращает имя фабрики модели.
     */
    public function getFactory(): string;

    /**
     * Возвращает все модели.
     */
    public function index(): Collection;

    /**
     * Возвращает все модели.
     */
    public function all(): Collection;

    /**
     * Возвращает случайную модель из таблицы.
     */
    public function random(): ?Model;

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $key
     */
    public function show(int|string $id): ?Model;

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $key
     */
    public function find(int|string $id): ?Model;

    /**
     * Проверяет наличие модели в таблице.
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $key
     */
    public function has($key): bool;

    /**
     * Создать модель.
     */
    public function make(array $attributes = []): Model;

    /**
     * Создать модель, только если она не существует в таблице.
     */
    public function makeIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать группу моделей.
     */
    public function makeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих в таблице моделей.
     */
    public function makeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function store(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function create(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function storeIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function createIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function storeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function createGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function storeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function createGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Возвращает фабрику модели.
     *
     * @param  \Closure|array|int|null  $count
     * @param  \Closure|array|null  $state
     */
    public function factory($count = null, $state = []): Factory;

    /**
     * Генерирует модели с помощью фабрики.
     *
     * @param  \Closure|array|int|bool|null  $attributes
     * @param  \Closure|int|bool|null  $count
     */
    public function generate($attributes = [], $count = null, bool $create = true): Model|Collection;

    /**
     * Обновляет модель.
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Обновляет модель.
     */
    public function fill(Model $model, array $attributes): Model;

    /**
     * Удаляет модель.
     */
    public function delete(Model $model): ?bool;

    /**
     * Очищает таблицу.
     */
    public function truncate(): void;

    /**
     * Удаляет модель.
     */
    public function forceDelete(Model $model): ?bool;

    /**
     * Восстанавливает модель.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function restore($model): bool;

    /**
     * Запускает сидер ролей.
     */
    public function seed(): void;
}
