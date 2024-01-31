<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use App\Models\User;
use BadMethodCallException;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Тестируем функционал ролей.
 */
class HasRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Имя модели роли.
     */
    protected string $model;

    /**
     * Имя модели пользователя.
     */
    protected string $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
        $this->user = User::class;
    }

    /**
     * Есть ли метод, возвращающий отношение модели с ролями?
     */
    public function test_roles(): void
    {
        $user = $this->generate($this->user);
        $roles = $this->generate($this->model, 3);
        $roles->each(fn ($role) => $user->roles()->attach($role));
        $this->generate($this->model, 2);
        $expected = $roles->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат отношения.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $query = $user->roles();
        $this->assertInstanceOf(MorphToMany::class, $query);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем получение ролей модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $actual = $user->roles()->get()->pluck('id')->all();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Если ли метод, возвращающий коллекцию ролей?
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

        $roles = $user->getRoles();
        $this->assertInstanceOf(Collection::class, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем возврат ролей при отключенной иерархии ролей.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => false]);
        $expected = [$level2->getKey()];
        $actual = $user->getRoles()->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем возврат ролей при включенной иерархии ролей.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $expected = [$level1->getKey(), $level2->getKey()];
        $actual = $user->getRoles()->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||      Подтверждаем количество запросов к БД при отключенной иерархии ролей.     ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => false]);
        $this->resetQueryExecutedCount();
        $user->getRoles();
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем количество запросов к БД при включенной иерархии ролей.      ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $this->resetQueryExecutedCount();
        $user->getRoles();
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, подгружающий отношение модели с ролями?
     */
    public function test_load_roles(): void
    {
        $user = $this->generate($this->user);
        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->load('roles');

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Подтверждаем, что отношение не было загружено.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $user->roles()->detach();
        $condition = $user->roles->isNotEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем, что отношение обновлено.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $user->loadRoles();
        $condition = $user->roles->isEmpty();
        $this->assertTrue($condition);
    }

    /**
     * Есть ли метод, присоединяющий роль к модели?
     */
    public function test_attach_role_use_levels_with_one_param(): void
    {
        config(['is.uses.levels' => true]);
        config(['is.uses.load_on_update' => true]);
        $user = $this->generate($this->user);
        $minLevel = 3;
        $level = 2;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $condition = $user->attachRole($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getSlug();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getSlug() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->is($role)));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Повторно передаем присоединенную роль.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $roles = $user->roles->where('id', $role->getKey());
        $this->assertCount(1, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||        Передаем роль с уровнем равным максимальному уровню пользователя.       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level]);
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $roles = $user->roles->where('level', $role->level);
        $this->assertCount(1, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Передаем роль с меньшим уровнем относительно                  ||
        // ! ||                       максимального уровня пользователя.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $minLevel - 1]);
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $roles = $user->roles->where('level', $role->level);
        $this->assertCount(0, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем отсутствующий в таблице slug.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 'my_slug';
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getSlug() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 634569569;
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||           при присоединении отсутствующей роли и при передачи модели.          ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||       при присоединении отсутствующей роли и при передачи идентификатора.      ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при присоединении существующей у пользователя роли               ||
        // ! ||                             и при передачи модели.                             ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level]);
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при присоединении существующей у пользователя роли               ||
        // ! ||                         и при передачи идентификатора.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level])->getKey();
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем, что при отключении опции                     ||
        // ! ||               авто обновления отношений, роли не были обновлены.               ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $role));
        $user->loadRoles();
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === $role));
    }

    /**
     * Есть ли метод, присоединяющий множество ролей к модели?
     */
    public function test_attach_role_use_levels_with_many_params(): void
    {
        config(['is.uses.levels' => true]);
        config(['is.uses.load_on_update' => true]);
        $user = $this->generate($this->user);
        $level = 2;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $condition = $user->attachRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $condition = $user->attachRole(...$roles);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === last($roles)));
        $this->assertTrue(
            collect($roles)->slice(0, count($roles) - 1)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = collect([
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ]);
        $condition = $user->attachRole($roles);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === $roles->last()));
        $this->assertTrue(
            $roles->slice(0, $roles->count() - 1)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
        ];
        $condition = $user->attachRole(...$roles);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getSlug() === last($roles)));
        $this->assertTrue(
            collect($roles)->slice(0, count($roles) - 1)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->getSlug() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $condition = $user->attachRole($roles);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->is(last($roles))));
        $this->assertTrue(
            collect($roles)->slice(0, count($roles) - 1)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->is($role)
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем массив уже присоединенных ролей.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->attachRole(...$roles);
        $this->assertFalse($condition);
        $this->assertTrue($user->roles->where('id', last($roles)->getKey())->count() === 1);
        $this->assertTrue(
            collect($roles)->slice(0, count($roles) - 1)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->is($role)
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                       равны максимальному уровню модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
        ];
        $condition = $user->attachRole($roles);
        $this->assertFalse($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => ! $item->is($role)
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                        ниже максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $condition = $user->attachRole($roles);
        $this->assertFalse($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => ! $item->is($role)
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354345, '354546544765', '34342'];
        $condition = $user->attachRole(...$roles);
        $this->assertFalse($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передачи массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передачи массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole($roles);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при повторной передачи ранее присоединенных ролей.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $user->attachRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передачи массива ролей с уровнем                      ||
        // ! ||                        ниже максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||           при передачи отсутствующих в таблице идентификаторов ролей.          ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354345, '354546544765', '34342'];
        $this->resetQueryExecutedCount();
        $condition = $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||              при отключении подгрузки отношений после обновления.              ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(2);
    }

    /**
     * Есть ли метод, присоединяющий роль к модели?
     */
    public function test_attach_role_without_levels_with_one_param(): void
    {
        config(['is.uses.levels' => false]);
        config(['is.uses.load_on_update' => true]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $condition = $user->attachRole($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getSlug();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getSlug() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->contains(fn ($item) => $item->is($role)));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Повторно передаем присоединенную роль.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $roles = $user->roles->where('id', $role->getKey());
        $this->assertCount(1, $roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем отсутствующий в таблице slug.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 'my_slug';
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getSlug() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 634569569;
        $condition = $user->attachRole($role);
        $this->assertFalse($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||       при присоединении отсутствующей роли и при передачи идентификатора.      ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||           при присоединении отсутствующей роли и при передачи модели.          ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при присоединении существующей у пользователя роли               ||
        // ! ||                             и при передачи модели.                             ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $user->attachRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при присоединении существующей у пользователя роли               ||
        // ! ||                         и при передачи идентификатора.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $user->attachRole($role->getKey());
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем, что при отключении опции                     ||
        // ! ||               авто обновления отношений, роли не были обновлены.               ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $role = $this->generate($this->model)->getKey();
        $condition = $user->attachRole($role);
        $this->assertTrue($condition);
        $this->assertFalse($user->roles->contains(fn ($item) => $item->getKey() === $role));
        $user->loadRoles();
        $this->assertTrue($user->roles->contains(fn ($item) => $item->getKey() === $role));
    }

    /**
     * Есть ли метод, присоединяющий множество ролей к модели?
     */
    public function test_attach_role_without_levels_with_many_params(): void
    {
        config(['is.uses.levels' => false]);
        config(['is.uses.load_on_update' => true]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
        ];
        $condition = $user->attachRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
        ];
        $condition = $user->attachRole(...$roles);
        $this->assertTrue($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = collect([
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
        ]);
        $condition = $user->attachRole($roles);
        $this->assertTrue($condition);
        $this->assertTrue(
            $roles->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model)->getSlug(),
            $this->generate($this->model)->getSlug(),
            $this->generate($this->model)->getSlug(),
        ];
        $condition = $user->attachRole(...$roles);
        $this->assertTrue($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => $item->getSlug() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model),
            $this->generate($this->model),
            $this->generate($this->model),
        ];
        $condition = $user->attachRole($roles);
        $this->assertTrue($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->contains(
                    fn ($item) => $item->is($role)
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем массив уже присоединенных ролей.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->attachRole(...$roles);
        $this->assertFalse($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => $user->roles->where('id', $role->getKey())->count() === 1
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354345, '354546544765', '34342'];
        $condition = $user->attachRole(...$roles);
        $this->assertFalse($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => ! $user->roles->contains(
                    fn ($item) => $item->getKey() === $role
                )
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передачи массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(5);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передачи массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model),
            $this->generate($this->model),
            $this->generate($this->model),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole($roles);
        $this->assertQueryExecutedCount(4);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при повторной передачи ранее присоединенных ролей.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $user->attachRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||           при передачи отсутствующих в таблице идентификаторов ролей.          ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354345, '354546544765', '34342'];
        $this->resetQueryExecutedCount();
        $condition = $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||              при отключении подгрузки отношений после обновления.              ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $roles = [
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
            $this->generate($this->model)->getKey(),
        ];
        $this->resetQueryExecutedCount();
        $user->attachRole(...$roles);
        $this->assertQueryExecutedCount(4);
    }

    /**
     * Есть ли метод, отсоединяющий роль?
     */
    public function test_detach_role_with_one_param(): void
    {
        config(['is.uses.levels' => false]);
        config(['is.uses.load_on_update' => true]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->detachRole($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->detachRole($role);
        $this->assertTrue($condition);
        $this->assertFalse(
            $user->roles->contains(fn ($item) => $item->getKey() === $role)
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->detachRole($role->getSlug());
        $this->assertTrue($condition);
        $this->assertFalse(
            $user->roles->contains(fn ($item) => $item->getSlug() === $role->getSlug())
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->detachRole($role);
        $this->assertTrue($condition);
        $this->assertFalse(
            $user->roles->contains(fn ($item) => $item->is($role))
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Повторно передаем отсоединенную модель.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->detachRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем не присоединенную роль.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $condition = $user->detachRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 4564564564;
        $condition = $user->detachRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                               Ничего не передаем.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->detachRole();
        $this->assertTrue($condition);
        $this->assertTrue($user->roles->isEmpty());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Передаем роль, которая фактически не присоединена,               ||
        // ! ||             но присутствует у модели при включении иерархии ролей.             ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $user->roles()->detach();
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $user->roles()->attach($level2);
        $user->loadRoles();
        $condition = $user->detachRole($level1);
        $this->assertFalse($condition);
        config(['is.uses.levels' => false]);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передачи идентификатора.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($role);
        $this->assertQueryExecutedCount(3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                              при передачи модели.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($role);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                     при передачи не присоединенной модели.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $this->resetQueryExecutedCount();
        $user->detachRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||       при отключении автоматической подгрузки отношений после обновления.      ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($role);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, отсоединяющий множество ролей?
     */
    public function test_detach_role_with_many_params(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->roles()->attach($roles);
        $user->loadRoles();
        $condition = $user->detachRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->roles()->attach($roles);
        $user->loadRoles();
        $condition = $user->detachRole(...$roles);
        $this->assertTrue($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => ! $user->roles->contains(fn ($item) => $item->getKey() === $role)
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->roles()->attach($roles);
        $user->loadRoles();
        $condition = $user->detachRole(collect($roles));
        $this->assertTrue($condition);
        $this->assertTrue(
            collect($roles)->every(
                fn ($role) => ! $user->roles->contains(fn ($item) => $item->getKey() === $role)
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $slugName = app($this->model)->getSlugName();
        $roles = $this->generate($this->model, 3);
        $user->roles()->attach($roles->pluck('id')->all());
        $user->loadRoles();
        $condition = $user->detachRole(...$roles->pluck($slugName)->all());
        $this->assertTrue($condition);
        $this->assertTrue(
            $roles->pluck($slugName)->every(
                fn ($role) => ! $user->roles->contains(fn ($item) => $item->getSlug() === $role)
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->roles()->attach($roles->pluck('id')->all());
        $user->loadRoles();
        $condition = $user->detachRole($roles->all());
        $this->assertTrue($condition);
        $this->assertTrue(
            $roles->every(
                fn ($role) => ! $user->roles->contains(fn ($item) => $item->is($role))
            )
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем массив не присоединенных ролей.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $condition = $user->detachRole(...$roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [485475, '45646', 'sjfldlg'];
        $condition = $user->detachRole($roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Передаем массив ролей с уровнями ниже максимального уровня модели.       ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $level3 = $this->generate($this->model, ['level' => 3]);
        $user->roles()->attach($level3);
        $user->loadRoles();
        $condition = $user->detachRole($level1, $level2);
        $this->assertFalse($condition);
        config(['is.uses.levels' => false]);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передачи массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->roles()->attach($roles);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($roles);
        $this->assertQueryExecutedCount(5);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передачи массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->roles()->attach($roles->pluck('id')->all());
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($roles->all());
        $this->assertQueryExecutedCount(4);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                  при передачи массива не присоединенных ролей.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->all();
        $this->resetQueryExecutedCount();
        $user->detachRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||       при отключении автоматической подгрузки отношений после изменения.       ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $roles = $this->generate($this->model, 3);
        $user->roles()->attach($roles->pluck('id')->all());
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($roles->all());
        $this->assertQueryExecutedCount(3);
    }

    /**
     * Есть ли метод, отсоединяющий все роли?
     */
    public function test_detach_all_roles(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->detachAllRoles();
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                              Отсоединяем все роли.                             ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->detachAllRoles();
        $this->assertTrue($condition);
        $this->assertEmpty($user->roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Повторно отсоединяем все роли.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->detachAllRoles();
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||      Подтверждаем количество выполненных запросов к БД при наличии ролей.      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->detachAllRoles();
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                              при отсутствии ролей.                             ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $user->detachAllRoles();
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||        при отключении автоматической подгрузки отношений при обновлении.       ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.load_on_update' => false]);
        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachAllRoles();
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, синхронизирующий роли?
     */
    public function test_sync_roles(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $expected = [$role->getKey()];
        $user->syncRoles($role->getKey());
        $actual = $user->roles->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $expected = [$role->getKey()];
        $user->syncRoles($role);
        $actual = $user->roles->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $expected = $roles->pluck('id')->all();
        $user->syncRoles($expected);
        $actual = $user->roles->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $expected = $roles->pluck('id')->all();
        $user->syncRoles($roles);
        $actual = $user->roles->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем массив несуществующих идентификаторов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [34543, '3453453434', 'sdfgdsg'];
        $user->syncRoles($roles);
        $this->assertEmpty($user->roles);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $user->attachRole($this->generate($this->model, 3));
        $roles = $this->generate($this->model, 3);
        $this->resetQueryExecutedCount();
        $user->syncRoles($roles);
        $this->assertQueryExecutedCount(6);
    }

    /**
     * Есть ли метод, проверяющий наличие роли?
     */
    public function test_has_one_role_use_levels_with_one_param(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level = 2;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role->getSlug());
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Передаем роль, равную по уровню с максимальным уровнем модели.         ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level]);
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем роль, меньшую по уровню                        ||
        // ! ||                    относительно максимального уровня модели.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level - 1]);
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем роль, большую по уровню                        ||
        // ! ||                    относительно максимального уровня модели.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level + 1]);
        $condition = $user->hasRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 384563459;
        $condition = $user->hasRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем количество запросов к БД при передаче идентификатора.       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level])->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем количество запросов к БД при передаче модели.           ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => ++$level]);
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем количество запросов к БД при передаче роли,            ||
        // ! ||        имеющую уровень меньший относительно максимального уровня модели.       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model, ['level' => $level - 1]);
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем количество запросов к БД                     ||
        // ! ||              при передаче отсутствующего в таблице идентификатора.             ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 384563459;
        $this->resetQueryExecutedCount();
        $condition = $user->hasRole($role);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие роли у модели?
     */
    public function test_has_one_role_without_levels_with_one_param(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                 Передаем slug.                                 ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role->getSlug());
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->hasRole($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем отсутствующую у модели роль.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $condition = $user->hasRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 384563459;
        $condition = $user->hasRole($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем количество запросов к БД при передаче идентификатора.       ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model)->getKey();
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем количество запросов к БД при передаче модели.           ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем количество запросов к БД                     ||
        // ! ||                    при передачи отсутствующей у модели роли.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $this->resetQueryExecutedCount();
        $user->hasRole($role);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем количество запросов к БД                     ||
        // ! ||              при передаче отсутствующего в таблице идентификатора.             ||
        // ! ||--------------------------------------------------------------------------------||

        $role = 384563459;
        $this->resetQueryExecutedCount();
        $condition = $user->hasRole($role);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие хотябы одной роли из переданных.
     */
    public function test_has_one_role_with_levels_with_many_params(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level = 2;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                       равны максимальному уровню модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
        ];
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                       меньше максимального уровня модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                        выше максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
        ];
        $condition = $user->hasRole($roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354354, '3456457', 'dfgdgf'];
        $condition = $user->hasRole($roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив смешанных данных.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            'dskjghkjdsgf',
            $this->generate($this->model, ['level' => $level + 1])->getKey(),
            $this->generate($this->model, ['level' => $level], false),
            $this->generate($this->model, ['level' => $level - 1])->getSlug(),
        ];
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передаче массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передаче массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                  при передаче массива ролей, у которых уровни                  ||
        // ! ||                        ниже максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                  при передаче массива ролей, у которых уровни                  ||
        // ! ||                        выше максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
        ];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||          при передаче массива отсутствующих в таблице идентификаторов.         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [6345437, '3454646', 'dsfgdsgsdgf'];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие хотябы одной роли из переданных.
     */
    public function test_has_one_role_without_levels_with_many_params(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id');
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $slugName = app($this->model)->getSlugName();
        $roles = $this->generate($this->model, 3)->pluck($slugName);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Передаем массив не присоединенных к модели ролей.               ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $condition = $user->hasRole($roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354354, '3456457', 'dfgdgf'];
        $condition = $user->hasRole($roles);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив смешанных данных.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            'dskjghkjdsgf',
            $this->generate($this->model)->getKey(),
            $this->generate($this->model, false),
            $this->generate($this->model)->getSlug(),
            $user->roles->first(),
        ];
        $condition = $user->hasRole($roles);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передаче массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передаче массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                 при передаче не присоединенных к модели ролей.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||          при передаче массива отсутствующих в таблице идентификаторов.         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [6345437, '3454646', 'dsfgdsgsdgf'];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие всех переданных ролей?
     */
    public function test_has_all_roles_use_levels(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level = 2;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
            $this->generate($this->model, ['level' => ++$level])->getSlug(),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                       равны максимальному уровню модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
            $this->generate($this->model, ['level' => $level]),
        ];
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                       меньше максимального уровня модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем массив ролей, у которых уровни                    ||
        // ! ||                        выше максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
        ];
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354354, '3456457', 'dfgdgf'];
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив смешанных данных.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            'dskjghkjdsgf',
            $this->generate($this->model, ['level' => $level + 1])->getKey(),
            $this->generate($this->model, ['level' => $level], false),
            $this->generate($this->model, ['level' => $level - 1])->getSlug(),
        ];
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передаче массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
            $this->generate($this->model, ['level' => ++$level])->getKey(),
        ];
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передаче массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
            $this->generate($this->model, ['level' => ++$level]),
        ];
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                  при передаче массива ролей, у которых уровни                  ||
        // ! ||                        ниже максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
            $this->generate($this->model, ['level' => $level - 1]),
        ];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                  при передаче массива ролей, у которых уровни                  ||
        // ! ||                        выше максимального уровня модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
            $this->generate($this->model, ['level' => $level + 1]),
        ];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||          при передаче массива отсутствующих в таблице идентификаторов.         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [6345437, '3454646', 'dsfgdsgsdgf'];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие всех ролей у модели?
     */
    public function test_has_all_roles_without_levels(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id');
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $slugName = app($this->model)->getSlugName();
        $roles = $this->generate($this->model, 3)->pluck($slugName);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $condition = $user->hasRole($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Передаем массив не присоединенных к модели ролей.               ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [354354, '3456457', 'dfgdgf'];
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив смешанных данных.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            'dskjghkjdsgf',
            $this->generate($this->model)->getKey(),
            $this->generate($this->model, false),
            $this->generate($this->model)->getSlug(),
            $user->roles->first(),
        ];
        $condition = $user->hasRole($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                      при передаче массива идентификаторов.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck('id')->all();
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                          при передаче массива моделей.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $user->attachRole($roles);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                 при передаче не присоединенных к модели ролей.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3);
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||          при передаче массива отсутствующих в таблице идентификаторов.         ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [6345437, '3454646', 'dsfgdsgsdgf'];
        $this->resetQueryExecutedCount();
        $user->hasRole($roles, true);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий роль модели с максимальным уровнем доступа.
     */
    public function test_role(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $level3 = $this->generate($this->model, ['level' => 3]);
        $user->roles()->attach($level2);
        $user->loadRoles();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                          Подтверждаем возврат модели.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $user->role();
        $this->assertInstanceOf($this->model, $role);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем возврат роли с максимальным уровнем.               ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $user->role();
        $this->assertTrue($level2->is($role));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Подтверждаем возврат null при отсутствии ролей.                ||
        // ! ||--------------------------------------------------------------------------------||

        $user->roles()->detach();
        $user->loadRoles();
        $role = $user->role();
        $this->assertNull($role);
    }

    /**
     * Есть ли метод, возвращающий наибольший уровень присоединенных ролей.
     */
    public function test_level(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
        $user = $this->generate($this->user);
        $level1 = $this->generate($this->model, ['level' => 1]);
        $level2 = $this->generate($this->model, ['level' => 2]);
        $level3 = $this->generate($this->model, ['level' => 3]);
        $user->roles()->attach($level2);
        $user->loadRoles();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Подтверждаем возврат числа.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $level = $user->level();
        $this->assertIsInt($level);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем возврат максимального уровня ролей.                ||
        // ! ||--------------------------------------------------------------------------------||

        $level = $user->level();
        $this->assertEquals($level2->level, $level);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Подтверждаем возврат нуля при отсутствии присоединенных ролей.         ||
        // ! ||--------------------------------------------------------------------------------||

        $user->roles()->detach();
        $user->loadRoles();
        $level = $user->level();
        $this->assertEquals(0, $user->level());
    }

    /**
     * Есть ли метод, проверяющий наличие ролей?
     */
    public function test_is(): void
    {
        config(['is.uses.extend_is_method' => true]);
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->is($role);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->is($role);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив slug'ов.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = [
            $this->generate($this->model)->getSlug(),
            $this->generate($this->model)->getSlug(),
            $this->generate($this->model)->getSlug(),
        ];
        $user->attachRole($roles);
        $condition = $user->is($roles, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем массив моделей.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = collect([
            $this->generate($this->model),
            $this->generate($this->model),
            $this->generate($this->model),
        ]);
        $user->attachRole($roles->slice(0, 2));
        $condition = $user->is($roles, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                          Передаем отсутствующую роль.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $condition = $user->is($role);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Подтверждаем работу метода по умолчанию.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $condition = $user->is($user);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Подтверждаем работу метода по умолчанию при отключении расширения.       ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.extend_is_method' => false]);
        $role = $this->generate($this->model);
        $user->roles()->attach($role);
        $user->loadRoles();
        $condition = $user->is($role);
        $this->assertFalse($condition);
    }

    /**
     * Есть ли магический метод, проверяющий наличие роли по его slug'у?
     */
    public function test_call_magic_is_role(): void
    {
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => false]);
        $user = $this->generate($this->user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->attachRole($role);
        $method = 'is'.ucfirst($role->getSlug());
        $condition = $user->{$method}();
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Подтверждаем наличие роли.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $user->attachRole($role);
        $method = 'is'.ucfirst($role->getSlug());
        $condition = $user->{$method}();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                          Подтверждаем отсутствие роли.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $role = $this->generate($this->model);
        $method = 'is'.ucfirst($role->getSlug());
        $condition = $user->{$method}();
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем выброс исключения.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(BadMethodCallException::class);
        $user->undefined();
    }
}
