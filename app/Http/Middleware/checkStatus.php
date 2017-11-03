<?php

namespace App\Http\Middleware;

use Closure;

class checkStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->status == 0) {
            return redirect('login')->with('msj', 'Su cuenta ha sido desactivada. Comuniquese con el administrador.');
        } else {
            return $next($request);
        }
    }
}
