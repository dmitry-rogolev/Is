<?php 

namespace dmitryrogolev\Is\Tests\Feature;

use dmitryrogolev\Is\Helper;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;

class HelperTest extends TestCase
{
    /**
     * Совпадает ли количество тестов с количеством методов в классе?
     *
     * @return void
     */
    public function test_count_tests(): void 
    {
        $count = collect((new ReflectionClass(Helper::class))->getMethods(ReflectionMethod::IS_PUBLIC))->count();
        $tests = collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
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
        $this->assertEquals('', Helper::str()->toString());
        $this->assertEquals('', Helper::str(null)->toString());
        $this->assertEquals('', Helper::str('')->toString());
        $this->assertEquals('', Helper::str([])->toString());
        $this->assertEquals('test', Helper::str('test')->toString());
        $this->assertEquals('test', Helper::str(fn () => 'test')->toString());
    }

    /**
     * Возвращает ли метод "slug" строку в виде slug?
     *
     * @return void
     */
    public function test_slug(): void 
    {
        $this->assertEquals('is.admin',     Helper::slug('isAdmin'));
        $this->assertEquals('is.user',      Helper::slug('is_user'));
        $this->assertEquals('is.moderator', Helper::slug('is.moderator'));
        $this->assertEquals('is.customer',  Helper::slug('IsCustomer'));
        $this->assertEquals('is.admin',     Helper::slug('is-Admin'));
        $this->assertEquals('is.admin',     Helper::slug('is Admin'));
        $this->assertEquals('is.admin',     Helper::slug(fn () => 'is Admin'));
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
        $this->assertEquals(['test'], Helper::toArray(fn () => 'test'));
        $this->assertEquals(['test', 5], Helper::toArray([[['test'], [5]]]));
        $this->assertEquals(['test', 5], Helper::toArray('test, 5'));
    }

    /**
     * Записывает ли метод "log" переданное сообщение в журнал?
     *
     * @return void
     */
    public function test_log(): void 
    {
        if (config('is.log_channel') === 'null' || ! config('is.uses.logging')) {
            $this->markTestSkipped('Ведение журнала отключено.');
        }
        
        Log::shouldReceive('channel->log')->once()->with('debug', 'test', []);
        Helper::log('debug', 'test');
    }
}
