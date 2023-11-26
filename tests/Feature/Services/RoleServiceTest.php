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
class RoleServiceTest extends TestCase
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
     * Есть ли метод, возвращающий роли модели?
     *
     * @return void
     */
    public function test_index(): void 
    {
        $this->service->generate(5);
        $user = $this->getUserWithRoles(3);
        $this->assertCount($user->getRoles()->count(), $this->service->index($user));
        $this->assertCount($user->getRoles()->count(), $this->service->all($user));
    }

    /**
     * Есть ли метод, возвращающий роль модели?
     *
     * @return void
     */
    public function test_show(): void 
    {
        if (config('is.uses.levels')) {
            $this->service->generate(['level' => 1], 5);
            $user = $this->getUser();
            $user->attachRole($this->service->generate(['level' => 2]));
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first(), $user)));
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first()->getKey(), $user)));
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first()->getSlug(), $user)));
            $this->assertNull($this->service->show($this->service->generate(['level' => 3]), $user));
        } else {
            $this->service->generate(5);
            $user = $this->getUserWithRoles();
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first(), $user)));
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first()->getKey(), $user)));
            $this->assertTrue($user->hasOneRole($this->service->show($user->getRoles()->first()->getSlug(), $user)));
            $this->assertNull($this->service->show($this->service->generate(), $user));
        }
    }

    /**
     * Есть ли метод, проверяющий наличие роли у модели?
     *
     * @return void
     */
    public function test_has(): void 
    {
        if (config('is.uses.levels')) {
            $level1 = $this->service->generate(['level' => 1]);
            $level2 = $this->service->generate(['level' => 2]);
            $level3 = $this->service->generate(['level' => 3]);
            $user = $this->getUser();
            $user->attachRole($level2);
            $this->assertTrue($this->service->has($level1, $user));
            $this->assertTrue($this->service->has($level2, $user));
            $this->assertFalse($this->service->has($level3, $user));
        } else {
            $user = $this->getUserWithRoles();
            $this->assertTrue($this->service->has($user->getRoles()->first(), $user));
            $this->assertFalse($this->service->has($this->service->generate(), $user));
        }
    }

    /**
     * Есть ли метод, создающий модель только если ее не существует в таблице?
     *
     * @return void
     */
    public function test_store_if_not_exists(): void 
    {
        $this->assertNotNull($this->service->storeIfNotExists([
            'name' => 'User', 
            'slug' => 'user', 
        ]));
        $this->assertNull($this->service->storeIfNotExists([
            'name' => 'User', 
            'slug' => 'user', 
        ]));
        $this->assertNotNull($this->service->createIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
        $this->assertNull($this->service->createIfNotExists([
            'name' => 'Admin', 
            'slug' => 'admin', 
        ]));
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
