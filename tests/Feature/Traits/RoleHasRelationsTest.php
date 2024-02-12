<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\Models\User;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Тестируем функционал, добавляющий модели роли отношения с другими моделями.
 */
class RoleHasRelationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Имя модели роли.
     */
    protected string $model;

    /**
     * Имя модели пользователя.
     */
    protected string $user;

    /**
     * Имя промежуточной модели.
     */
    protected string $pivot;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
        $this->user = User::class;
        $this->pivot = config('is.models.roleable');
    }

    /**
     * Относится ли роль к множеству моделей?
     */
    public function test_roleables(): void
    {
        $role = $this->generate($this->model);

        $users = $this->generate($this->user, 3);
        $users->each(fn ($user) => $user->roles()->attach($role));
        $expected = $users->pluck('id')->all();
        $relation = $role->roleables($this->user);
        $actual = $relation->get()->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат отношения.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $this->assertInstanceOf(MorphToMany::class, $relation);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Подтверждаем получение отношения.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $this->assertEquals($expected, $actual);
    }
}
