<?php 

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Traits\RoleHasRelations;
use dmitryrogolev\Is\Traits\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Модель роли.
 */
class BaseRole extends Database implements ContractRoleHasRelations 
{
    use HasFactory, Sluggable, RoleHasRelations;

    /**
     * Атрибуты, для которых разрешено массовое присвоение значений.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 
        'slug', 
        'description', 
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('is.tables.roles');

        if (config('is.uses.levels')) {
            array_push($this->fillable, 'level');
        }
    }

    /**
     * Магический метод, возвращающий роль по ее slug.
     * 
     * Например, Role::admin(), Role::user(), Role::moderator().
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        try {
            return parent::__callStatic($method, $parameters);
        } catch (\BadMethodCallException $e) {
            if ($role = static::findBySlug($method)) {
                return $role;
            }

            throw $e;
        }
    }

    /**
     * Создайте новый экземпляр фабрики для модели.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return config('is.factories.role')::new();
    }
}
