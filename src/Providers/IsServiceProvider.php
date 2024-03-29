<?php

namespace dmitryrogolev\Is\Providers;

use dmitryrogolev\Is\Console\Commands\InstallCommand;
use dmitryrogolev\Is\Http\Middlewares\VerifyLevel;
use dmitryrogolev\Is\Http\Middlewares\VerifyRole;
use Illuminate\Support\ServiceProvider;

/**
 * Поставщик функционала ролей для моделей.
 */
class IsServiceProvider extends ServiceProvider
{
    /**
     * Имя тега пакета.
     */
    private string $packageTag = 'is';

    /**
     * Регистрация любых служб пакета.
     */
    public function register(): void
    {
        $this->mergeConfig();
    }

    /**
     * Загрузка любых служб пакета.
     */
    public function boot(): void
    {
        $this->loadMigrations();
        $this->loadRoutes();
        $this->publishFiles();
        $this->registerCommands();

        if (config('is.uses.middlewares')) {
            $this->app['router']->aliasMiddleware('is', VerifyRole::class);
            $this->app['router']->aliasMiddleware('role', VerifyRole::class);
            $this->app['router']->aliasMiddleware('level', VerifyLevel::class);
        }
    }

    /**
     * Объединяем конфигурацию пакета с конфигурацией приложения.
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/is.php', 'is');
    }

    /**
     * Регистрируем миграции пакета.
     */
    private function loadMigrations(): void
    {
        if (config('is.uses.migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    public function loadRoutes(): void
    {
        if (config('is.uses.api')) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }
    }

    /**
     * Публикуем файлы пакета.
     */
    private function publishFiles(): void
    {
        $this->publishes([
            __DIR__.'/../../config/is.php' => config_path('is.php'),
        ], $this->packageTag.'-config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], $this->packageTag.'-migrations');

        $this->publishes([
            __DIR__.'/../../database/seeders/publish' => database_path('seeders'),
        ], $this->packageTag.'-seeders');

        $this->publishes([
            __DIR__.'/../../config/is.php' => config_path('is.php'),
            __DIR__.'/../../database/migrations' => database_path('migrations'),
            __DIR__.'/../../database/seeders/publish' => database_path('seeders'),
        ], $this->packageTag);
    }

    /**
     * Регистрируем сидеры.
     */
    private function loadSeedsFrom(): void
    {
        if (config('is.uses.seeders')) {
            $this->app->afterResolving('seed.handler', function ($handler) {
                $handler->register(config('is.seeders.role'));
            });
        }
    }

    /**
     * Регистрируем директивы Blade.
     */
    private function registerBladeExtensions(): void
    {
        if (config('is.uses.blade')) {
            $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

            $blade->directive('is', function ($expression) {
                return "<?php if (Auth::check() && Auth::user()->hasRole({$expression})): ?>";
            });
            $blade->directive('endis', function () {
                return '<?php endif; ?>';
            });

            $blade->directive('role', function ($expression) {
                return "<?php if (Auth::check() && Auth::user()->hasRole({$expression})): ?>";
            });
            $blade->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $blade->directive('level', function ($expression) {
                $level = trim($expression, '()');

                return "<?php if (Auth::check() && Auth::user()->level() >= {$level}): ?>";
            });
            $blade->directive('endlevel', function () {
                return '<?php endif; ?>';
            });
        }
    }

    /**
     * Регистрируем команды.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
