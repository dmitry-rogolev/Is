<?php 

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Contracts\Sluggable;
use dmitryrogolev\Is\Traits\HasSlug;
use dmitryrogolev\Is\Traits\RoleHasRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель роли.
 */
abstract class Model extends Database implements Sluggable, ContractRoleHasRelations 
{
    use HasFactory, HasSlug, RoleHasRelations;

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

        $this->setTable(config('is.tables.roles'));

        if (config('is.uses.levels')) {
            array_push($this->fillable, 'level');
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

if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
    class Role extends Model
    {
        use HasUuids, SoftDeletes;
    }
} else if (config('is.uses.uuid')) {
    class Role extends Model
    {
        use HasUuids;
    }
} else if (config('is.uses.soft_deletes')) {
    class Role extends Model
    {
        use SoftDeletes;
    }
} else {
    class Role extends Model
    {
        
    }
}
