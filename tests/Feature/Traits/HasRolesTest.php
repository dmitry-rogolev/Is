<?php 

namespace dmitryrogolev\Is\Tests\Feature\Traits;

use dmitryrogolev\Is\Tests\TestCase;
use dmitryrogolev\Is\Traits\AbstractHasRoles;
use dmitryrogolev\Is\Traits\ExtendIsMethod;
use dmitryrogolev\Is\Traits\HasLevels;

/**
 * Тестируем функционал ролей.
 */
class HasRolesTest extends TestCase 
{
    /**
     * Подключены ли трейты согласно конфигурации?
     *
     * @return void
     */
    public function test_uses_traits(): void 
    {
        $traits = collect(class_uses_recursive(app(config('is.models.user'))));
        $abstractHasRoles = $traits->contains(AbstractHasRoles::class);
        $hasLevels = $traits->contains(HasLevels::class);
        $extendIsMethod = $traits->contains(ExtendIsMethod::class);
        $hasTraits = function () use ($abstractHasRoles, $hasLevels, $extendIsMethod) {
            if (config('is.uses.levels') && config('is.uses.extend_is_method')) {
                return $abstractHasRoles && $hasLevels && $extendIsMethod;
            } 
            
            if (config('is.uses.levels')) {
                return $abstractHasRoles && $hasLevels && ! $extendIsMethod;
            } 
            
            if (config('is.uses.extend_is_method')) {
                return $abstractHasRoles && ! $hasLevels && $extendIsMethod;
            }
            
            return $abstractHasRoles && ! $hasLevels && ! $extendIsMethod;
        };
        
        $this->assertTrue($hasTraits());
    }
}
