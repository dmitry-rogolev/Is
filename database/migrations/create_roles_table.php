<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица ролей
 */
return new class extends Migration
{
    /**
     * Запустить миграцию
     */
    public function up(): void
    {
        $table      = config('is.tables.roles');
        $connection = config('is.connection');

        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                if (config('is.uses.uuid')) {
                    $table->uuid(config('is.primary_key'));
                } else {
                    $table->id(config('is.primary_key'));
                }

                $table->string('name', 255)->unique();
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->tinyInteger('level')->default(0);

                if (config('is.uses.timestamps')) {
                    $table->timestamps();
                }

                if (config('is.uses.soft_deletes')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::connection(config('is.connection'))->dropIfExists(config('is.tables.roles'));
    }
};
