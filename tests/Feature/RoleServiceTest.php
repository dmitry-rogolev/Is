<?php 

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Services\RoleService;
use dmitryrogolev\Is\Tests\TestCase;

class RoleServiceTest extends TestCase
{
    protected RoleService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleService();
    }

    /**
     * Проверяем получение всех ролей
     *
     * @return void
     */
    public function test_index(): void 
    {
        $this->assertCount(config('is.models.role')::all()->count(), $this->service->index());
    }

    /**
     * Проверяем получение роли
     *
     * @return void
     */
    public function test_show(): void 
    {
        $this->assertTrue(config('is.models.role')::admin()->is($this->service->show('admin')));
    }

    /**
     * Проверяем возможность создания роли
     * 
     * @return void
     */
    public function test_store(): void 
    {
        $role = $this->service->store([
            'name' => 'Role', 
            'slug' => 'role', 
            'level' => 1, 
        ]);
        $this->assertModelExists($role);

        $role = $this->service->store();
        $this->assertModelExists($role);
    }

    /**
     * Проверяем возможность обновления роли
     *
     * @return void
     */
    public function test_update(): void 
    {
        $role = $this->service->store([
            'name' => 'Role', 
            'slug' => 'role', 
            'level' => 1, 
        ]);
        $oldLevel = $role->level;
        $this->service->update($role, [
            'level' => 3, 
        ]);
        $this->assertNotEquals($oldLevel, $role->level);
    }

    /**
     * Проверяем возможность удаления
     *
     * @return void
     */
    public function test_delete(): void 
    {
        $role = $this->service->store();
        $role->delete();
        if (config('is.uses.soft_deletes')) {
            $this->assertModelExists($role);
            $this->assertTrue($role->trashed());
        } else {
            $this->assertModelMissing($role);
        }
    }

    /**
     * Проверяем возможность удаления
     *
     * @return void
     */
    public function test_force_delete(): void 
    {
        $role = $this->service->store();
        $role->forceDelete();
        $this->assertModelMissing($role);
    }

    /**
     * Проверяем возможность восстановления
     *
     * @return void
     */
    public function test_restore(): void 
    {
        if (! config('is.uses.soft_deletes')) {
            $this->markTestSkipped('Программное удаление отключено.');
        }

        $role = $this->service->store();
        $role->delete();
        $this->assertTrue($role->trashed());
        $role->restore();
        $this->assertFalse($role->trashed());
    }

    /**
     * Проверяем возможность очищения таблицы.
     *
     * @return void
     */
    public function test_trancate(): void 
    {
        config('is.models.role')::factory()->count(10)->create();
        $this->service->truncate();
        $this->assertCount(0, $this->service->index());
    }
}
