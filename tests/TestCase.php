<?php

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Providers\IsServiceProvider;
use dmitryrogolev\Slug\Providers\SlugServiceProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Получить поставщиков пакета.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            SlugServiceProvider::class,
            IsServiceProvider::class,
        ];
    }

    /**
     * Определите настройку маршрутов.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->middleware('web')->group(__DIR__ . '/routes/web.php');
    }

    /**
     * Возвращает пользователя, который относится к множеству ролей.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getUserWithRoles(int $count = 3): Model
    {
        $roles = $this->getRole($count);
        $user  = $this->getUser();
        $roles->each(fn ($item) => $user->roles()->attach($item));

        return $user;
    }

    /**
     * Возвращает случайно сгенерированного пользователя.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    protected function getUser(int $count = 1): Model|Collection
    {
        $factory = Is::userModel()::factory();

        return $count > 1 ? $factory->count($count)->create() : $factory->create();
    }

    /**
     * Возвращает случайно сгенерированную роль.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    protected function getRole(int $count = 1): Model|Collection
    {
        $factory = Is::factory();

        return $count > 1 ? $factory->count($count)->create() : $factory->create();
    }
}
