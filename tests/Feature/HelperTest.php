<?php

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Helper;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Stringable;
use ReflectionClass;
use ReflectionMethod;

/**
 * Тестируем помощника.
 */
class HelperTest extends TestCase
{
    /**
     * Совпадает ли количество тестов с количеством методов в классе?
     *
     * @return void
     */
    public function test_count_tests(): void
    {
        $methods = (new ReflectionClass(Helper::class))->getMethods(ReflectionMethod::IS_PUBLIC);
        $count   = collect($methods)->count();
        $methods = (new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC);
        $tests   = collect($methods)
            ->filter(fn ($method) => str_starts_with($method->name, 'test'))
            ->where('name', '!=', __FUNCTION__);

        $this->assertCount($count, $tests);
    }

    /**
     * Возвращает ли метод "str" объект "\Illuminate\Support\Stringable"?
     *
     * @return void
     */
    public function test_str(): void
    {
        $this->assertInstanceOf(Stringable::class, Helper::str());
        $this->assertEquals('test', Helper::str('test')->toString());
    }

    /**
     * Возвращает ли метод "slug" строку в виде slug?
     *
     * @return void
     */
    public function test_slug(): void
    {
        $this->assertEquals('is.admin', Helper::slug('isAdmin'));
        $this->assertEquals('is.user', Helper::slug('is_user'));
        $this->assertEquals('is.moderator', Helper::slug('is.moderator'));
        $this->assertEquals('is.customer', Helper::slug('IsCustomer'));
        $this->assertEquals('is.admin', Helper::slug('is-Admin'));
        $this->assertEquals('is.admin', Helper::slug('is Admin'));
        $this->assertEquals('is.admin', Helper::slug(fn () => 'is Admin'));
    }

    /**
     * Разбивает ли метод "split" строку на массив подстрок?
     *
     * @return void
     */
    public function test_split(): void
    {
        $this->assertEquals(['test', 'split'], Helper::split('test,split'));
        $this->assertEquals(['test', 'split'], Helper::split('test|split'));
        $this->assertEquals(['test', 'split'], Helper::split('test split'));
        $this->assertEquals(['test', 'split'], Helper::split('test, split'));
        $this->assertEquals(['test', 'split'], Helper::split('test.split'));
        $this->assertEquals(['test', 'split'], Helper::split('test-split'));
        $this->assertEquals(['test', 'split'], Helper::split(fn () => 'test-split'));
    }

    /**
     * Возвращает ли метод "toArray" массив?
     *
     * @return void
     */
    public function test_to_array(): void
    {
        $this->assertEquals([], Helper::toArray(null));
        $this->assertEquals([5], Helper::toArray(5));
        $this->assertEquals(['test'], Helper::toArray('test'));
        $this->assertEquals(['test', 5], Helper::toArray('test,5'));
        $this->assertEquals(['test'], Helper::toArray(fn () => 'test'));
        $this->assertEquals(['test', 5], Helper::toArray([[['test'], [5]]]));
        $this->assertEquals(['test', 5], Helper::toArray('test, 5'));
    }
}
