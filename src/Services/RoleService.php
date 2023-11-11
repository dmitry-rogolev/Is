<?php 

namespace dmitryrogolev\Is\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RoleService extends Service 
{
    /**
     * Возвращает все роли
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(): Collection 
    {
        return config('is.models.role')::all();
    }

    /**
     * Возвращает роль по ее идентификатору, slug'у или модели
     *
     * @param mixed $role
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show(mixed $id): Model|null 
    {
        return $this->getRole($id);
    }

    /**
     * Создает роль
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $attributes = []): Model 
    {
        return empty($attributes) ? config('is.models.role')::factory()->create() : config('is.models.role')::create($attributes);
    }

    /**
     * Обновляет модель
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($model, array $attributes): Model 
    {
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Удаляет модель
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function delete($model): bool|null
    {
        return $model->delete();
    }

    /**
     * Очищает таблицу ролей
     *
     * @return void
     */
    public function truncate(): void 
    {
        config('is.models.role')::truncate();
    }

    /**
     * Удаляет модель
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool|null
     */
    public function forceDelete($model): bool|null
    {
        return $model->forceDelete();
    }

    /**
     * Востанавливает модель
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function restore($model): bool 
    {
        return $model->restore();
    }

    /**
     * Получить роль по ее идентификатору или slug'у.
     *
     * @param mixed $role
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getRole($role): Model|null 
    {
        if (is_int($role) || is_string($role)) {
            return config('is.models.role')::where(app(config('is.models.role'))->getKeyName(), $role)->orWhere('slug', $role)->first();
        }

        return $role instanceof (config('is.models.role')) ? $role : null;
    }
}
