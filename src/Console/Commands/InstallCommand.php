<?php

namespace dmitryrogolev\Is\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'is:install 
                                {--config}
                                {--migrations}
                                {--seeders}';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Installs the "Is" package that provides role functionality for the Laravel framework.';

    /**
     * Выполнить консольную команду.
     *
     * @return mixed
     */
    public function handle()
    {
        $tag = 'is';
        
        if ($this->option('config')) {
            $tag .= '-config';
        } else if ($this->option('migrations')) {
            $tag .= '-migrations';
        } else if ($this->option('seeders')) {
            $tag .= '-seeders';
        } 
        
        $this->call('vendor:publish', [
            '--tag' => $tag, 
        ]);
    }
}