<?php

namespace dmitryrogolev\Is\Tests\Feature\Http\Middlewares;

use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем посредника, проверяющего наличие роли у пользователя.
 */
class VerifyRoleTest extends TestCase
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

        config(['is.uses.levels' => false]);

        $this->model = config('is.models.role');
        $this->user = config('is.models.user');
        $this->slugName = app($this->model)->getSlugName();
    }

    /**
     * Можно ли посетить страницу без аутентификации, но с необходимой ролью?
     */
    public function test_without_auth(): void
    {
        $response = $this->get('role/user');
        $response->assertRedirectToRoute('welcome');
    }

    /**
     * Можно ли посетить страницу с аутентификацией и со случайной ролью?
     */
    public function test_with_some_role(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('is/editor');
        $response->assertRedirectToRoute('welcome');
    }

    /**
     * Можно ли посетить страницу с необходимой ролью?
     */
    public function test_with_role(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model, [$this->slugName => 'admin']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->post('is/admin');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу, имея одну из требуемых ролей?
     */
    public function test_with_several_roles(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model, [$this->slugName => 'moderator']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->post('is/user/moderator/editor');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу с меньшим уровнем?
     */
    public function test_with_lower_level(): void
    {
        $user = $this->generate($this->user);
        $this->generate($this->model, [$this->slugName => 'editor', 'level' => 3]);
        $role = $this->generate($this->model, [$this->slugName => 'moderator', 'level' => 2]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('role/editor');
        $response->assertRedirectToRoute('welcome');
    }

    /**
     * Можно ли посетить страницу с большим уровнем?
     */
    public function test_with_large_level(): void
    {
        $user = $this->generate($this->user);
        $this->generate($this->model, [$this->slugName => 'editor', 'level' => 3]);
        $role = $this->generate($this->model, [$this->slugName => 'admin', 'level' => 5]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('is/editor');
        $response->assertRedirectToRoute('welcome');
    }
}
