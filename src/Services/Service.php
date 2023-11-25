<?php 

namespace dmitryrogolev\Is\Services;

use dmitryrogolev\Is\Contracts\Servicable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с таблицей.
 */
abstract class Service implements Servicable
{
    /**
     * Имя модели таблицы.
     *
     * @var string
     */
    protected string $model;

    /**
     * Имя сидера модели.
     *
     * @var string
     */
    protected string $seeder;

    /**
     * Возвращает имя модели сервиса.
     *
     * @return string
     */
    public function getModel(): string 
    {
        return $this->model;
    }

    /**
     * Изменяет имя модели сервиса.
     *
     * @param string $model
     * @return static
     */
    public function setModel(string $model): static 
    {
        if (class_exists($model)) {
            $this->model = $model;
        }

        return $this;
    }

    /**
     * Возвращает имя сидера модели.
     *
     * @return string
     */
    public function getSeeder(): string 
    {
        return $this->seeder;
    }

    /**
     * Изменяет имя сидера модели.
     *
     * @param string $seeder
     * @return static
     */
    public function setSeeder(string $seeder): static 
    {
        if (class_exists($seeder)) {
            $this->seeder = $seeder;
        }

        return $this;
    }

    /**
     * Возвращает все модели.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(): Collection
    {
        return $this->model::all();
    }

    /**
     * Возвращает все модели.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection
    {
        return $this->index();
    }

    /**
     * Возвращает случайную модель из таблицы.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function random(): Model|null 
    {
        return $this->model::query()->inRandomOrder()->first();
    }

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($key): Model|null 
    {
        if ($key instanceof ($this->model) && $key->exists) {
            return $key;
        }

        if (is_int($key) || is_string($key)) {
            return $this->model::find($key);
        }

        return null;
    }

    /**
     * Возвращает модель по ее идентификатору.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($key): Model|null 
    {
        return $this->show($key);
    }

    /**
     * Проверяет наличие модели в таблице.
     *
     * @param int|string|\Illuminate\Database\Eloquent\Model $key
     * @return bool
     */
    public function has($key): bool 
    {
        return (bool) $this->show($key);
    }

    /**
     * Создать модель.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function make(array $attributes = []): Model 
    {
        return $this->model::make($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $attributes = []): Model 
    {
        return $this->model::create($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes = []): Model 
    {
        return $this->store($attributes);
    }

    /**
     * Возвращает фабрику модели.
     *
     * @param \Closure|array|integer|null $count
     * @param \Closure|array|null $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function factory($count = null, $state = []): Factory
    {
        return $this->model::factory($count, $state);
    }

    /**
     * Геренирует модели с помощью фабрики.
     *
     * @param \Closure|array|integer|bool|null $attributes
     * @param \Closure|integer|bool|null $count
     * @param bool $create
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function generate($attributes = [], $count = null, bool $create = true): Model|Collection
    {
        $attributes = value($attributes);
        $count = value($count);

        if (is_int($attributes)) {
            $count = $attributes;
            $attributes = [];
        }

        if (is_bool($attributes)) {
            $create = $attributes;
            $attributes = [];
        }

        if (is_bool($count)) {
            $create = $count;
            $count = null;
        }

        $factory = $this->factory($count);
        
        return $create ? $factory->create($attributes) : $factory->make($attributes);
    }

    /**
     * Обновляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(Model $model, array $attributes): Model 
    {
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Обновляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fill(Model $model, array $attributes): Model 
    {
        return $this->update($model, $attributes);
    }

    /**
     * Удаляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function delete(Model $model): bool|null
    {
        return $model->delete();
    }

    /**
     * Очищает таблицу.
     *
     * @return void
     */
    public function truncate(): void
    {
        $this->model::truncate();
    }

    /**
     * Удаляет модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function forceDelete(Model $model): bool|null
    {
        return $model->forceDelete();
    }

    /**
     * Востанавливает модель.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function restore($model): bool
    {
        return $model->restore();
    }

    /**
     * Запускает сидер модели.
     *
     * @return void
     */
    public function seed(): void 
    {
        app($this->seeder)->run();
    }
}
