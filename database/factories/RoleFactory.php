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

        $definition = [
            'name' => $name, 
            'slug' => $name, 
            'description' => $name.' role', 
        ];

        // Если иерархия ролей отключена, то поля "level" в таблице не будет.
        if (config('is.uses.levels')) {
            $definition['level'] = fake()->randomNumber(1);
        }

        return $definition;
    }
}