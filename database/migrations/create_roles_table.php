<?php

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица ролей
 */
return new class extends Migration
{
    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        $table      = Is::rolesTable();
        $connection = Is::connection();

        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {

                Is::usesUuid() ? $table->uuid(Is::primaryKey()) : $table->id(Is::primaryKey());

                $table->string('name', 255)->unique();
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->tinyInteger('level')->default(0);

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
     */
    public function down(): void
    {
        Schema::connection(Is::connection())->dropIfExists(Is::rolesTable());
    }
};
