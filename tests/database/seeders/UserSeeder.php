<?php

namespace dmitryrogolev\Is\Tests\Database\Seeders;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Seeder;

/**
 * Сидер модели пользователей.
 */
class UserSeeder extends Seeder
{
    protected $count_users = 20;

    /**
     * Запустить сидер.
     */
    public function run(): void
    {
        $roles = Is::roleModel()::where('slug', '!=', 'admin')->get();
        $admin = Is::roleModel()::where('slug', '=', 'admin')->first();

        // Администратор будет только один.
        $user = Is::generate();
        $user->attachRole($admin);

        // Пользователи со случайными ролями.
        for ($i = 0; $i < $this->count_users; $i++) {
            $user = Is::generate();
            $role = $roles->random();

            if (Is::usesLevels()) {
                $user->attachRole($role);
            } else {
                $user->attachRole($roles->where('level', '<=', $role->level));
            }
        }
    }
}
