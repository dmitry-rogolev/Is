<?php

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Helper;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Stringable;

/**
 * Тестируем помощника.
 */
class HelperTest extends TestCase
{
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
