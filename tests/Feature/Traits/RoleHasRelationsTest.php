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
     */
    public function test_roleables(): void
    {
        $role = Is::generate();
        $users = config('is.models.user')::factory()->count(3)->create();
        $users->each(fn ($item) => $item->roles()->attach($role));
        $this->assertEquals($users->pluck(config('is.primary_key')), $role->roleables(config('is.models.user'))->get()->pluck(config('is.primary_key')));
    }

    /**
     * Есть ли временные метки у загруженных отношений?
     */
    public function test_roleables_with_timestamps(): void
    {
        $role = Is::generate();
        config('is.models.user')::factory()->create()->roles()->attach($role);
        $createdAtColumn = app(config('is.models.roleable'))->getCreatedAtColumn();
        $updatedAtColumn = app(config('is.models.roleable'))->getUpdatedAtColumn();
        $user = fn () => $role->roleables(config('is.models.user'))->first();
        $checkTimestamps = fn () => $user()->pivot->{$createdAtColumn} && $user()->pivot->{$updatedAtColumn};

        // Включаем временные метки моделей.
        config(['is.uses.timestamps' => true]);
        $this->assertTrue($checkTimestamps());

        // Отключаем временные метки моделей.
        config(['is.uses.timestamps' => false]);
        $this->assertFalse($checkTimestamps());
    }
}
