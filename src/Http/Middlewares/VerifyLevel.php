<?php 

namespace dmitryrogolev\Is\Http\Middlewares;

use dmitryrogolev\Is\Contracts\Roleable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class VerifyLevel 
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected Guard $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
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