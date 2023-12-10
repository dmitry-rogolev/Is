<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
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
        $this->connection = config('is.connection');
        $this->table = app(config('is.models.user'))->getTable();
    }

    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        $exists = $this->schema()->hasTable($this->table);

        if (! $exists) {
            $this->schema()->create($this->table, function (Blueprint $table) {
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
        $this->schema()->dropIfExists($this->table);
    }

    private function schema(): Builder
    {
        return Schema::connection($this->connection);
    }
};
