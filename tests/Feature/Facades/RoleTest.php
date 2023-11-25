<?php 

namespace dmitryrogolev\Is\Tests\Feature\Facades;

use dmitryrogolev\Is\Facades\Role;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем фасад работы с таблицей ролей.
 */
class RoleTest extends TestCase 
{
    use RefreshDatabase;

    /**
     * Работает ли фасад?
     *
     * @return void
     */
    public function test_working(): void 
    {
        $this->assertModelExists(Role::generate());
    }
}
