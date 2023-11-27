<?php

use dmitryrogolev\Is\Facades\Is;
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
        $table      = Is::roleablesTable();
        $connection = Is::connection();

        if (! Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {

                $table->foreignIdFor(Is::roleModel());

                Is::usesUuid() ? $table->uuidMorphs(Is::relationName()) : $table->morphs(Is::relationName());

                if (Is::usesTimestamps()) {
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
        Schema::connection(Is::connection())->dropIfExists(Is::roleablesTable());
    }
};
