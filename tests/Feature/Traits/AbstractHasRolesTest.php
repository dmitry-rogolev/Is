<?php 

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\Models\UserAbstractHasRoles;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем функционал ролей.
 */
class AbstractHasRolesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void 
    {
        parent::setUp();
        config(['is.models.user' => UserAbstractHasRoles::class]);
    }

    /**
     * Есть ли метод, возвращающий отношений модели с ролями?
     *
     * @return void
     */
    public function test_roles(): void 
    {
        $user = $this->getUserWithRoles();
        $roles = $user->roles()->get()->pluck(config('is.primary_key'));
        $this->assertEquals($roles, $user->roles->pluck(config('is.primary_key')));

        // Есть ли временные метки у промежуточной таблицы ролей?
        $createdAtColumn = app(config('is.models.roleable'))->getCreatedAtColumn();
        $updatedAtColumn = app(config('is.models.roleable'))->getUpdatedAtColumn();

        config(['is.uses.timestamps' => true]);
        $role = $user->roles()->first();
        $this->assertTrue($role->pivot->{$createdAtColumn} && $role->pivot->{$updatedAtColumn});

        config(['is.uses.timestamps' => false]);
        $this->refreshDatabase();
        $role = $user->roles()->first();
        $this->assertFalse($role->pivot->{$createdAtColumn} && $role->pivot->{$updatedAtColumn});
    }

    /**
     * Есть ли метод, возвращающий коллекцию ролей модели?
     *
     * @return void
     */
    public function test_get_roles(): void 
    {
        $user = $this->getUserWithRoles();
        $this->assertEquals($user->roles->pluck(config('is.primary_key')), 
                            $user->getRoles()->pluck(config('is.primary_key')));
    }

    /**
     * Есть ли метод, подгружающий отношение модели с ролями?
     *
     * @return void
     */
    public function test_load_roles(): void 
    {
        $user = $this->getUserWithRoles();
        $user->loadRoles()->roles()->detach();
        $this->assertTrue($user->roles->isNotEmpty());
        $user->loadRoles();
        $this->assertTrue($user->roles->isEmpty());
    }

    /**
     * Есть ли метод, присоединяющий роль к модели?
     *
     * @return void
     */
    public function test_attach_role(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUser();
        $roles = $this->getRole(3);
        $this->assertTrue($user->attachRole($roles));
        $this->assertEquals($roles->pluck(config('is.primary_key')), $user->roles->pluck(config('is.primary_key')));

        // При попытке повторного присоединения роли вернется false.
        $role = $this->getRole();
        $this->assertTrue($user->attachRole($role));
        $this->assertFalse($user->attachRole($role));

        config(['is.uses.load_on_update' => false]);
        $user = $this->getUser();
        $roles = $this->getRole(3);
        $this->assertTrue($user->attachRole($roles));
        $this->assertCount(0, $user->roles);
        $user->loadRoles();
        $this->assertCount(3, $user->roles);
    }

    /**
     * Есть ли метод, отсоединяющий роль?.
     *
     * @return void
     */
    public function test_detach_role(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUserWithRoles(5);
        $roles = $user->roles->slice(0, 3);
        $this->assertTrue($user->detachRole($user->roles->slice(3)));
        $this->assertEquals($roles->pluck(config('is.primary_key')), $user->roles->pluck(config('is.primary_key')));

        // При попытке отсоеднинения отсутствующей у пользователя роли вернется false.
        $this->assertFalse($user->detachRole($this->getRole()));

        // Есть не передать аргрумент, то будут отсоединены все роли.
        $user = $this->getUserWithRoles();
        $this->assertTrue($user->detachRole());
        $this->assertCount(0, $user->roles);

        config(['is.uses.load_on_update' => false]);
        $user = $this->getUserWithRoles(5);
        $this->assertTrue($user->detachRole());
        $this->assertCount(5, $user->roles);
        $user->loadRoles();
        $this->assertCount(0, $user->roles);
    }

    /**
     * Есть ли метод, отсоединяющий все роли?
     *
     * @return void
     */
    public function test_detach_all_roles(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUserWithRoles();
        $this->assertTrue($user->detachAllRoles());
        $this->assertCount(0, $user->roles);
        $this->assertFalse($user->detachAllRoles());

        config(['is.uses.load_on_update' => false]);
        $user = $this->getUserWithRoles(5);
        $this->assertTrue($user->detachAllRoles());
        $this->assertCount(5, $user->roles);
        $user->loadRoles();
        $this->assertCount(0, $user->roles);
    }

    /**
     * Есть ли метод, синхронизирующий роли?
     *
     * @return void
     */
    public function test_sync_roles(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUserWithRoles();
        $roles = $this->getRole(5);
        $user->syncRoles($roles);
        $this->assertEquals($roles->pluck(config('is.primary_key')), $user->roles->pluck(config('is.primary_key')));
    }

    /**
     * Есть ли метод, проверяющий наличие хотябы одной переданной роли?
     *
     * @return void
     */
    public function test_has_one_role(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUser();
        $role = $this->getRole();
        $user->attachRole($role);
        $this->assertTrue($user->hasOneRole($role));
        $this->assertFalse($user->hasOneRole($this->getRole()));
    }

    /**
     * Есть ли метод, проверяющий наличие всех переданных ролей?
     *
     * @return void
     */
    public function test_has_all_roles(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUser();
        $roles = $this->getRole(3);
        $user->attachRole($roles);
        $this->assertTrue($user->hasAllRoles($roles));
        $this->assertFalse($user->hasAllRoles($this->getRole(2)));
    }

    /**
     * Есть ли метод, проверяющий наличие ролей?
     *
     * @return void
     */
    public function test_has_role(): void 
    {
        config(['is.uses.load_on_update' => true]);

        $user = $this->getUser();
        $role = $this->getRole();
        $user->attachRole($role);
        $this->assertTrue($user->hasRole($role));
        $this->assertFalse($user->hasRole($this->getRole()));

        $user = $this->getUser();
        $roles = $this->getRole(3);
        $user->attachRole($roles);
        $this->assertTrue($user->hasRole($roles, true));
        $this->assertFalse($user->hasRole($this->getRole(2), true));
    }

    /**
     * Есть ли магический метод, проверяющий наличие роли по его slug'у?
     *
     * @return void
     */
    public function test_call_magic_is_role(): void 
    {
        config(['is.uses.load_on_update' => true]);
        $user = $this->getUser();
        $role = $this->getRole();
        $user->attachRole($role);
        $this->assertTrue($user->{'is'.ucfirst($role->slug)}());
        $this->assertFalse($user->isAdmin());
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
