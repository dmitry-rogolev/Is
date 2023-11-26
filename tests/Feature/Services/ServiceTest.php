<?php

namespace dmitryrogolev\Is\Tests\Feature\Services;

use dmitryrogolev\Is\Services\RoleService;
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

    public RoleService $service;

    public function setUp(): void 
    {
        parent::setUp();
        $this->service = app(RoleService::class);
        config(['is.uses.load_on_update' => true]);
    }

    /**
     * Есть ли метод, возвращающий имя модели сервиса?
     *
     * @return void
     */
    public function test_get_model(): void 
    {
        $this->assertEquals(config('is.models.role'), $this->service->getModel());
    }

    /**
     * Есть ли метод, возвращающий имя сидера модели?
     *
     * @return void
     */
    public function test_get_seeder(): void 
    {
        $this->assertEquals(config('is.seeders.role'), $this->service->getSeeder());
    }

    /**
     * Есть ли метод, возвращающий имя фабрики модели?
     *
     * @return void
     */
    public function test_get_factory(): void 
    {
        $this->assertEquals(config('is.factories.role'), $this->service->getFactory());
    }

    /**
     * Есть ли метод, возвращающий все записи таблицы?
     *
     * @return void
     */
    public function test_index(): void 
    {
        $count = random_int(1, 10);
        $this->getRole($count);
        $this->assertCount($count, $this->service->index());
        $this->assertCount($count, $this->service->all());
    }

    /**
     * Есть ли метод, возвращающий случайную модели из таблицы?
     *
     * @return void
     */
    public function test_random(): void 
    {
        $this->getRole(10);
        $this->assertModelExists($this->service->random());
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору?
     *
     * @return void
     */
    public function test_show(): void 
    {
        $role = $this->getRole();
        $this->assertEquals($role->getKey(), $this->service->show($role->getKey())->getKey());
        $this->assertEquals($role->getKey(), $this->service->find($role->getKey())->getKey());
        $this->assertEquals($role->getKey(), $this->service->find($role)->getKey());
        $this->assertNull($this->service->find($this->getUser()));
    }

    /**
     * Есть ли метод, проверяющий наличие модели в таблице?
     *
     * @return void
     */
    public function test_has(): void 
    {
        $role = $this->getRole();
        $this->assertTrue($this->service->has($role));
        $this->assertTrue($this->service->has($role->getKey()));
        $this->assertFalse($this->service->has('my_key'));
        $this->assertFalse($this->service->has(config('is.models.role')::factory()->make()));
    }

    /**
     * Есть ли метод, создающий модель?
     *
     * @return void
     */
    public function test_make(): void 
    {
        $model = $this->service->make([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]);
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
        $this->assertNotNull($this->service->makeIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
        config('is.models.role')::create([
            'name' => 'Admin', 
            'slug' => 'admin',  
        ]);
        $this->assertNull($this->service->makeIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin',  
        ]));
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

        $models = $this->service->makeGroup($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelMissing($item));

        collect($group)->each(fn ($item) => $this->service->getModel()::create($item));

        $models = $this->service->makeGroup($group);
        $this->assertNotCount(0, $models);
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

        $models = $this->service->makeGroupIfNotExists($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelMissing($item));

        collect($group)->each(fn ($item) => $this->service->getModel()::create($item));

        $models = $this->service->makeGroupIfNotExists($group);
        $this->assertCount(0, $models);
    }

    /**
     * Есть ли метод, создающий модель и сохраняющий ее в таблице?
     *
     * @return void
     */
    public function test_store(): void 
    {
        $this->assertModelExists($this->service->store([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
        $this->assertModelExists($this->service->create([
            'name' => 'User', 
            'slug' => 'user', 
        ]));
    }

    /**
     * Есть ли метод, создающий модель и сохраняющий ее в таблице, 
     * только если она не существует в таблице?
     *
     * @return void
     */
    public function test_store_if_not_exists(): void 
    {
        $this->assertModelExists($this->service->storeIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
        $this->assertNull($this->service->storeIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
        $this->assertModelExists($this->service->createIfNotExists([
            'name' => 'User', 
            'slug' => 'user', 
        ]));
        $this->assertNull($this->service->createIfNotExists([
            'name' => 'User', 
            'slug' => 'user', 
        ]));
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

        $models = $this->service->storeGroup($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        config('is.models.role')::truncate();

        $models = $this->service->createGroup($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));
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

        $models = $this->service->storeGroupIfNotExists($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        $models = $this->service->storeGroupIfNotExists($group);
        $this->assertCount(0, $models);

        config('is.models.role')::truncate();

        $models = $this->service->createGroupIfNotExists($group);
        $this->assertNotCount(0, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        $models = $this->service->createGroupIfNotExists($group);
        $this->assertCount(0, $models);
    }

    /**
     * Есть ли метод, возвращающий фабрику модели?
     *
     * @return void
     */
    public function test_factory(): void 
    {
        $this->assertEquals($this->service->getFactory(), $this->service->factory()::class);
    }

    /**
     * Есть ли метод, генерирующий модели с помошью фабрики?
     *
     * @return void
     */
    public function test_generate(): void 
    {
        // Создаем модель со случайными данными.
        $this->assertModelExists($this->service->generate());

        // Создаем модель с указанными аттрибутами.
        $role = $this->service->generate(['slug' => 'admin']);
        $this->assertEquals('admin', $role->getSlug());
        $this->assertModelExists($role);

        // Создаем сразу несколько моделей.
        $roles = $this->service->generate(3);
        $this->assertCount(3, $roles);
        $this->assertModelExists($roles->first());

        // Создаем модель, но не сохраняем ее в таблицу.
        $this->assertModelMissing($this->service->generate(false));

        // Создаем сразу несколько моделей с указанными аттрибутами.
        $roles = $this->service->generate(['description' => 'Role'], 3);
        $this->assertCount(3, $roles);
        $this->assertTrue($roles->every(fn ($item) => $item->description === 'Role' && $item->exists));

        // Создаем сразу несколько моделей с указанными аттрибутами, но не сохраняем их в таблице.
        $roles = $this->service->generate(['description' => 'Role'], 3, false);
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
        $role = $this->service->generate(['slug' => 'user']);
        $this->service->update($role, ['slug' => 'admin']);

        $this->assertEquals('admin', $role->getSlug());
    }

    /**
     * Есть ли метод, удаляющий модель?
     *
     * @return void
     */
    public function test_delete(): void 
    {
        $role = $this->service->generate();
        $this->assertTrue($this->service->delete($role));
        
        if (config('is.uses.soft_deletes')) {
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
        $this->service->generate(3);
        $this->assertCount(3, $this->service->all());
        $this->service->truncate();
        $this->assertCount(0, $this->service->all());
    }

    /**
     * Есть ли метод, удаляющий модель?
     *
     * @return void
     */
    public function test_force_delete(): void 
    {
        $role = $this->service->generate();
        $this->assertModelExists($role);
        $this->service->forceDelete($role);
        $this->assertModelMissing($role);
    }

    /**
     * Есть ли метод, востанавливающий модель после программного удаления?
     *
     * @return void
     */
    public function test_restore(): void 
    {
        if (! config('is.uses.soft_deletes')) {
            $this->markTestSkipped('Программное удаление моделей отключено.');
        }

        $role = $this->service->generate();
        $this->service->delete($role);
        $this->assertSoftDeleted($role);
        $this->service->restore($role);
        $this->assertNotSoftDeleted($role);
    }

    /**
     * Есть ли метод, запускающий сидер ролей?
     *
     * @return void
     */
    public function test_seed(): void 
    {
        $this->assertCount(0, $this->service->all());
        $this->service->seed();
        $this->assertNotCount(0, $this->service->all());
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
