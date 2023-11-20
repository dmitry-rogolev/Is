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
     *
     * @var string
     */
    private string $packageTag = 'is';

    /**
     * Регистрация любых служб пакета.
     * 
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfig();
        $this->loadMigrations();
        $this->publishFiles();
        $this->registerCommands();
    }

    /**
     * Загрузка любых служб пакета.
     * 
     * @return void
     */
    public function boot(): void
    {
        if (config('is.uses.middlewares')) {
            $this->app['router']->aliasMiddleware('is', VerifyRole::class);
            $this->app['router']->aliasMiddleware('role', VerifyRole::class);

            if (config('is.uses.levels')) {
                $this->app['router']->aliasMiddleware('level', VerifyLevel::class);
            }
        }
    }

    /**
     * Объединяем конфигурацию пакета с конфигурацией приложения.
     *
     * @return void
     */
    private function mergeConfig(): void 
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/is.php', 'is');
    }

    /**
     * Регистируем миграции пакета.
     *
     * @return void
     */
    private function loadMigrations(): void 
    {
        if (config('is.uses.migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    /**
     * Публикуем файлы пакета.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
            
            if (config('is.uses.levels')) {
                $blade->directive('level', function ($expression) {
                    $level = trim($expression, '()');
        
                    return "<?php if (Auth::check() && Auth::user()->level() >= {$level}): ?>";
                });
                $blade->directive('endlevel', function () {
                    return '<?php endif; ?>';
                });
            }
        }
    }

    /**
     * Регистрируем комманды.
     *
     * @return void
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
