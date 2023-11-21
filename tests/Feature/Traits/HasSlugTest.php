<?php 

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Helper;
use dmitryrogolev\Is\Tests\TestCase;
use dmitryrogolev\Is\Traits\HasSlug;
use ReflectionClass;
use ReflectionMethod;

/**
 * Тестируем функционал, упрощающий работу с аттрибутом "slug".
 */
class HasSlugTest extends TestCase 
{
    /**
     * Совпадает ли количество тестов с количеством методов в трейте?
     *
     * @return void
     */
    public function test_count_tests(): void 
    {
        $count = collect((new ReflectionClass(HasSlug::class))->getMethods(ReflectionMethod::IS_PUBLIC))->count();
        $tests = collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn ($method) => str_starts_with($method->name, 'test'))
            ->where('name', '!=', __FUNCTION__);
            
        $this->assertCount($count, $tests);
    }

    /**
     * Есть ли метод, возвращающий ключ "slug"?
     *
     * @return void
     */
    public function test_get_slug_key(): void 
    {
        $this->assertEquals('slug', app(config('is.models.role'))->getSlugKey());
    }

    /**
     * Есть ли метод, изменяющий значение ключа "slug"?
     *
     * @return void
     */
    public function test_set_slug_key(): void 
    {
        $model = app(config('is.models.role'));
        $model->setSlugKey('some_name');
        $this->assertEquals('some_name', $model->getSlugKey());
    }

    /**
     * Есть ли метод, возвращающий значение slug'а?
     *
     * @return void
     */
    public function test_get_slug(): void 
    {
        $this->runLaravelMigrations();

        $model = app(config('is.models.role'));
        $model->slug = 'admin';
        $this->assertEquals('admin', $model->getSlug());
    }

    /**
     * Есть ли метод, изменяющий значение slug'а?
     *
     * @return void
     */
    public function test_set_slug(): void 
    {
        $model = app(config('is.models.role'));
        $model->setSlug('admin');
        $this->assertEquals('admin', $model->getSlug());
    }

    /**
     * Есть ли метод, вызывающийся при попытки записи аттрибута как свойства модели?
     *
     * @return void
     */
    public function test_set_slug_attribute(): void 
    {
        $model = app(config('is.models.role'));
        $slug = 'it_is_my_big_slug';
        $model->{$model->getSlugKey()} = $slug;
        $this->assertEquals(Helper::slug($slug), $model->getSlug());
    }

    /**
     * Есть ли статический метод поиска модели по его "slug"?
     *
     * @return void
     */
    public function test_find_by_slug(): void 
    {
        $this->runLaravelMigrations();

        $model = config('is.models.role')::factory()->create(['slug' => 'admin']);
        $this->assertTrue($model->is(config('is.models.role')::findBySlug('admin')));
    }
}
