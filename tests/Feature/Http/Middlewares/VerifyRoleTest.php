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
     * Можно ли посетить страницу без аутентификации, но с необходимой ролью?
     *
     * @return void
     */
    public function test_without_auth(): void 
    {
        config(['is.uses.levels' => false]);
        $user = $this->getUser();
        $user->attachRole(Role::generate(['slug' => 'user']));

        $response = $this->get('role/user');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с аутентификацией и со случайной ролью?
     *
     * @return void
     */
    public function test_with_some_role(): void 
    {
        config(['is.uses.levels' => false]);
        $user = $this->getUser();
        $user->attachRole(Role::generate());

        $response = $this->actingAs($user)->get('is/editor');
        $response->assertStatus(403);
    } 

    /**
     * Можно ли посетить страницу с необхомой ролью?
     *
     * @return void
     */
    public function test_with_role(): void 
    {
        config(['is.uses.levels' => false]);
        $user = $this->getUser();
        $user->attachRole(Role::generate(['slug' => 'admin']));

        $response = $this->actingAs($user)->post('is/admin');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу, имея одну из требуемых ролей?
     *
     * @return void
     */
    public function test_with_several_roles(): void 
    {
        config(['is.uses.levels' => false]);
        $user = $this->getUser();
        $user->attachRole(Role::generate(['slug' => 'moderator']));

        $response = $this->actingAs($user)->post('is/user/moderator/editor');
        $response->assertStatus(200);
    }

    /**
     * Можно ли посетить страницу с меньшим уровнем?
     *
     * @return void
     */
    public function test_with_lower_level(): void 
    {
        if (! config('is.uses.levels')) {
            $this->markTestSkipped('Иерархия ролей отключена.');
        }

        $user = $this->getUser();
        Role::generate(['slug' => 'editor', 'level' => 3]);
        $user->attachRole(Role::generate(['slug' => 'moderator', 'level' => 2]));

        $response = $this->actingAs($user)->get('role/editor');
        $response->assertStatus(403);
    }

    /**
     * Можно ли посетить страницу с большим уровнем?
     *
     * @return void
     */
    public function test_with_large_level(): void 
    {
        if (! config('is.uses.levels')) {
            $this->markTestSkipped('Иерархия ролей отключена.');
        }

        $user = $this->getUser();
        Role::generate(['slug' => 'editor', 'level' => 3]);
        $user->attachRole(Role::generate(['slug' => 'admin', 'level' => 5]));

        $response = $this->actingAs($user)->get('is/editor');
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
