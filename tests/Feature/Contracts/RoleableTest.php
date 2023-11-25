<?php 

namespace dmitryrogolev\Is\Tests\Feature\Contracts;

use dmitryrogolev\Is\Contracts\AbstractRoleable;
use dmitryrogolev\Is\Contracts\Levelable;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем интрерфейс функционала ролей.
 */
class RoleableTest extends TestCase
{
    /**
     * Наследуется ли интрерфейс согласно конфигурации?
     *
     * @return void
     */
    public function test_extends(): void 
    {
        $this->assertInstanceOf(AbstractRoleable::class, app(config('is.models.user')));

        if (config('is.uses.levels')) {
            $this->assertInstanceOf(Levelable::class, app(config('is.models.user')));
        }
    }
}
