<?php

namespace dmitryrogolev\Is\Http\Middlewares;

use dmitryrogolev\Is\Contracts\Roleable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Посредник, проверяющий наличие роли у пользователя.
 */
class VerifyRole
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
    public function handle(Request $request, \Closure $next, mixed ...$role): mixed
    {
        if ($this->auth->check() && $this->auth->user() instanceof Roleable && $this->auth->user()->hasRole($role)) {
            return $next($request);
        }

        return redirect('/');
    }
}
