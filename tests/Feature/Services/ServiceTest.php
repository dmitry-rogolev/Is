<?php

namespace dmitryrogolev\Is\Tests\Feature\Services;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

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
     * Если ли метод, возвращающий роли модели?
     */
    public function test_get_roles(): void
    {
        $user = $this->generate($this->user);
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $this->generate($this->model, ['level' => 3]);
        $user->roles()->attach($level2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат коллекции.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = Is::getRoles($user);
        $this->assertInstanceOf(Collection::class, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем возврат ролей при отключенной иерархии ролей.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => false]);
        $expected = [$level2->getKey()];
        $actual = Is::getRoles($user)->pluck($this->keyName)->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем возврат ролей при включенной иерархии ролей.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $expected = [$level1->getKey(), $level2->getKey()];
        $actual = Is::getRoles($user)->pluck($this->keyName)->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||      Подтверждаем количество запросов к БД при отключенной иерархии ролей.     ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => false]);
        $this->resetQueryExecutedCount();
        Is::getRoles($user);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем количество запросов к БД при включенной иерархии ролей.      ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $this->resetQueryExecutedCount();
        Is::getRoles($user);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие роли у модели?
     */
    public function test_has_role(): void
    {
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);
        $roles = $this->generate($this->model, 3);
        $roles->each(fn ($role) => $user->roles()->attach($role));
        $this->generate($this->model, 2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $roles->get(0)->getKey();
        $condition = Is::hasRole($user, $role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $roles->get(0)->getKey();
        $condition = Is::hasRole($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $roles->get(1)->getSlug();
        $condition = Is::hasRole($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $roles->get(2);
        $condition = Is::hasRole($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Передаем идентификатор отсутствующей у модели роли.              ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $condition = Is::hasRole($user, $role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем количество запросов к БД.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $roles->get(0)->getKey();
        $this->resetQueryExecutedCount();
        Is::hasRole($user, $role);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, проверяющий наличие доступа модели?
     */
    public function test_has_level(): void
    {
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $level3 = $this->generate($this->model, ['level' => 3]);
        $user->roles()->attach($level2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level1->getKey();
        $condition = Is::hasLevel($user, $role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level1->getKey();
        $condition = Is::hasLevel($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level2->getSlug();
        $condition = Is::hasLevel($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level1;
        $condition = Is::hasLevel($user, $role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Передаем идентификатор отсутствующей у модели роли.              ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level3->getKey();
        $condition = Is::hasLevel($user, $role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем количество запросов к БД при передачи идентификатора.       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level1->getKey();
        $this->resetQueryExecutedCount();
        Is::hasLevel($user, $role);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем количество запросов к БД при передачи модели.           ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $level3;
        $this->resetQueryExecutedCount();
        Is::hasLevel($user, $role);
        $this->assertQueryExecutedCount(0);
    }
}
