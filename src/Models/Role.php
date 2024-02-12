<?php

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Contracts\Sluggable;
use dmitryrogolev\Is\Contracts\RoleHasRelations as ContractRoleHasRelations;
use dmitryrogolev\Is\Database\Factories\RoleFactory;
use dmitryrogolev\Is\Traits\RoleHasRelations;
use dmitryrogolev\Traits\HasSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model implements ContractRoleHasRelations, Sluggable
{
    use HasFactory;
    use HasSlug;
    use HasUuids;
    use RoleHasRelations;
    use SoftDeletes;

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

    public function uniqueKeys()
    {
        return [
            $this->getSlugName(),
        ];
    }

    public static function toSlug(string $str): string
    {
        return Str::slug($str, config('is.separator'));
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
