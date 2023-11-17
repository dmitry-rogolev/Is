<?php

namespace dmitryrogolev\Is\Tests\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запустить миграцию.
     *
     * @return void
     */
    public function up(): void
    {
        $connection = config('is.connection');
        $table = app(config('is.models.user'))->getTable();
        
        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                if (config('is.uses.uuid')) {
                    $table->uuid(config('is.primary_key'));
                } else {
                    $table->id();
                }

                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();

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
     * Откатить миграцию.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('is.connection'))->dropIfExists(app(config('is.models.user'))->getTable());
    }
};