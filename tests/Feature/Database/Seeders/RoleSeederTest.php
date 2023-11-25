<?php 

namespace dmitryrogolev\Is\Tests\Feature\Database\Seeders;

use dmitryrogolev\Is\Facades\Role;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем сидер роли.
 */
class RoleSeederTest extends TestCase 
{
    use RefreshDatabase;

    /**
     * Есть ли метод, возвращающий роли?
     *
     * @return void
     */
    public function test_get_roles(): void 
    {
        $seeder = app(config('is.seeders.role'));

        $checkFields = fn () => collect($seeder->getRoles())->every(fn ($item) => 
            array_key_exists('name', $item)
            && array_key_exists('slug', $item)
            && array_key_exists('definition', $item) 
            && config('is.uses.levels') ? array_key_exists('level', $item) : true 
        );
        
        config(['is.uses.levels' => false]);
        $this->assertTrue($checkFields());

        config(['is.uses.levels' => true]);
        $this->assertTrue($checkFields());
    }

    /**
     * Создаются ли модели при запуске сидера?
     *
     * @return void
     */
    public function test_run(): void 
    {
        app(config('is.seeders.role'))->run();

        $this->assertNotCount(0, Role::all());
    }
}
