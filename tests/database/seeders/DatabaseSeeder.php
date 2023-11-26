<?php

namespace dmitryrogolev\Is\Tests\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        $this->call([
                // Сначала добавляем роли.
            \dmitryrogolev\Is\Database\Seeders\RoleSeeder::class,

                // Потом создаем пользователей и связываем их с ролями.
            \dmitryrogolev\Is\Tests\Database\Seeders\UserSeeder::class,
        ]);
    }
}
