<?php 

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Traits\RoleHasRelations;
use dmitryrogolev\Is\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BaseRole extends Model implements ContractRoleHasRelations 
{
    use HasFactory, Sluggable, RoleHasRelations;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('is.connection');
        $this->table = config('is.tables.roles');
        $this->primaryKey = config('is.primary_key');
        $this->timestamps = config('is.uses.timestamps');
        $this->fillable = [
            'name', 
            'slug', 
            'description', 
        ];

        if (config('is.uses.levels')) {
            array_push($this->fillable, 'level');
        }
    }

    /**
     * Возвращаем роль по ее slug
     * 
     * Например, Role::admin(), Role::user(), Role::moderator()
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
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

    protected static function newFactory()
    {
        return config('is.factories.role')::new();
    }
}
