<?php

namespace dmitryrogolev\Is\Tests\Database\Factories;

use dmitryrogolev\Is\Tests\Models\User;
use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

class UserFactory extends TestbenchUserFactory
{
    protected $model = User::class;
}
