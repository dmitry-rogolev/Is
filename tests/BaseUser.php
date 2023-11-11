<?php

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Traits\HasRoles;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class BaseUser extends Model implements AuthorizableContract, AuthenticatableContract, Roleable
{
    use Authorizable;
    use Authenticatable;
    use HasFactory;
    use HasRoles;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('is.connection');
        $this->table = 'users';
        $this->primaryKey = config('is.primary_key');
        $this->timestamps = config('is.uses.timestamps');
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}