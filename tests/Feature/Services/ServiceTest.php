<?php

namespace dmitryrogolev\Is\Tests\Feature\Services;

use dmitryrogolev\Contracts\Resourcable;
use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Services\RoleService;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем сервис работы с таблицей ролей.
 */
class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Имя первичного ключа.
     */
    protected string $keyName;

    /**
     * Имя модели.
     */
    protected string $model;

    /**
     * Имя модели пользователя.
     */
    protected string $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->keyName = config('is.primary_key');
        $this->model = config('is.models.role');
        $this->user = config('is.models.user');
    }

    /**
     * Настроен ли сервис?
     */
    public function test_configuration(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем установку модели, с которой работает сервис.           ||
        // ! ||--------------------------------------------------------------------------------||

        $expected = $this->model;
        $actual = Is::getModel();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем установку сидера модели.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $expected = config('is.seeders.role');
        $actual = Is::getSeeder();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Подтверждаем реализацию ресурсного интерфейса.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $service = new RoleService;
        $this->assertInstanceOf(Resourcable::class, $service);
    }
}
