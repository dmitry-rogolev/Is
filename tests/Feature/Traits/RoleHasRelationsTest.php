<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

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

    /**
     * Имя первичного ключа.
     */
    protected string $keyName;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
        $this->user = config('is.models.user');
        $this->pivot = config('is.models.roleable');
        $this->keyName = config('is.primary_key');
    }

    /**
     * Относится ли роль к множеству моделей?
     */
    public function test_roleables(): void
    {
        $role = $this->generate($this->model);

        $users = $this->generate($this->user, 3);
        $users->each(fn ($user) => $user->roles()->attach($role));
        $expected = $users->pluck($this->keyName)->all();
        $relation = $role->roleables($this->user);
        $actual = $relation->get()->pluck($this->keyName)->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат отношения.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $this->assertInstanceOf(MorphToMany::class, $relation);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Подтверждаем получение отношения.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $this->assertEquals($expected, $actual);
    }

    /**
     * Есть ли временные метки у загруженных отношений?
     */
    public function test_roleables_with_timestamps(): void
    {
        $role = $this->generate($this->model);
        $users = $this->generate($this->user, 3);
        $users->each(fn ($user) => $user->roles()->attach($role));
        $createdAtColumn = app($this->pivot)->getCreatedAtColumn();
        $updatedAtColumn = app($this->pivot)->getUpdatedAtColumn();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем наличие временных меток при включении опции.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => true]);
        $pivot = $role->roleables($this->user)->first()->pivot;
        $this->assertNotNull($pivot->{$createdAtColumn});
        $this->assertNotNull($pivot->{$updatedAtColumn});

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем отсутствие временных меток при отключении опции.         ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => false]);
        $pivot = $role->roleables($this->user)->first()->pivot;
        $this->assertNull($pivot->{$createdAtColumn});
        $this->assertNull($pivot->{$updatedAtColumn});
    }
}
