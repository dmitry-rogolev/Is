<?php

namespace dmitryrogolev\Is\Tests\Database\Migrations;

use dmitryrogolev\Is\Facades\Is;
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
        $connection = Is::connection();
        $table      = app(Is::userModel())->getTable();

        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {

                Is::usesUuid() ? $table->uuid(Is::primaryKey()) : $table->id(Is::primaryKey());

                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();

                if (Is::usesTimestamps()) {
                    $table->timestamps();
                }

                if (Is::usesSoftDeletes()) {
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
        Schema::connection(Is::connection())->dropIfExists(app(Is::userModel())->getTable());
    }
};