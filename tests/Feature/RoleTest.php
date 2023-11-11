<?php

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Tests\TestCase;

class RoleTest extends TestCase
{
    private string $role = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = config('is.models.role');
    }

    /**
     * Проверяем, что имя таблицы ролей совпадает с именем таблицы из конфига.
     *
     * @return void
     */
    public function test_table(): void 
    {
        $this->assertEquals(config('is.tables.roles'), (new ($this->role))->getTable());
    }

    /**
     * Проверяем, что имя первичного ключа роли совпадает с первичным ключом из конфига.
     *
     * @return void
     */
    public function test_primary_key(): void 
    {
        $this->assertEquals(config('is.primary_key'), (new ($this->role))->getKeyName());
    }

    /**
     * Проверяем статус временных меток
     *
     * @return void
     */
    public function test_timestamps(): void 
    {
        $this->assertEquals(config('is.uses.timestamps'), (new ($this->role))->usesTimestamps());
    }

    /**
     * Получаем роль по ее slug
     *
     * @return void
     */
    public function test_get_a_role_by_slug(): void
    {
        $this->assertModelExists($this->role::user());
        $this->assertModelExists($this->role::admin());
        $this->assertModelExists($this->role::moderator());

        $this->expectException(\BadMethodCallException::class);
        $this->role::undefined();
    }

    /**
     * Проверяем наличие фабрики
     *
     * @return void
     */
    public function test_factory(): void 
    {
        $this->assertEquals(config('is.factories.role'), $this->role::factory()::class);
        $this->assertModelExists($this->role::factory()->create());
    }

    /**
     * Проверяем полиморфную связь многие-ко-многим
     *
     * @return void
     */
    public function test_roleables(): void 
    {
        $moderator = $this->role::moderator();
        
        $this->assertTrue($moderator->roleables(config('is.models.user'))->get()->isNotEmpty());
        $this->assertTrue($moderator->users->isNotEmpty());
    }
}
