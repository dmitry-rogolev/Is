<?php 

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\Models\UserExtendIsMethod;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем расширение метода "is", добавляющий ему проверку наличия роли у модели.
 */
class ExtendIsMethodTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void 
    {
        parent::setUp();
        config(['is.models.user' => UserExtendIsMethod::class]);
        config(['is.uses.levels' => true]);
    }

    /**
     * Есть ли метод, расширяющий метод "is"?
     *
     * @return void
     */
    public function test_is(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $role = config('is.models.role')::factory()->create(['level' => 1]);
        $user->attachRole($role);

        $this->assertTrue($user->is($user));
        $this->assertFalse($user->is($this->getUser()));
        $this->assertTrue($user->is($role));
        $this->assertTrue($user->is($role->getKey()));
        $this->assertFalse($user->is(config('is.models.role')::factory()->create(['level' => 2])));
        $this->assertTrue($user->is([$role, config('is.models.role')::factory()->create(['level' => 1])], true));
        $this->assertFalse($user->is([$role, config('is.models.role')::factory()->create(['level' => 2])], true));
    }

    /**
     * Возвращает пользователя, который относится к множеству ролей.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function getUserWithRoles(int $count = 3): Model
    {
        $roles = $this->getRole($count);
        $user = $this->getUser();
        $roles->each(fn ($item) => $user->roles()->attach($item));

        return $user;
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

    /**
     * Возвращает случайно сгенерированную роль.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    private function getRole(int $count = 1): Model|Collection
    {
        $factory = config('is.models.role')::factory();

        return $count > 1 ? $factory->count($count)->create() : $factory->create();
    }
}
