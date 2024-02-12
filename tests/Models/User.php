<?php

namespace dmitryrogolev\Is\Tests\Models;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Is\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Model;

class User extends Model implements Roleable
{
    use HasFactory;
    use HasRoles;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
