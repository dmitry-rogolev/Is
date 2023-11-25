<?php 

namespace dmitryrogolev\Is\Tests\Feature\Http\Middlewares;

use dmitryrogolev\Is\Facades\Role;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем посредника, проверяющего наличие роли у пользователя.
 */
class VerifyRoleTest extends TestCase 
{
    use RefreshDatabase;

    /**
     * Есть ли посредник, проверяющий наличие роли у пользователя?
     *
     * @return void
     */
    public function test_verify_one_role(): void 
    {
        $user = $this->getUser();
        $role = Role::generate(['slug' => 'user']);
        $user->attachRole($role);

        $response = $this->get('role/user');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('role/user');
        $response->assertStatus(200);

        $user = $this->getUser();
        if (config('is.uses.levels')) {
            $role = Role::generate(['slug' => 'moderator', 'level' => 2]);
            Role::generate(['slug' => 'admin', 'level' => 5]);
        } else {
            $role = Role::generate(['slug' => 'moderator']);
            Role::generate(['slug' => 'admin']);
        }
        $user->attachRole($role);

        $response = $this->actingAs($user)->get('role/admin');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('role/moderator');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('role/user/moderator');
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
