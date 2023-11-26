<?php

namespace dmitryrogolev\Is\Tests\Database\Factories;

use dmitryrogolev\Is\Facades\Is;
use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

/**
 * Фабрика модели пользователя.
 */
class UserFactory extends TestbenchUserFactory
{
    public function __construct(...$parameters)
    {
        parent::__construct(...$parameters);
        $this->model = Is::userModel();
    }
}