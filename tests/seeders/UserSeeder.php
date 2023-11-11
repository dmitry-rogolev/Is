<?php

namespace dmitryrogolev\Is\Tests\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        $adminRole = config('is.models.role')::admin();
        $moderatorRole = config('is.models.role')::moderator();
        $userRole = config('is.models.role')::user();

        $admin = config('is.models.user')::factory()->create();
        $admin->roles()->attach($adminRole);
        $admin->roles()->attach($moderatorRole);
        $admin->roles()->attach($userRole);

        for ($i = 0; $i < 3; $i++) {
            $moderator = config('is.models.user')::factory()->create();
            $moderator->roles()->attach($moderatorRole);
            $moderator->roles()->attach($userRole);
        }

        for ($i = 0; $i < 10; $i++) {
            $user = config('is.models.user')::factory()->create();
            $user->roles()->attach($userRole); 
        }
    }
}
