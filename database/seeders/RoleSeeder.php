<?php

namespace dmitryrogolev\Is\Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        if (config('is.uses.levels')) {
            $roles = [
                [
                    'name'        => 'Admin',
                    'slug'        => 'admin',
                    'description' => 'Admin Role',
                    'level'       => 5,
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
        } else {
            $roles = [
                [
                    'name'        => 'Admin',
                    'slug'        => 'admin',
                    'description' => 'Admin Role',
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

        foreach ($roles as $role) {
            if (! config('is.models.role')::whereSlug($role['slug'])->first()) {
                config('is.models.role')::create($role);
            }
        }
    }
}
