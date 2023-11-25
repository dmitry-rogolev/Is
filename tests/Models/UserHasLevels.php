<?php 

namespace dmitryrogolev\Is\Tests\Models;

use dmitryrogolev\Is\Contracts\Levelable;
use dmitryrogolev\Is\Models\Database;
use dmitryrogolev\Is\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Is\Traits\HasLevels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель пользователя. 
 */
abstract class BaseUserHasLevels extends Database implements Levelable, AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasFactory, HasLevels;

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
    class UserHasLevels extends BaseUserHasLevels
    {
        use HasUuids, SoftDeletes;
    }
} else if (config('is.uses.uuid')) {
    class UserHasLevels extends BaseUserHasLevels
    {
        use HasUuids;
    }
} else if (config('is.uses.soft_deletes')) {
    class UserHasLevels extends BaseUserHasLevels
    {
        use SoftDeletes;
    }
} else {
    class UserHasLevels extends BaseUserHasLevels
    {
        
    }
}
