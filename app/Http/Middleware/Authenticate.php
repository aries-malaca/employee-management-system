<?php

namespace ExactivEM\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use ExactivEM\User;
use ExactivEM\Libraries\Mailer_Class;

class Authenticate extends \ExactivEM\Http\Controllers\Controller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }
            else {
                return redirect()->guest('login')->with(array("referrer"=>$request->server('REQUEST_URI')));
            }
        }
        else{
            if(Auth::user()->allow_access == 0){
              Auth::logout();
                return redirect()->guest('login')->with(array("referrer"=>$request->server('REQUEST_URI')));
            }
        }
        return $next($request);
    }
}
