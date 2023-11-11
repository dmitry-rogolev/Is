<?php

namespace dmitryrogolev\Is\Tests\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        $this->call([
            \dmitryrogolev\Is\Database\Seeders\RoleSeeder::class, 
            \dmitryrogolev\Is\Tests\Seeders\UserSeeder::class, 
        ]);
    }
}
