<?php

namespace dmitryrogolev\Is\Tests\Models;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Is\Traits\HasRoles;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * Модель пользователя.
 */
abstract class BaseUser extends Database implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, Roleable
{
    use Authenticatable, Authorizable, CanResetPassword, HasFactory, HasRoles, MustVerifyEmail;

    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Атрибуты, для которых НЕ разрешено массовое присвоение значений.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Создайте новый экземпляр фабрики для модели.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}

if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
    class User extends BaseUser
    {
        use HasUuids, SoftDeletes;
    }
} elseif (config('is.uses.uuid')) {
    class User extends BaseUser
    {
        use HasUuids;
    }
} elseif (config('is.uses.soft_deletes')) {
    class User extends BaseUser
    {
        use SoftDeletes;
    }
} else {
    class User extends BaseUser
    {
    }
}
