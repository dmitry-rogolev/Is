<?php

namespace dmitryrogolev\Is\Database\Seeders;

use dmitryrogolev\Is\Facades\Role;
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
        foreach ($this->getRoles() as $role) {
            if (! Role::has($role['slug'])) {
                Role::create($role);
            }
        }
    }

    /**
     * Возвращает роли.
     *
     * @return array
     */
    public function getRoles(): array 
    {
        return config('is.uses.levels') ? static::ROLES_WITH_LEVELS : static::ROLES;
    }

    /**
     * Роли с полями уровней.
     */
    const ROLES_WITH_LEVELS = [
        [
            'name'        => 'Admin',
            'slug'        => 'admin',
            'description' => 'Admin Role',
            'level'       => 5,
        ],
        [
            'name'        => 'Editor',
            'slug'        => 'editor',
            'description' => 'Editor Role',
            'level'       => 3,
        ],
        [
            'name'        => 'Moderator',
            'slug'        => 'moderator',
            'description' => 'Moderator Role',
            'level'       => 2,
        ],
        [
            'name'        => 'User',
            'slug'        => 'user',
            'description' => 'User Role',
            'level'       => 1,
        ],
    ];

    /**
     * Роли без полей уровней.
     */
    const ROLES = [
        [
            'name'        => 'Admin',
            'slug'        => 'admin',
            'description' => 'Admin Role',
        ],
        [
            'name'        => 'Editor',
            'slug'        => 'editor',
            'description' => 'Editor Role',
        ],
        [
            'name'        => 'Moderator',
            'slug'        => 'moderator',
            'description' => 'Moderator Role',
        ],
        [
            'name'        => 'User',
            'slug'        => 'user',
            'description' => 'User Role',
        ],
    ];
}
