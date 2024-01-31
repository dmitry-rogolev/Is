<?php

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Providers\IsServiceProvider;
use dmitryrogolev\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use function Orchestra\Testbench\workbench_path;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use InteractsWithDatabase;

    /**
     * Количество выполненных запросов к БД.
     */
    protected int $queryExecutedCount = 0;

    /**
     * SQL-запросы, отправленные на выполнение.
     *
     * @var array<int, string>
     */
    protected array $queries;

    public function setUp(): void
    {
        parent::setUp();

        $this->registerQueryListener();
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
        $router->middleware('web')->group(workbench_path('routes/web.php'));
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
}
