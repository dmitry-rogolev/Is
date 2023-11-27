<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use dmitryrogolev\Is\Traits\ExtendIsMethod;
use dmitryrogolev\Is\Traits\HasLevels;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем функционал ролей.
 */
class HasRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Есть ли метод, возвращающий отношений модели с ролями?
     *
     * @return void
     */
    public function test_roles(): void
    {
        // Проверяем наличие метода связи модели с ролями.
        $user  = $this->getUserWithRoles();
        $roles = $user->roles()->get()->pluck(Is::primaryKey());
        $this->assertEquals($roles, $user->roles->pluck(Is::primaryKey()));

        // Проверяем наличие временных меток у промежуточной модели ролей.
        $createdAtColumn = app(Is::roleableModel())->getCreatedAtColumn();
        $updatedAtColumn = app(Is::roleableModel())->getUpdatedAtColumn();

        Is::usesTimestamps(true);
        $role = $user->roles()->first();
        $this->assertTrue($role->pivot->{$createdAtColumn} && $role->pivot->{$updatedAtColumn});

        Is::usesTimestamps(false);
        $role = $user->roles()->first();
        $this->assertTrue(! $role->pivot->{$createdAtColumn} && ! $role->pivot->{$updatedAtColumn});
    }

    /**
     * Есть ли метод, возвращающий коллекцию ролей модели?
     *
     * @return void
     */
    public function test_get_roles(): void
    {
        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user = $this->getUser();
        $user->roles()->attach(Is::generate(['level' => 2]));
        Is::generate(['level' => 1]);
        $this->assertCount(2, $user->getRoles());

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $user = $this->getUserWithRoles();
        $this->assertEquals(
            $user->roles->pluck(Is::primaryKey()),
            $user->getRoles()->pluck(Is::primaryKey()),
        );
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
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user = $this->getUser();
        $this->assertTrue($user->attachRole(Is::generate(['level' => 3])));
        $this->assertFalse($user->attachRole(Is::generate(['level' => 2])));
        $this->assertCount(2, $user->getRoles());

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $role = Is::generate();
        $this->assertTrue($user->attachRole($role));
        $this->assertFalse($user->attachRole($role));
        $this->assertCount(2, $user->getRoles());

        // Отключаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(false);

        $user->attachRole(Is::generate(3));
        $this->assertCount(2, $user->getRoles());
        $user->loadRoles();
        $this->assertCount(5, $user->getRoles());
    }

    /**
     * Есть ли метод, отсоединяющий роль?
     *
     * @return void
     */
    public function test_detach_role(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user = $this->getUser();
        $role = Is::generate(['level' => 3]);
        $user->attachRole($role);
        $this->assertTrue($user->detachRole(Is::generate(['level' => 2])));
        $this->assertFalse($user->detachRole(Is::generate(['level' => 4])));
        $this->assertCount(2, $user->getRoles());
        $this->assertTrue($user->detachRole($role));
        $this->assertCount(0, $user->getRoles());

        // Есть не передать аргумент, то будут отсоединены все роли.
        $user->attachRole(Is::generate());
        $this->assertNotCount(0, $user->getRoles());
        $this->assertTrue($user->detachRole());
        $this->assertCount(0, $user->getRoles());

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $role = Is::generate();
        $user->attachRole($role);
        $this->assertFalse($user->detachRole(Is::generate()));
        $this->assertTrue($user->detachRole($role));
        $this->assertCount(0, $user->getRoles());

        // Отключаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(false);

        $user->attachRole(Is::generate(3));
        $user->loadRoles();
        $this->assertCount(3, $user->getRoles());
        $user->detachRole();
        $this->assertCount(3, $user->getRoles());
        $user->loadRoles();
        $this->assertCount(0, $user->getRoles());
    }

    /**
     * Есть ли метод, отсоединяющий все роли?
     *
     * @return void
     */
    public function test_detach_all_roles(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        $user = $this->getUserWithRoles();
        $this->assertTrue($user->detachAllRoles());
        $this->assertCount(0, $user->getRoles());
        $this->assertFalse($user->detachAllRoles());

        // Отключаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(false);

        $user = $this->getUserWithRoles(5);
        $this->assertTrue($user->detachAllRoles());
        $this->assertNotCount(0, $user->getRoles());
        $user->loadRoles();
        $this->assertCount(0, $user->getRoles());
    }

    /**
     * Есть ли метод, синхронизирующий роли?
     *
     * @return void
     */
    public function test_sync_roles(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user = $this->getUser();
        $user->attachRole(Is::generate(['level' => 2]));
        $role = Is::generate(['level' => 3]);
        $user->syncRoles($role);
        $this->assertCount(2, $user->getRoles());

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $roles = Is::generate(3);
        $user->syncRoles($roles);
        $this->assertEquals($roles->pluck(Is::primaryKey()), $user->getRoles()->pluck(Is::primaryKey()));
    }

    /**
     * Есть ли метод, проверяющий наличие хотябы одной переданной роли?
     *
     * @return void
     */
    public function test_has_one_role(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user = $this->getUser();
        $user->attachRole(Is::generate(['level' => 3]));
        $this->assertTrue($user->hasOneRole(Is::generate(['level' => 2])));
        $this->assertFalse($user->hasOneRole(Is::generate(['level' => 4])));

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $role = Is::generate();
        $user->attachRole($role);
        $this->assertTrue($user->hasOneRole($role));
        $this->assertFalse($user->hasOneRole(Is::generate()));
    }

    /**
     * Есть ли метод, проверяющий наличие всех переданных ролей?
     *
     * @return void
     */
    public function test_has_all_roles(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $user   = $this->getUser();
        $level1 = Is::generate(['level' => 1]);
        $level2 = Is::generate(['level' => 2]);
        $level3 = Is::generate(['level' => 3]);
        $user->attachRole($level2);
        $this->assertTrue($user->hasAllRoles($level1, $level2));
        $this->assertFalse($user->hasAllRoles($level1, $level2, $level3));

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $user->attachRole($level3);
        $this->assertTrue($user->hasAllRoles($level2, $level3));
        $this->assertFalse($user->hasAllRoles($level1, $level2, $level3));
    }

    /**
     * Есть ли метод, проверяющий наличие ролей?
     *
     * @return void
     */
    public function test_has_role(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);

        // Проверяем наличие хотябы одной роли.
        $user = $this->getUser();
        $user->attachRole(Is::generate(['level' => 3]));
        $this->assertTrue($user->hasRole(Is::generate(['level' => 2])));
        $this->assertFalse($user->hasRole(Is::generate(['level' => 4])));
        $user->detachAllRoles();

        // Проверяем наличие нескольких ролей.
        $level1 = Is::generate(['level' => 1]);
        $level2 = Is::generate(['level' => 2]);
        $level3 = Is::generate(['level' => 3]);
        $user->attachRole($level2);
        $this->assertTrue($user->hasRole([$level1, $level2], true));
        $this->assertFalse($user->hasAllRoles([$level1, $level2, $level3], true));
        $user->detachAllRoles();

        // Отключаем иерархию ролей.
        Is::usesLevels(false);

        // Проверяем наличие хотябы одной роли.
        $role = Is::generate();
        $user->attachRole($role);
        $this->assertTrue($user->hasRole($role));
        $this->assertFalse($user->hasRole(Is::generate()));
        $user->detachAllRoles();

        // Проверяем наличие нескольких ролей.
        $user->attachRole($level2, $level3);
        $this->assertTrue($user->hasRole([$level2, $level3], true));
        $this->assertFalse($user->hasRole([$level1, $level2, $level3], true));
    }

    /**
     * Есть ли магический метод, проверяющий наличие роли по его slug'у?
     *
     * @return void
     */
    public function test_call_magic_is_role(): void
    {
        // Включаем авто подгрузку ролей.
        Is::usesLoadOnUpdate(true);

        // Включаем иерархию ролей.
        Is::usesLevels(true);
        $level1 = Is::generate(['level' => 1]);
        $level2 = Is::generate(['level' => 2]);
        $level3 = Is::generate(['level' => 3]);
        $user   = $this->getUser();
        $user->attachRole($level2);
        $this->assertTrue($user->{'is' . ucfirst($level2->slug)}());
        $this->assertTrue($user->{'is' . ucfirst($level1->slug)}());
        $this->assertFalse($user->{'is' . ucfirst($level3->slug)}());

        // Отключаем иерархию ролей.
        Is::usesLevels(false);
        $role = Is::generate();
        $user->attachRole($role);
        $this->assertTrue($user->{'is' . ucfirst($role->slug)}());
        $this->assertFalse($user->{'is' . ucfirst(Is::generate()->slug)}());
    }

    /**
     * Подключены ли трейты согласно конфигурации?
     *
     * @return void
     */
    public function test_uses_traits(): void
    {
        $traits = collect(class_uses_recursive(app(Is::userModel())));

        $this->assertTrue($traits->contains(HasLevels::class));
        $this->assertTrue($traits->contains(ExtendIsMethod::class));
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
        $user  = $this->getUser();
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
