<?php

namespace dmitryrogolev\Is\Tests\Feature\Contracts;

use dmitryrogolev\Is\Contracts\Levelable;
use dmitryrogolev\Is\Contracts\Roleable;
use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\TestCase;

/**
 * Тестируем интерфейс функционала ролей.
 */
class RoleableTest extends TestCase
{
    /**
     * Наследуется ли интерфейс согласно конфигурации?
     *
     * @return void
     */
    public function test_extends(): void
    {
        $this->assertInstanceOf(Roleable::class, app(Is::userModel()));
        $this->assertInstanceOf(Levelable::class, app(Is::userModel()));
    }
}
