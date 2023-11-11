<?php 

namespace dmitryrogolev\Is\Http\Middlewares;

use dmitryrogolev\Is\Contracts\Roleable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class VerifyRole 
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
     * @param int|string $role
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$role): mixed 
    {
        $role = join(',', $role);

        if ($this->auth->check() && $this->auth->user() instanceof Roleable && $this->auth->user()->hasRole($role)) {
            return $next($request);
        }

        abort(403, sprintf("Доступ запрещен. Нет требуемой роли \"%s\".", $role));
    }
}
