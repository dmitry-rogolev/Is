<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица ролей.
 */
return new class extends Migration
{
    /**
     * Имя таблицы.
     */
    protected string $table;

    public function __construct()
    {
        $this->table = config('is.tables.roles');
    }

    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        $exists = Schema::hasTable($this->table);

        if (! $exists) {
            Schema::create($this->table, function (Blueprint $table) {
                // Первичный ключ.
                if (config('is.uses.uuid')) {
                    $table->uuid(config('is.primary_key'));
                } else {
                    $table->id(config('is.primary_key'));
                }

                // Название роли.
                $table->string('name', 255);

                // Человеко-понятный идентификатор роли.
                $slugName = app(config('is.models.role'))->getSlugName();
                $table->string($slugName, 255)->unique();

                // Описание роли.
                $table->text('description')->nullable();

                // Уровень доступа роли.
                $table->tinyInteger('level')->default(0);

                // Временные метки.
                if (config('is.uses.timestamps')) {
                    $table->timestamps();
                }

                // Временная метка программного удаления.
                if (config('is.uses.soft_deletes')) {
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
        Schema::dropIfExists($this->table);
    }
};
