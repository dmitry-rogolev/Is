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
     *
     * @param  int|string  $role
     */
    public function handle(Request $request, \Closure $next, ...$role): mixed
    {
        if ($this->auth->check() && $this->auth->user() instanceof Roleable && $this->auth->user()->hasRole($role)) {
            return $next($request);
        }

        abort(403, sprintf('Доступ запрещен. Нет требуемой роли "%s".', implode(',', $role)));
    }
}
