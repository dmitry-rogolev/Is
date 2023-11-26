<?php

namespace dmitryrogolev\Is\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Фабрика роли.
 */
class RoleFactory extends Factory
{
    /**
     * Создаем фабрику и указываем имя модели.
     *
     * @param mixed ...$parameters
     */
    public function __construct(...$parameters) 
    {
        parent::__construct(...$parameters);
        $this->model = config('is.models.role');
    }
    
    /**
     * Устанавливает состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'name' => $name, 
            'slug' => $name, 
            'description' => $name.' role', 
            'level' => config('is.uses.levels') ? fake()->randomNumber(1) : 0,
        ];
    }
}