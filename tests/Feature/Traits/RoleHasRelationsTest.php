<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем функционал, добавляющий модели роли отношения с другими моделями.
 */
class RoleHasRelationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Относится ли роль к множеству моделей?
     *
     * @return void
     */
    public function test_roleables(): void
    {
        $role  = Is::generate();
        $users = Is::userModel()::factory()->count(3)->create();
        $users->each(fn ($item) => $item->roles()->attach($role));
        $this->assertEquals($users->pluck(Is::primaryKey()), $role->roleables(Is::userModel())->get()->pluck(Is::primaryKey()));
    }

    /**
     * Есть ли временные метки у загруженных отношений?
     *
     * @return void
     */
    public function test_roleables_with_timestamps(): void
    {
        $role = Is::generate();
        Is::userModel()::factory()->create()->roles()->attach($role);
        $createdAtColumn = app(Is::roleableModel())->getCreatedAtColumn();
        $updatedAtColumn = app(Is::roleableModel())->getUpdatedAtColumn();
        $user            = fn () => $role->roleables(Is::userModel())->first();
        $checkTimestamps = fn () => $user()->pivot->{$createdAtColumn} && $user()->pivot->{$updatedAtColumn};

        // Включаем временные метки моделей.
        Is::usesTimestamps(true);
        $this->assertTrue($checkTimestamps());

        // Отключаем временные метки моделей.
        Is::usesTimestamps(false);
        $this->assertFalse($checkTimestamps());
    }
}
