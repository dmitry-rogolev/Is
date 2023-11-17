<?php

namespace dmitryrogolev\Is\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Сидер модели ролей.
 */
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
                    'name'        => 'Seller',
                    'slug'        => 'seller',
                    'description' => 'Seller Role',
                    'level'       => 1,
                ],
                [
                    'name'        => 'Customer',
                    'slug'        => 'customer',
                    'description' => 'Customer Role',
                    'level'       => 1,
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
                    'name'        => 'Seller',
                    'slug'        => 'seller',
                    'description' => 'Seller Role',
                ],
                [
                    'name'        => 'Customer',
                    'slug'        => 'customer',
                    'description' => 'Customer Role',
                ],
                [
                    'name'        => 'User',
                    'slug'        => 'user',
                    'description' => 'User Role',
                ],
            ];
        }

        foreach ($roles as $role) {
            if (! config('is.models.role')::where('slug', $role['slug'])->count()) {
                config('is.models.role')::create($role);
            }
        }
    }
}
