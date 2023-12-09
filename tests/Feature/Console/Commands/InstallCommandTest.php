<?php

namespace dmitryrogolev\Is\Tests\Feature\Console\Commands;

use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем команду установки пакета "Is".
 */
class InstallCommandTest extends TestCase
{
    /**
     * Запускается ли команда?
     */
    public function test_run(): void
    {
        $this->artisan('is:install')->assertOk();
        $this->artisan('is:install --config')->assertOk();
        $this->artisan('is:install --migrations')->assertOk();
        $this->artisan('is:install --seeders')->assertOk();
    }
}
