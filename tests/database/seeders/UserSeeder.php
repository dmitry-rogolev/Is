<?php

namespace dmitryrogolev\Is\Tests\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Сидер модели пользователей.
 */
class UserSeeder extends Seeder
{
    protected $count_users = 20;

    /**
     * Запустить сидер
     */
    public function run(): void
    {
        $roles = config('is.models.role')::where('slug', '!=', 'admin')->get();
        $admin = config('is.models.role')::where('slug', '=', 'admin')->first();

        // Администратор будет только один.
        $user = config('is.models.user')::factory()->create();
        $user->attachRole($admin);

        // Пользователи со случайными ролями.
        for ($i = 0; $i < $this->count_users; $i++) {
            $user = config('is.models.user')::factory()->create();
            $role = $roles->random();

            if (config('is.uses.levels')) {
                $user->attachRole($role);
            } else {
                $user->attachRole($roles->where('level', '<=', $role->level));
            }
        }
    }
}
