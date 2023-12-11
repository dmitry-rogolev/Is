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
     */
    public function __construct(mixed ...$parameters)
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
        $name = fake()->unique()->word();

        $definition = [
            'name' => ucfirst($name),
            'description' => ucfirst($name).' role',
            'level' => fake()->numberBetween(1, 9),
        ];

        $slugName = app($this->model)->getSlugName();
        $definition[$slugName] = $this->model::toSlug($name);

        return $definition;
    }
}
