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
     * Имя slug'а.
     */
    protected string $slugName;

    public function __construct()
    {
        $this->table = config('is.tables.roles');
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
                $table->uuid('id');
                $table->string('name', 255);
                $table->string($this->slugName, 255)->unique();
                $table->text('description')->nullable();
                $table->tinyInteger('level')->default(0);
                $table->timestamps();
                $table->softDeletes();
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
