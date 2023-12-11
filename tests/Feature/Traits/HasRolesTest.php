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
        $user = $this->generate($this->user);
        config(['is.uses.load_on_update' => true]);
        config(['is.uses.levels' => true]);
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
    }

    // /**
    //  * Есть ли метод, отсоединяющий роль?
    //  */
    // public function test_detach_role(): void
    // {
    //     // Включаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(true);

    //     // Включаем иерархию ролей.
    //     Is::usesLevels(true);
    //     $user = $this->getUser();
    //     $role = Is::generate(['level' => 3]);
    //     $user->attachRole($role);
    //     $this->assertTrue($user->detachRole(Is::generate(['level' => 2])));
    //     $this->assertFalse($user->detachRole(Is::generate(['level' => 4])));
    //     $this->assertCount(2, $user->getRoles());
    //     $this->assertTrue($user->detachRole($role));
    //     $this->assertCount(0, $user->getRoles());

    //     // Есть не передать аргумент, то будут отсоединены все роли.
    //     $user->attachRole(Is::generate());
    //     $this->assertNotCount(0, $user->getRoles());
    //     $this->assertTrue($user->detachRole());
    //     $this->assertCount(0, $user->getRoles());

    //     // Отключаем иерархию ролей.
    //     Is::usesLevels(false);
    //     $role = Is::generate();
    //     $user->attachRole($role);
    //     $this->assertFalse($user->detachRole(Is::generate()));
    //     $this->assertTrue($user->detachRole($role));
    //     $this->assertCount(0, $user->getRoles());

    //     // Отключаем авто подгрузку ролей.
    //     Is::usesLoadOnUpdate(false);

    //     $user->attachRole(Is::generate(3));
    //     $user->loadRoles();
    //     $this->assertCount(3, $user->getRoles());
    //     $user->detachRole();
    //     $this->assertCount(3, $user->getRoles());
    //     $user->loadRoles();
    //     $this->assertCount(0, $user->getRoles());
    // }

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
