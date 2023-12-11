<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица пользователей.
 */
return new class extends Migration
{
    /**
     * Имя таблицы.
     */
    protected string $table;

    public function __construct()
    {
        $this->table = app(config('is.models.user'))->getTable();
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

                // Имя пользователя.
                $table->string('name');

                // Электронная почта.
                $table->string('email')->unique();

                // Время подтверждения электронной почты.
                $table->timestamp('email_verified_at')->nullable();

                // Пароль.
                $table->string('password');

                // Токен входа пользователя.
                $table->rememberToken();

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
