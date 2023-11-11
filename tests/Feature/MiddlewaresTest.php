<?php 

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Tests\TestCase;

class MiddlewaresTest extends TestCase
{
    /**
     * Проверяем возможность работы с маршрутами
     *
     * @return void
     */
    public function test_welcome(): void 
    {
        $response = $this->get('welcome');

        $response->assertStatus(200);
    }

    /**
     * Проверяем аутентицикацию пользователя
     *
     * @return void
     */
    public function test_profile(): void 
    {
        $user = config('is.models.user')::factory()->create();

        $response = $this->actingAs($user)->get('profile');

        $response->assertStatus(200);
    }

    /**
     * Проверяем наличие роли
     *
     * @return void
     */
    public function test_has_role(): void 
    {
        // Пользователь имеет необходимую роль
        $user = config('is.models.user')::factory()->create();
        $user->attachRole('admin');
        $response = $this->actingAs($user)->post('role/admin', []);
        $response->assertStatus(200);

        // Пользователь имеет необходимую роль
        $user = config('is.models.user')::factory()->create();
        $user->attachRole('moderator');
        $response = $this->actingAs($user)->post('role/moderator-admin', []);
        $response->assertStatus(200);

        // У пользователя нет требуемой роли
        $user = config('is.models.user')::factory()->create();
        $response = $this->actingAs($user)->post('role/user', []);
        $response->assertStatus(403);
    }

    /**
     * Проверяем наличие необходимого уровня доступа
     *
     * @return void
     */
    public function test_has_level(): void 
    {
        if (! config('is.uses.levels')) {
            $this->markTestSkipped('Уровни ролей отключены.');
        }
        
        // Пользователь имеет необходимый уровень доступа
        $user = config('is.models.user')::factory()->create();
        $user->attachRole('admin'); // level 3
        $response = $this->actingAs($user)->post('level/2', []);
        $response->assertStatus(200);

        // У пользователя нет требуемого уровня доступа
        $user = config('is.models.user')::factory()->create();
        $user->attachRole('user');
        $response = $this->actingAs($user)->post('level/2', []);
        $response->assertStatus(403);
    }
}