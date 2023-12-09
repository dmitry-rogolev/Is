<?php

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Contracts\Sluggable;
use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Traits\RoleHasRelations;
use dmitryrogolev\Traits\HasSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель роли.
 */
abstract class Model extends Database implements ContractRoleHasRelations, Sluggable
{
    use HasFactory, HasSlug, RoleHasRelations;

    /**
     * Атрибуты, для которых разрешено массовое присвоение значений.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'level',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('is.tables.roles'));
        array_push($this->fillable, $this->getSlugName());
    }

    /**
     * Возвращает столбцы, которые содержат уникальные данные.
     *
     * @return array<int, string>
     */
    public function uniqueKeys()
    {
        return [
            'name',
            $this->getSlugName(),
        ];
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
} elseif (config('is.uses.uuid')) {
    class Role extends Model
    {
        use HasUuids;
    }
} elseif (config('is.uses.soft_deletes')) {
    class Role extends Model
    {
        use SoftDeletes;
    }
} else {
    class Role extends Model
    {
    }
}
