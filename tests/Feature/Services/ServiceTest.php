<?php

namespace dmitryrogolev\Is\Tests\Feature\Services;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем сервис работы с таблицей ролей.
 */
class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Есть ли метод, возвращающий имя модели сервиса?
     *
     * @return void
     */
    public function test_get_model(): void
    {
        $this->assertEquals(Is::roleModel(), Is::getModel());
    }

    /**
     * Есть ли метод, возвращающий имя сидера модели?
     *
     * @return void
     */
    public function test_get_seeder(): void
    {
        $this->assertEquals(Is::roleSeeder(), Is::getSeeder());
    }

    /**
     * Есть ли метод, возвращающий имя фабрики модели?
     *
     * @return void
     */
    public function test_get_factory(): void
    {
        $this->assertEquals(Is::roleFactory(), Is::getFactory());
    }

    /**
     * Есть ли метод, возвращающий все записи таблицы?
     *
     * @return void
     */
    public function test_index(): void
    {
        // Получаем все роли из таблицы.
        $count = random_int(1, 10);
        $this->getRole($count);
        $this->assertCount($count, Is::index());
        $this->assertCount($count, Is::all());

        // Получаем все роли пользователя.
        $user  = $this->getUserWithRoles(3);
        $roles = Is::usesLevels() ? Is::getModel()::where('level', '<=', $user->level())->get() : $user->roles;
        $this->assertCount($roles->count(), Is::index($user));
        $this->assertCount($roles->count(), Is::all($user));
    }

    /**
     * Есть ли метод, возвращающий случайную модели из таблицы?
     *
     * @return void
     */
    public function test_random(): void
    {
        $this->getRole(10);
        $this->assertModelExists(Is::random());
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору?
     *
     * @return void
     */
    public function test_show(): void
    {
        // Получаем роль из таблицы.
        $role = $this->getRole();
        $this->assertTrue($role->is(Is::show($role->getKey())));
        $this->assertTrue($role->is(Is::show($role)));
        $this->assertNull(Is::show($this->getUser()));

        $this->assertTrue($role->is(Is::find($role->getKey())));
        $this->assertTrue($role->is(Is::find($role)));
        $this->assertNull(Is::find($this->getUser()));

        // Получаем роль пользователя.
        Is::usesLevels(false);

        $user = $this->getUser();
        $role = $this->getRole();
        $user->roles()->attach($role);

        $this->assertTrue($role->is(Is::show($role->getKey(), $user)));
        $this->assertTrue($role->is(Is::show($role->getSlug(), $user)));
        $this->assertTrue($role->is(Is::show($role, $user)));
        $this->assertNull(Is::show($this->getRole(), $user));

        $this->assertTrue($role->is(Is::find($role->getKey(), $user)));
        $this->assertTrue($role->is(Is::find($role->getSlug(), $user)));
        $this->assertTrue($role->is(Is::find($role, $user)));
        $this->assertNull(Is::find($this->getRole(), $user));

        Is::usesLevels(true);

        $user = $this->getUser();
        $role = Is::getModel()::factory()->create(['level' => 1]);
        $user->roles()->attach(Is::getModel()::factory()->create(['level' => 5]));

        $this->assertTrue($role->is(Is::show($role->getKey(), $user)));
        $this->assertTrue($role->is(Is::show($role->getSlug(), $user)));
        $this->assertTrue($role->is(Is::show($role, $user)));
        $this->assertNull(Is::show(Is::getModel()::factory()->create(['level' => 7]), $user));

        $this->assertTrue($role->is(Is::find($role->getKey(), $user)));
        $this->assertTrue($role->is(Is::find($role->getSlug(), $user)));
        $this->assertTrue($role->is(Is::find($role, $user)));
        $this->assertNull(Is::find(Is::getModel()::factory()->create(['level' => 7]), $user));
    }

    /**
     * Есть ли метод, проверяющий наличие модели в таблице?
     *
     * @return void
     */
    public function test_has(): void
    {
        // Проверяем наличие роли в таблице.
        $role = $this->getRole();
        $this->assertTrue(Is::has($role));
        $this->assertTrue(Is::has($role->getKey()));
        $this->assertFalse(Is::has('my_key'));
        $this->assertFalse(Is::has(Is::getModel()::factory()->make()));

        // Проверяем наличие роли у пользователя.
        Is::usesLevels(false);

        $user = $this->getUser();
        $role = $this->getRole();
        $user->roles()->attach($role);

        $this->assertTrue(Is::has($role->getKey(), $user));
        $this->assertTrue(Is::has($role->getSlug(), $user));
        $this->assertTrue(Is::has($role, $user));
        $this->assertFalse(Is::has($this->getRole(), $user));

        Is::usesLevels(true);

        $user = $this->getUser();
        $role = Is::getModel()::factory()->create(['level' => 1]);
        $user->roles()->attach(Is::getModel()::factory()->create(['level' => 5]));

        $this->assertTrue(Is::has($role->getKey(), $user));
        $this->assertTrue(Is::has($role->getSlug(), $user));
        $this->assertTrue(Is::has($role, $user));
        $this->assertFalse(Is::has(Is::getModel()::factory()->create(['level' => 7]), $user));
    }

    /**
     * Есть ли метод, создающий модель?
     *
     * @return void
     */
    public function test_make(): void
    {
        $model = Is::make(['name' => 'Admin', 'slug' => 'admin']);

        $this->assertEquals('admin', $model->getSlug());
        $this->assertModelMissing($model);
    }

    /**
     * Есть ли метод, создающий модель, только если она не существует в таблице?
     *
     * @return void
     */
    public function test_make_if_not_exists(): void
    {
        $attributes = ['name' => 'Admin', 'slug' => 'admin'];

        $this->assertNotNull(Is::makeIfNotExists($attributes));
        Is::roleModel()::create($attributes);
        $this->assertNull(Is::makeIfNotExists($attributes));
    }

    /**
     * Есть ли метод, создающий группу моделей?
     *
     * @return void
     */
    public function test_make_group(): void
    {
        $group = [
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Moderator', 'slug' => 'moderator'],
            ['name' => 'Editor', 'slug' => 'editor'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        $models = Is::makeGroup($group);
        $this->assertCount(4, $models);
        $this->assertTrue($models->every(fn ($item) => ! $item->exists));
    }

    /**
     * Есть ли метод, создающий группу не существующих моделей?
     *
     * @return void
     */
    public function test_make_group_if_not_exists(): void
    {
        $group = [
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Moderator', 'slug' => 'moderator'],
            ['name' => 'Editor', 'slug' => 'editor'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        $models = Is::makeGroupIfNotExists($group);
        $this->assertCount(4, $models);
        $this->assertTrue($models->every(fn ($item) => ! $item->exists));

        collect($group)->each(fn ($item) => Is::getModel()::create($item));

        $models = Is::makeGroupIfNotExists($group);
        $this->assertCount(0, $models);
    }

    /**
     * Есть ли метод, создающий модель и сохраняющий ее в таблице?
     *
     * @return void
     */
    public function test_store(): void
    {
        $this->assertModelExists(Is::store(['name' => 'Admin', 'slug' => 'admin']));
        $this->assertModelExists(Is::create(['name' => 'User', 'slug' => 'user']));
    }

    /**
     * Есть ли метод, создающий модель и сохраняющий ее в таблице, 
     * только если она не существует в таблице?
     *
     * @return void
     */
    public function test_store_if_not_exists(): void
    {
        $this->assertModelExists(Is::store(['name' => 'Admin', 'slug' => 'admin']));
        $this->assertNull(Is::storeIfNotExists(['name' => 'Admin', 'slug' => 'admin']));

        $this->assertModelExists(Is::create(['name' => 'User', 'slug' => 'user']));
        $this->assertNull(Is::createIfNotExists(['name' => 'User', 'slug' => 'user']));
    }

    /**
     * Есть ли метод, создающий группу моделей?
     *
     * @return void
     */
    public function test_store_group(): void
    {
        $group = [
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Moderator', 'slug' => 'moderator'],
            ['name' => 'Editor', 'slug' => 'editor'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        $models = Is::storeGroup($group);
        $this->assertCount(4, $models);
        $this->assertTrue($models->every(fn ($item) => $item->exists));

        Is::roleModel()::truncate();

        $models = Is::createGroup($group);
        $this->assertCount(4, $models);
        $this->assertTrue($models->every(fn ($item) => $item->exists));
    }

    /**
     * Есть ли метод, создающий группу не существующих моделей?
     *
     * @return void
     */
    public function test_store_group_if_not_exists(): void
    {
        $group = [
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Moderator', 'slug' => 'moderator'],
            ['name' => 'Editor', 'slug' => 'editor'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        $models = Is::storeGroupIfNotExists($group);
        $this->assertCount(4, $models);
        $this->assertTrue($models->every(fn ($item) => $item->exists));

        $models = Is::storeGroupIfNotExists($group);
        $this->assertCount(0, $models);

        Is::roleModel()::truncate();

        $models = Is::createGroupIfNotExists($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        $models = Is::createGroupIfNotExists($group);
        $this->assertCount(0, $models);
    }

    /**
     * Есть ли метод, возвращающий фабрику модели?
     *
     * @return void
     */
    public function test_factory(): void
    {
        $this->assertInstanceOf(Is::getFactory(), Is::factory());
    }

    /**
     * Есть ли метод, генерирующий модели с помощью фабрики?
     *
     * @return void
     */
    public function test_generate(): void
    {
        // Создаем модель со случайными данными.
        $this->assertModelExists(Is::generate());

        // Создаем модель с указанными аттрибутами.
        $role = Is::generate(['slug' => 'admin']);
        $this->assertEquals('admin', $role->getSlug());
        $this->assertModelExists($role);

        // Создаем сразу несколько моделей.
        $roles = Is::generate(3);
        $this->assertCount(3, $roles);
        $this->assertModelExists($roles->first());

        // Создаем модель, но не сохраняем ее в таблицу.
        $this->assertModelMissing(Is::generate(false));

        // Создаем сразу несколько моделей с указанными аттрибутами.
        $roles = Is::generate(['description' => 'Role'], 3);
        $this->assertCount(3, $roles);
        $this->assertTrue($roles->every(fn ($item) => $item->description === 'Role' && $item->exists));

        // Создаем сразу несколько моделей с указанными аттрибутами, но не сохраняем их в таблице.
        $roles = Is::generate(['description' => 'Role'], 3, false);
        $this->assertCount(3, $roles);
        $this->assertTrue($roles->every(fn ($item) => $item->description === 'Role' && ! $item->exists));
    }

    /**
     * Есть ли метод, обновляющий роль?
     *
     * @return void
     */
    public function test_update(): void
    {
        $role = Is::generate(['slug' => 'user']);
        Is::update($role, ['slug' => 'admin']);
        $this->assertEquals('admin', $role->getSlug());

        $role = Is::generate(['slug' => 'moderator']);
        Is::fill($role, ['slug' => 'editor']);
        $this->assertEquals('editor', $role->getSlug());
    }

    /**
     * Есть ли метод, удаляющий модель?
     *
     * @return void
     */
    public function test_delete(): void
    {
        $role = Is::generate();
        $this->assertTrue(Is::delete($role));

        if (Is::usesSoftDeletes()) {
            $this->assertSoftDeleted($role);
        } else {
            $this->assertModelMissing($role);
        }
    }

    /**
     * Есть ли метод, очищающий таблицу?
     *
     * @return void
     */
    public function test_truncate(): void
    {
        Is::generate(3);
        $this->assertCount(3, Is::all());
        Is::truncate();
        $this->assertCount(0, Is::all());
    }

    /**
     * Есть ли метод, удаляющий модель?
     *
     * @return void
     */
    public function test_force_delete(): void
    {
        $role = Is::generate();
        $this->assertModelExists($role);
        Is::forceDelete($role);
        $this->assertModelMissing($role);
    }

    /**
     * Есть ли метод, восстанавливающий модель после программного удаления?
     *
     * @return void
     */
    public function test_restore(): void
    {
        if (! Is::usesSoftDeletes()) {
            $this->markTestSkipped('Программное удаление моделей отключено.');
        }

        $role = Is::generate();
        Is::delete($role);
        $this->assertSoftDeleted($role);
        Is::restore($role);
        $this->assertNotSoftDeleted($role);
    }

    /**
     * Есть ли метод, запускающий сидер ролей?
     *
     * @return void
     */
    public function test_seed(): void
    {
        $this->assertCount(0, Is::all());
        Is::seed();
        $this->assertNotCount(0, Is::all());
    }
}
