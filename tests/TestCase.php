<?php

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Providers\IsServiceProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Количество выполненных запросов к БД.
     */
    protected int $queryExecutedCount = 0;

    public function setUp(): void
    {
        parent::setUp();

        $this->registerListeners();
    }

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
        $router->middleware('web')->group(__DIR__.'/routes/web.php');
    }

    /**
     * Возвращает сгенерированную с помощью фабрики модель.
     */
    protected function generate(string $class, array|int|bool|null $count = null, array|bool $state = [], bool $create = true): Model|Collection
    {
        if (is_bool($state)) {
            $create = $state;
            $state = [];
        }

        if (is_array($count)) {
            $state = $count;
            $count = null;
        }

        if (is_bool($count)) {
            $create = $count;
            $count = null;
        }

        $factory = $class::factory($count, $state);

        return $create ? $factory->create() : $factory->make();
    }

    /**
     * Зарегистрировать слушатели событий.
     */
    protected function registerListeners(): void
    {
        DB::listen(fn () => $this->queryExecutedCount++);
    }

    /**
     * Сбросить количество выполненных запросов к БД.
     */
    protected function resetQueryExecutedCount(): void
    {
        $this->queryExecutedCount = 0;
    }

    /**
     * Подтвердить количество выполненных запросов к БД.
     */
    protected function assertQueryExecutedCount(int $expectedCount, ?string $message = ''): void
    {
        $this->assertEquals($expectedCount, $this->queryExecutedCount, $message);
    }

    /**
     * Возвращает построитель SQL запросов к БД.
     */
    protected function schema(): Builder
    {
        return Schema::connection(config('is.connection'));
    }
}
