<?php 

namespace dmitryrogolev\Is\Contracts;

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
     * Изменяет имя модели сервиса.
     *
     * @param string $model
     * @return static
     */
    public function setModel(string $model): static;

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
     * @return Model
     */
    public function make(array $attributes = []): Model;

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
     * Возвращает фабрику модели.
     *
     * @param \Closure|array|integer|null $count
     * @param \Closure|array|null $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function factory($count = null, $state = []): Factory;

    /**
     * Геренирует модели с помощью фабрики.
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
     * Востанавливает модель.
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
