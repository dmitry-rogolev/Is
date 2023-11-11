<?php 

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Tests\TestCase;

class HasRolesTest extends TestCase 
{
    private string $user = '';
    private string $role = '';

    protected function setUp(): void 
    {
        parent::setUp();

        $this->user = config('is.models.user');
        $this->role = config('is.models.role');
    }

    /**
     * Проверяем получение ролей
     *
     * @return void
     */
    public function test_get_roles(): void 
    {
        $admin = $this->role::admin()->users()->first();

        $this->assertTrue($admin->roles->isNotEmpty());
    }

    /**
     * Присоединяем роль к пользователю
     *
     * @return void
     */
    public function test_attach_role(): void 
    {
        $user = $this->user::factory()->create();
        $roles = $this->role::all();

        // Присоединяем по идентификатору
        $role = $roles->get(2)->getKey();
        $this->assertTrue($user->attachRole($role));
        if (! config('is.uses.load_on_update')) {
            $user->loadRoles();
        }
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() == $role));

        // Присоединяем по slug
        $role = $roles->get(1)->slug;
        $this->assertTrue($user->attachRole($role));
        if (! config('is.uses.load_on_update')) {
            $user->loadRoles();
        }
        $this->assertTrue($user->roles->contains(fn ($item) => $item->slug == $role));

        // Присоединяем по модели
        $role = $roles->get(0);
        $this->assertTrue($user->attachRole($role));
        if (! config('is.uses.load_on_update')) {
            $user->loadRoles();
        }
        $this->assertTrue($user->roles->contains(fn ($item) => $item->is($role)));

        $role = 'undefined';
        $this->assertFalse($user->attachRole($role));

        $user = $this->user::factory()->create();

        // Присоединяем по множеству идентификаторов
        $roles = $this->role::all()->map(fn ($item) => $item->getKey());
        $this->assertTrue($user->attachRole($roles));
        foreach ($roles as $role) {
            $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() == $role));
        }
    }

    /**
     * Отсоединяем роль
     *
     * @return void
     */
    public function test_detach_role(): void 
    {
        $user = $this->role::admin()->users()->first();
        $roles = $user->roles;

        $this->assertTrue($roles->isNotEmpty());

        // Отсоединяем по идентификатору
        $role = $roles->get(0)->getKey();
        $user->detachRole($role);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $role));

        // Отсоединяем по slug
        $role = $roles->get(1)->slug;
        $user->detachRole($role);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->slug === $role));

        // Отсоединяем по модели
        $role = $roles->get(2);
        $user->detachRole($role);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->is($role)));

        $user = $this->user::factory()->create();
        for ($i = 0; $i < 10; $i++) {
            $user->roles()->attach($this->role::factory()->create());
        }
        $user->loadRoles();
        $this->assertTrue($user->roles->count() >= 10);

        // Отсоединяем множество ролей по идентификатору
        $roles = $user->roles;
        $this->assertTrue($user->detachRole([$roles->get(0)->getKey(), $roles->get(1)->getKey()]));
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $roles->get(0)->getKey() || $item->getKey() === $roles->get(1)->getKey()));
    }

    /**
     * Отсоединяем все роли
     * 
     * @return void
     */
    public function test_detach_all_roles(): void 
    {
        $user = $this->role::admin()->users()->first();
        $this->assertTrue($user->roles->isNotEmpty());
        $this->assertTrue($user->detachAllRoles());
        if (! config('is.uses.load_on_update')) {
            $user->loadRoles();
        }
        $this->assertTrue($user->roles->isEmpty());      
    }

    /**
     * Синхронизируем роли
     *
     * @return void
     */
    public function test_sync_roles(): void 
    {
        $user = $this->role::admin()->users()->first();

        $roles = $this->role::limit(2)->get();
        $user->syncRoles($roles);
        if (! config('is.uses.load_on_update')) {
            $user->loadRoles();
        }
        $this->assertCount($roles->count(), $user->roles);
        foreach ($roles as $role) {
            $this->assertTrue($user->roles->contains(fn ($item) => $item->is($role)));
        }
    }

    /**
     * Проверяем наличие хотябы одной роли
     *
     * @return void
     */
    public function test_has_one_role(): void 
    {
        $user = $this->role::admin()->users()->first();
        $roles = $user->roles;

        $this->assertTrue($user->hasOneRole($roles->get(0)->getKey()));
        $this->assertTrue($user->hasOneRole($roles->get(1)->slug));
        $this->assertTrue($user->hasOneRole($roles));
        $this->assertTrue($user->hasOneRole([$roles->get(0)->getKey(), $roles->get(1)->getKey()]));
        $this->assertTrue($user->hasOneRole($roles->get(0)->slug . '|' . $roles->get(1)->slug));

        $user = config('is.models.user')::factory()->create();
        $user->attachRole('admin');
        if (config('is.uses.levels')) {
            $this->assertTrue($user->hasOneRole($this->role::factory()->create([
                'level' => 1
            ])));
        } else {
            $this->assertFalse($user->hasOneRole($this->role::factory()->create()));
        }
    }

    /**
     * Проверяем наличие всех ролей
     *
     * @return void
     */
    public function test_has_all_roles(): void 
    {
        $user = $this->role::admin()->users()->first();
        $roles = $user->roles;

        $this->assertTrue($user->hasAllRoles($roles));
        $this->assertTrue($user->hasAllRoles($roles->get(0)->getKey()));
        $this->assertTrue($user->hasAllRoles($roles->get(0)->slug));

        $user = config('is.models.user')::factory()->create();
        $user->attachRole('admin');
        if (config('is.uses.levels')) {
            $this->assertTrue($user->hasAllRoles($this->role::factory()->create([
                'level' => 1
            ])));
            $this->assertTrue($user->hasAllRoles($this->role::factory()->count(3)->create([
                'level' => 2
            ])));
        } else {
            $this->assertFalse($user->hasRole($this->role::factory()->create()));
            $this->assertFalse($user->hasRole($this->role::factory()->count(3)->create()));
        }
    }

    /**
     * Проверяем наличие роли
     *
     * @return void
     */
    public function test_has_role(): void 
    {
        $user = $this->role::admin()->users()->first();
        $roles = $user->roles;

        $this->assertTrue($user->hasRole($roles->first()));
        $this->assertTrue($user->hasRole($roles, true));

        $user = config('is.models.user')::factory()->create();
        $user->attachRole('admin');
        if (config('is.uses.levels')) {
            $this->assertTrue($user->hasRole($this->role::factory()->create([
                'level' => 1
            ])));
        } else {
            $this->assertFalse($user->hasRole($this->role::factory()->create()));
        }
    }

    /**
     * Проверяем получение максимального уровня ролей
     *
     * @return void
     */
    public function test_level(): void 
    {
        if (! config('is.uses.levels')) {
            $this->markTestSkipped('Уровни ролей отключены.');
        }

        $user = $this->role::admin()->users()->first();

        $this->assertEquals($user->roles->sortByDesc('level')->first()->level, $user->level());
    }

    /**
     * Проверяем получение роли с максимальным уровнем
     *
     * @return void
     */
    public function test_role(): void 
    {
        if (! config('is.uses.levels')) {
            $this->markTestSkipped('Уровни ролей отключены.');
        }

        $user = $this->role::admin()->users()->first();

        $this->assertTrue($user->roles->sortByDesc('level')->first()->is($user->role()));
    }

    /**
     * Проверяем возможность получения роли с помощью магического метода
     *
     * @return void
     */
    public function test_magic_is(): void 
    {
        $user = $this->role::admin()->users()->first();

        $this->assertTrue($user->isUser());
        $this->assertTrue($user->isModerator());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isUndefined());

        $this->expectException(\BadMethodCallException::class);
        $this->assertFalse($user->undefined());
    }

    /**
     * Тестируем расширение метода is
     *
     * @return void
     */
    public function test_is(): void 
    {
        if (! config('is.uses.extend_is_method')) {
            $this->markTestSkipped('Метод is не расширен.');
        }

        $user = $this->role::admin()->users()->first();
        $this->assertTrue($user->is('admin'));
        $this->assertTrue($user->is($user));
        $this->assertFalse($user->is(config('is.models.user')::factory()->create()));
    }
}
