<?php

namespace dmitryrogolev\Is\Tests\Feature\Http\Middlewares;

use dmitryrogolev\Is\Tests\Models\User;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем посредника, проверяющего наличие необходимого уровня доступа.
 */
class VerifyLevelTest extends TestCase
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
     * Имя slug'а.
     */
    protected string $slugName;

    public function setUp(): void
    {
        parent::setUp();

        config(['is.uses.levels' => true]);

        $this->model = config('is.models.role');
        $this->user = User::class;
        $this->slugName = app($this->model)->getSlugName();
    }

    /**
     * Можно ли посетить страницу без аутентификации и с необходимым уровнем доступа?
     */
    public function test_without_auth(): void
    {
        $response = $this->get('level/1');
        $response->assertRedirectToRoute('welcome');
    }

    /**
     * Можно ли посетить страницу с необходимым уровнем доступа?
     */
    public function test_with_level(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model, [$this->slugName => 'user', 'level' => 2]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('level/2');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу с меньшим уровнем доступа?
     */
    public function test_with_lower_level(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model, [$this->slugName => 'user', 'level' => 2]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('level/3');
        $response->assertRedirectToRoute('welcome');
    }

    /**
     * Можно ли посетить страницу с большим уровнем доступа?
     */
    public function test_with_large_level(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model, [$this->slugName => 'user', 'level' => 5]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('level/4');
        $response->assertStatus(200);
    }
}
