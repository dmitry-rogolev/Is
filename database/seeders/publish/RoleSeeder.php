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
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin Role', 'level' => 5],
            ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Editor Role', 'level' => 3],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Moderator Role', 'level' => 2],
            ['name' => 'User', 'slug' => 'user', 'description' => 'User Role', 'level' => 1],
        ];
    }
}
