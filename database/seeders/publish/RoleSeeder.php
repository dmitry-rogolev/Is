<?php

namespace Database\Seeders;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Seeder;

/**
 * Сидер модели ролей.
 */
class RoleSeeder extends Seeder
{
    /**
     * Запустить сидер.
     */
    public function run(): void
    {
        Is::createGroupIfNotExists($this->getRoles());
    }

    /**
     * Возвращает роли.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin role', 'level' => 5],
            ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Editor role', 'level' => 3],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Moderator role', 'level' => 2],
            ['name' => 'User', 'slug' => 'user', 'description' => 'User role', 'level' => 1],
        ];
    }
}
