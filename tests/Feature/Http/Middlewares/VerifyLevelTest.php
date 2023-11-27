<?php

namespace dmitryrogolev\Is\Tests\Feature\Http\Middlewares;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
        Is::usesLevels(true);
    }

    /**
     * Можно ли посетить страницу без аутентификации и с необходимым уровнем доступа?
     *
     * @return void
     */
    public function test_without_auth(): void
    {
        $user = $this->getUser();
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 1]));

        $response = $this->get('level/1');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с необходимым уровнем доступа?
     *
     * @return void
     */
    public function test_with_level(): void
    {
        $user = $this->getUser();
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 2]));

        $response = $this->actingAs($user)->get('level/2');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу с меньшим уровнем доступа?
     *
     * @return void
     */
    public function test_with_lower_level(): void
    {
        $user = $this->getUser();
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 2]));

        $response = $this->actingAs($user)->get('level/3');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с большим уровнем доступа?
     *
     * @return void
     */
    public function test_with_large_level(): void
    {
        $user = $this->getUser();
        $user->attachRole(Is::generate(['slug' => 'user', 'level' => 5]));

        $response = $this->actingAs($user)->get('level/4');
        $response->assertStatus(200);
    }

    /**
     * Возвращает случайно сгенерированного пользователя.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    private function getUser(int $count = 1): Model|Collection
    {
        $factory = config('is.models.user')::factory();

        return $count > 1 ? $factory->count($count)->create() : $factory->create();
    }
}
