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
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected Guard $auth;

    /**
     * Создать новый экземпляр посредника.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Обработать входящий запрос.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $level
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $level): mixed 
    {
        if ($this->auth->check() && $this->auth->user() instanceof Roleable && $this->auth->user()->level() >= intval($level)) {
            return $next($request);
        }

        abort(403, sprintf("Доступ запрещен. Нет требуемого уровня \"%s\".", $level));
    }
}