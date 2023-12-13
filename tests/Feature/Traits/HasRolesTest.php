<?php

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use dmitryrogolev\Is\Traits\ExtendIsMethod;
use dmitryrogolev\Is\Traits\HasLevels;
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

    /**
     * Имя первичного ключа.
     */
    protected string $keyName;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = config('is.models.role');
        $this->user = config('is.models.user');
        $this->keyName = config('is.primary_key');
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
        $expected = $roles->pluck($this->keyName)->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат отношения.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $query = $user->roles();
        $this->assertInstanceOf(MorphToMany::class, $query);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем получение ролей модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $actual = $user->roles()->get()->pluck($this->keyName)->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем наличие временных меток у промежуточных моделей.         ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => true]);
        $pivot = $user->roles()->first()->pivot;
        $this->assertNotNull($pivot->{$pivot->getCreatedAtColumn()});
        $this->assertNotNull($pivot->{$pivot->getUpdatedAtColumn()});

        // ! ||--------------------------------------------------------------------------------||
        // ! ||        Подтверждаем отсутствие временных меток у промежуточных моделей.        ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.timestamps' => false]);
        $pivot = $user->roles()->first()->pivot;
        $this->assertNull($pivot->{$pivot->getCreatedAtColumn()});
        $this->assertNull($pivot->{$pivot->getUpdatedAtColumn()});
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
        $actual = $user->getRoles()->pluck($this->keyName)->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Подтверждаем возврат ролей при включенной иерархии ролей.           ||
        // ! ||--------------------------------------------------------------------------------||

        config(['is.uses.levels' => true]);
        $expected = [$level1->getKey(), $level2->getKey()];
        $actual = $user->getRoles()->pluck($this->keyName)->all();
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
        $roles = $user->roles->where($this->keyName, $role->getKey());
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
        $this->resetQueries();
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
        $this->assertTrue($user->roles->where($this->keyName, last($roles)->getKey())->count() === 1);
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
        $roles = $user->roles->where($this->keyName, $role->getKey());
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
        $this->resetQueries();
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
                fn ($role) => $user->roles->where($this->keyName, $role->getKey())->count() === 1
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

        $roles = $this->generate($this->model, 3)->pluck($this->keyName)->all();
        $user->roles()->attach($roles);
        $user->loadRoles();
        $condition = $user->detachRole($roles);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $roles = $this->generate($this->model, 3)->pluck($this->keyName)->all();
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

        $roles = $this->generate($this->model, 3)->pluck($this->keyName)->all();
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
        $user->roles()->attach($roles->pluck($this->keyName)->all());
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
        $user->roles()->attach($roles->pluck($this->keyName)->all());
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

        $roles = $this->generate($this->model, 3)->pluck($this->keyName)->all();
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

        $roles = $this->generate($this->model, 3)->pluck($this->keyName)->all();
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
        $user->roles()->attach($roles->pluck($this->keyName)->all());
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
        $user->roles()->attach($roles->pluck($this->keyName)->all());
        $user->loadRoles();
        $this->resetQueryExecutedCount();
        $user->detachRole($roles->all());
        $this->assertQueryExecutedCount(3);
    }

    // /**
    //  * Есть ли метод, отсоединяющий все роли?
    //  */
    // public function test_detach_all_roles(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     $user = $this->getUserWithRoles();
    //     $this->assertTrue($user->detachAllRoles());
    //     $this->assertCount(0, $user->getRoles());
    //     $this->assertFalse($user->detachAllRoles());

    //     // Отключаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(false);

    //     $user = $this->getUserWithRoles(5);
    //     $this->assertTrue($user->detachAllRoles());
    //     $this->assertNotCount(0, $user->getRoles());
    //     $user->loadRoles();
    //     $this->assertCount(0, $user->getRoles());
    // }

    // /**
    //  * Есть ли метод, синхронизирующий роли?
    //  */
    // public function test_sync_roles(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);
    //     $user = $this->getUser();
    //     $user->attachRole(Is::generate(['level' => 2]));
    //     $role = Is::generate(['level' => 3]);
    //     $user->syncRoles($role);
    //     $this->assertCount(2, $user->getRoles());

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);
    //     $roles = Is::generate(3);
    //     $user->syncRoles($roles);
    //     $this->assertEquals($roles->pluck(config('is.primary_key')), $user->getRoles()->pluck(config('is.primary_key')));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие хотябы одной переданной роли?
    //  */
    // public function test_has_one_role(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);
    //     $user = $this->getUser();
    //     $user->attachRole(Is::generate(['level' => 3]));
    //     $this->assertTrue($user->hasOneRole(Is::generate(['level' => 2])));
    //     $this->assertFalse($user->hasOneRole(Is::generate(['level' => 4])));

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);
    //     $role = Is::generate();
    //     $user->attachRole($role);
    //     $this->assertTrue($user->hasOneRole($role));
    //     $this->assertFalse($user->hasOneRole(Is::generate()));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие всех переданных ролей?
    //  */
    // public function test_has_all_roles(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);
    //     $user = $this->getUser();
    //     $level1 = Is::generate(['level' => 1]);
    //     $level2 = Is::generate(['level' => 2]);
    //     $level3 = Is::generate(['level' => 3]);
    //     $user->attachRole($level2);
    //     $this->assertTrue($user->hasAllRoles($level1, $level2));
    //     $this->assertFalse($user->hasAllRoles($level1, $level2, $level3));

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);
    //     $user->attachRole($level3);
    //     $this->assertTrue($user->hasAllRoles($level2, $level3));
    //     $this->assertFalse($user->hasAllRoles($level1, $level2, $level3));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие ролей?
    //  */
    // public function test_has_role(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);

    //     // Проверяем наличие хотябы одной роли.
    //     $user = $this->getUser();
    //     $user->attachRole(Is::generate(['level' => 3]));
    //     $this->assertTrue($user->hasRole(Is::generate(['level' => 2])));
    //     $this->assertFalse($user->hasRole(Is::generate(['level' => 4])));
    //     $user->detachAllRoles();

    //     // Проверяем наличие нескольких ролей.
    //     $level1 = Is::generate(['level' => 1]);
    //     $level2 = Is::generate(['level' => 2]);
    //     $level3 = Is::generate(['level' => 3]);
    //     $user->attachRole($level2);
    //     $this->assertTrue($user->hasRole([$level1, $level2], true));
    //     $this->assertFalse($user->hasAllRoles([$level1, $level2, $level3], true));
    //     $user->detachAllRoles();

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);

    //     // Проверяем наличие хотябы одной роли.
    //     $role = Is::generate();
    //     $user->attachRole($role);
    //     $this->assertTrue($user->hasRole($role));
    //     $this->assertFalse($user->hasRole(Is::generate()));
    //     $user->detachAllRoles();

    //     // Проверяем наличие нескольких ролей.
    //     $user->attachRole($level2, $level3);
    //     $this->assertTrue($user->hasRole([$level2, $level3], true));
    //     $this->assertFalse($user->hasRole([$level1, $level2, $level3], true));
    // }

    // /**
    //  * Есть ли магический метод, проверяющий наличие роли по его slug'у?
    //  */
    // public function test_call_magic_is_role(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);
    //     $level1 = Is::generate(['level' => 1]);
    //     $level2 = Is::generate(['level' => 2]);
    //     $level3 = Is::generate(['level' => 3]);
    //     $user = $this->getUser();
    //     $user->attachRole($level2);
    //     $this->assertTrue($user->{'is'.ucfirst($level2->slug)}());
    //     $this->assertTrue($user->{'is'.ucfirst($level1->slug)}());
    //     $this->assertFalse($user->{'is'.ucfirst($level3->slug)}());

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);
    //     $role = Is::generate();
    //     $user->attachRole($role);
    //     $this->assertTrue($user->{'is'.ucfirst($role->slug)}());
    //     $this->assertFalse($user->{'is'.ucfirst(Is::generate()->slug)}());
    // }

    // /**
    //  * Подключены ли трейты согласно конфигурации?
    //  */
    // public function test_uses_traits(): void
    // {
    //     $traits = collect(class_uses_recursive(app(config('is.models.user'))));

    //     $this->assertTrue($traits->contains(HasLevels::class));
    //     $this->assertTrue($traits->contains(ExtendIsMethod::class));
    // }

    // /**
    //  * Есть ли метод, возвращающий роль с наибольшим уровнем?
    //  */
    // public function test_role(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);

    //     $user = $this->getUser();
    //     $user->attachRole(Is::generate(['level' => 1]));
    //     $user->attachRole(Is::generate(['level' => 2]));
    //     $role = Is::generate(['level' => 3]);
    //     $user->attachRole($role);

    //     $this->assertTrue($role->is($user->role()));
    // }

    // /**
    //  * Есть ли метод, возвращающий наибольший уровень ролей, привязанных к модели?
    //  */
    // public function test_level(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);

    //     $user = $this->getUser();
    //     $user->attachRole(Is::generate(['level' => 1]));
    //     $user->attachRole(Is::generate(['level' => 2]));
    //     $user->attachRole(Is::generate(['level' => 3]));

    //     $this->assertEquals(3, $user->level());
    // }

    // /**
    //  * Есть ли метод, расширяющий метод "is"?
    //  */
    // public function test_is(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);

    //     // Включаем расширение метода "is" модели Eloquent.
    //     Is::usesExtendIsMethod(true);

    //     $user = $this->getUser();
    //     $level1 = Is::generate(['level' => 1]);
    //     $level2 = Is::generate(['level' => 2]);
    //     $level3 = Is::generate(['level' => 3]);
    //     $user->attachRole($level2);

    //     // Проверяем возможность сравнения моделей.
    //     $this->assertTrue($user->is($user));
    //     $this->assertFalse($user->is($this->getUser()));

    //     // Проверяем наличие роли.
    //     $this->assertTrue($user->is($level2));
    //     $this->assertTrue($user->is($level1->getKey()));
    //     $this->assertFalse($user->is($level3));

    //     // Проверяем наличие нескольких ролей.
    //     $this->assertTrue($user->is([$level1, $level2], true));
    //     $this->assertFalse($user->is([$level1, $level2, $level3], true));
    // }
}
