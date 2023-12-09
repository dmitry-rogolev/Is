<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Промежуточная таблица полиморфного отношения многие-ко-многим.
 *
 * @link https://clck.ru/36JLPn Полиморфные отношения многие-ко-многим
 */
return new class extends Migration
{
    /**
     * Запустить миграцию
     */
    public function up(): void
    {
        $table = config('is.tables.roleables');
        $connection = config('is.connection');

        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                $table->foreignIdFor(config('is.models.role'));
                $table->morphs(config('is.relations.roleable'));

                if (config('is.uses.timestamps')) {
                    $table->timestamps();
                }

            });
        }
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::connection(config('is.connection'))->dropIfExists(config('is.tables.roleables'));
    }
};
