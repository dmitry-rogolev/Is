<?php

namespace dmitryrogolev\Is\Tests\Database\Factories;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

/**
 * Фабрика модели пользователя.
 */
class UserFactory extends TestbenchUserFactory
{
    /**
     * Имя модели.
     *
     * @var string
     */
    protected $model = \dmitryrogolev\Is\Tests\Models\User::class;
}