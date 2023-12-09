<?php

namespace dmitryrogolev\Is\Tests\Feature\Http\Middlewares;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем посредника, проверяющего наличие необходимого уровня доступа.
 */
class VerifyLevelTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Включаем иерархию ролей.
        config(['is.uses.levels' => true]);
    }

    /**
     * Можно ли посетить страницу без аутентификации и с необходимым уровнем доступа?
     */
    public function test_without_auth(): void
    {
        $user = $this->generate(config('is.models.user'));
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 1]));

        $response = $this->get('level/1');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с необходимым уровнем доступа?
     */
    public function test_with_level(): void
    {
        $user = $this->generate(config('is.models.user'));
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 2]));

        $response = $this->actingAs($user)->get('level/2');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу с меньшим уровнем доступа?
     */
    public function test_with_lower_level(): void
    {
        $user = $this->generate(config('is.models.user'));
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 2]));

        $response = $this->actingAs($user)->get('level/3');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с большим уровнем доступа?
     */
    public function test_with_large_level(): void
    {
        $user = $this->generate(config('is.models.user'));
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 5]));

        $response = $this->actingAs($user)->get('level/4');
        $response->assertStatus(200);
    }
}
