<?php

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Contracts\Sluggable;
use dmitryrogolev\Is\Facades\Is;
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
        'level',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(Is::rolesTable());
    }

    /**
     * Создайте новый экземпляр фабрики для модели.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return Is::roleFactory()::new();
    }
}

if (Is::usesUuid() && Is::usesSoftDeletes()) {
    class Role extends Model
    {
        use HasUuids, SoftDeletes;
    }
} else if (Is::usesUuid()) {
    class Role extends Model
    {
        use HasUuids;
    }
} else if (Is::usesSoftDeletes()) {
    class Role extends Model
    {
        use SoftDeletes;
    }
} else {
    class Role extends Model
    {

    }
}
