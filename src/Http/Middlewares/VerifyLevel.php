<?php

namespace dmitryrogolev\Is\Http\Middlewares;

use dmitryrogolev\Is\Contracts\Roleable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Посредник, проверяющий наличие необходимого уровня доступа.
 */
class VerifyLevel
{
    protected Guard $auth;

    /**
     * Создать новый экземпляр посредника.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Обработать входящий запрос.
     */
    public function handle(Request $request, \Closure $next, mixed $level): mixed
    {
        if ($this->auth->check() && $this->auth->user() instanceof Roleable && $this->auth->user()->level() >= intval($level)) {
            return $next($request);
        }

        abort(403, "Доступ запрещен. Нет требуемого уровня \"$level\".");
    }
}
