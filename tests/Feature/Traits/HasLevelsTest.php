<?php 

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\Models\UserHasLevels;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем функционал иерархии ролей.
 */
class HasLevelsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void 
    {
        parent::setUp();
        config(['is.models.user' => UserHasLevels::class]);
    }

    /**
     * Есть ли метод, возвращающий роль с наибольшим уровнем?
     *
     * @return void
     */
    public function test_role(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 1]));
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 2]));
        $role = config('is.models.role')::factory()->create(['level' => 3]);
        $user->attachRole($role);

        $this->assertTrue($role->is($user->role()));
    }

    /**
     * Есть ли метод, возвращающий наибольший уровень ролей, привязанных к модели?
     *
     * @return void
     */
    public function test_level(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 1]));
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 2]));
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 3]));

        $this->assertEquals(3, $user->level());
    }

    /**
     * Переопределен ли метод, возвращающий коллекцию ролей?
     *
     * @return void
     */
    public function test_get_roles(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        config('is.models.role')::factory(3)->create(['level' => 2]);
        $user->attachRole(config('is.models.role')::factory()->create(['level' => 3]));

        $this->assertTrue($user->getRoles()->count() > 1);
    }

    /**
     * Переопределен ли метод, присоединяющий роль?
     *
     * @return void
     */
    public function test_attach_role(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $this->assertTrue($user->attachRole(config('is.models.role')::factory()->create(['level' => 3])));
        $this->assertFalse($user->attachRole(config('is.models.role')::factory()->create(['level' => 2])));
    }

    /**
     * Переопределен ли метод, проверяющий наличие хотябы одной из переданных ролей?
     *
     * @return void
     */
    public function test_has_one_role(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $level1 = config('is.models.role')::factory()->create(['level' => 1]);
        $level2 = config('is.models.role')::factory()->create(['level' => 2]);
        $level3 = config('is.models.role')::factory()->create(['level' => 3]);
        $user->attachRole($level3);
        $this->assertTrue($user->hasOneRole($level3));
        $this->assertTrue($user->hasOneRole($level2));
        $this->assertTrue($user->hasOneRole($level1));
        $this->assertFalse($user->hasOneRole(config('is.models.role')::factory()->create(['level' => 5])));
    }

    /**
     * Переопределен ли метод, проверяющий наличие всех из переданных ролей?
     *
     * @return void
     */
    public function test_has_all_roles(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $level1 = config('is.models.role')::factory()->create(['level' => 1]);
        $level2 = config('is.models.role')::factory()->create(['level' => 2]);
        $level3 = config('is.models.role')::factory()->create(['level' => 3]);
        $user->attachRole($level3);
        $this->assertTrue($user->hasAllRoles($level1, $level2, $level3));
        $this->assertFalse($user->hasAllRoles($level1, config('is.models.role')::factory()->create(['level' => 5])));
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
