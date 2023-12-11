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
     * Имя таблицы.
     */
    protected string $table;

    public function __construct()
    {
        $this->table = config('is.tables.roleables');
    }

    /**
     * Запустить миграцию
     */
    public function up(): void
    {
        $exists = Schema::hasTable($this->table);

        if (! $exists) {
            Schema::create($this->table, function (Blueprint $table) {
                // Внешний ключ роли.
                $table->foreignIdFor(config('is.models.role'));

                // Внешний ключ модели, связанной с ролями.
                $table->morphs(config('is.relations.roleable'));

                // Временные метки.
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
        Schema::dropIfExists($this->table);
    }
};
