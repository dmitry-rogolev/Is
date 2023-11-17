<?php

namespace dmitryrogolev\Is\Tests\Models;

use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Is\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * Модель пользователя. 
 */
class BaseUser extends Database implements Roleable, AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasFactory, HasRoles;

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