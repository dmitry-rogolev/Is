<?php

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Providers\IsServiceProvider;

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
}
