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

    /**
     * Имя первичного ключа.
     */
    protected string $keyName;

    /**
     * Имя slug'а.
     */
    protected string $slugName;

    public function __construct()
    {
        $this->table = config('is.tables.roles');
        $this->keyName = config('is.primary_key');
        $this->slugName = app(config('is.models.role'))->getSlugName();
    }

    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        $exists = Schema::hasTable($this->table);

        if (! $exists) {
            Schema::create($this->table, function (Blueprint $table) {
                config('is.uses.uuid') ? $table->uuid($this->keyName) : $table->id($this->keyName);
                $table->string('name', 255);
                $table->string($this->slugName, 255)->unique();
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
     * Откатить миграцию.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
